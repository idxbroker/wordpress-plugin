<?php
namespace IDX;

class Idx_Api
{
    /**
     *
     * Using our web services function, lets get the system links built in the middleware,
     * clean and prepare them, and return them in a new array for use.
     *
     */
    public function idx_api_get_systemlinks()
    {
        if (!get_option('idx_broker_apikey')) {
            return array();
        }
        return $this->idx_api('systemlinks', $this->idx_api_get_apiversion());
    }

    /**
     *
     * Using our web services function, lets get saved links built in the middleware,
     * clean and prepare them, and return them in a new array for use.
     *
     */
    public function idx_api_get_savedlinks()
    {
        if (!get_option('idx_broker_apikey')) {
            return array();
        }
        return $this->idx_api('savedlinks', $this->idx_api_get_apiversion());
    }

    /**
     *
     * Using our web services function, lets get the widget details built in the middleware,
     * clean and prepare them, and return them in a new array for use.
     *
     */
    public function idx_api_get_widgetsrc()
    {
        if (!get_option('idx_broker_apikey')) {
            return array();
        }
        return $this->idx_api('widgetsrc', $this->idx_api_get_apiversion());
    }

    /**
     * Get api version
     */
    public function idx_api_get_apiversion()
    {
        if (!get_option('idx_broker_apikey')) {
            return Initiate_Plugin::IDX_API_DEFAULT_VERSION;
        }

        $data = $this->idx_api('apiversion', Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'clients', array(), 86400);
        if (is_array($data) && !empty($data)) {
            return $data['version'];
        } else {
            return Initiate_Plugin::IDX_API_DEFAULT_VERSION;
        }
    }

    /**
     * apiResponse handles the various replies we get from the IDX Broker API and returns appropriate error messages.
     * @param  [array] $response [response header from API call]
     * @return [array]           [keys: 'code' => response code, 'error' => false (default), or error message if one is found]
     */
    public function apiResponse($response)
    {
        if (!$response || !is_array($response) || !isset($response['response'])) {
            return array("code" => "Generic", "error" => "Unable to complete API call.");
        }
        if (!function_exists('curl_init')) {
            return array("code" => "PHP", "error" => "The cURL extension for PHP is not enabled on your server.<br />Please contact your developer and/or hosting provider.");
        }
        $response_code = $response['response']['code'];
        $err_message = false;
        if (is_numeric($response_code)) {
            switch ($response_code) {
                case 401:$err_message = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the IDX Broker Platinum Dashboard or call 800-421-9668.';
                    break;
                case 403:
                case 403.4:$err_message = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.';
                    break;
                case 405:
                case 409:$err_message = 'Invalid request sent to IDX Broker API, please re-install the IDX Broker Platinum plugin';
                    break;
                case 406:$err_message = 'Access key is missing. To obtain an access key, please visit your IDX Broker Platinum Dashboard';
                    break;
                case 412:$err_message = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the IDX Broker Platinum Dashboard, or call 800-421-9668.';
                    break;
                case 500:$err_message = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact 800-421-9668 if the problem persists.';
                    break;
                case 503:$err_message = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or call 800-421-9668 if the problem persists.';
                    break;
            }
        }
        return array("code" => $response_code, "error" => $err_message);
    }

    /**
     * IDX API Request
     */
    public function idx_api(
        $method,
        $apiversion = Initiate_Plugin::IDX_API_DEFAULT_VERSION,
        $level = 'clients',
        $params = array(),
        $expiration = 7200
    ) {
        $cache_key = 'idx_' . $method . '_cache';
        if (($data = get_transient($cache_key))) {
            return $data;
        }

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'accesskey' => get_option('idx_broker_apikey'),
            'outputtype' => 'json',
            'apiversion' => $apiversion,
            'pluginversion' => \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION,
        );

        $params = array_merge(array('timeout' => 120, 'sslverify' => false, 'headers' => $headers), $params);

        $url = Initiate_Plugin::IDX_API_URL . '/' . $level . '/' . $method;

        $response = wp_remote_get($url, $params);
        $response = (array) $response;

        extract($this->apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
        if (isset($error) && $error !== false) {
            if ($code == 401) {
                delete_transient($cache_key);
            }
            return new \WP_Error("idx_api_error", __("Error {$code}: $error"));
        } else {
            $data = (array) json_decode((string) $response['body']);
            set_transient($cache_key, $data, $expiration);
            return $data;
        }
    }

    /**
     * Clean IDX cached data
     *
     * @param void
     * @return void
     */
    public static function idx_clean_transients()
    {
        // clean old key before 1.1.6
        if (get_transient('idx_savedlink_cache')) {
            delete_transient('idx_savedlink_cache');
        }
        if (get_transient('idx_widget_cache')) {
            delete_transient('idx_widget_cache');
        }

        if (get_transient('idx_savedlinks_cache')) {
            delete_transient('idx_savedlinks_cache');
        }

        if (get_transient('idx_widgetsrc_cache')) {
            delete_transient('idx_widgetsrc_cache');
        }
        if (get_transient('idx_systemlinks_cache')) {
            delete_transient('idx_systemlinks_cache');
        }
        if (get_transient('idx_apiversion_cache')) {
            delete_transient('idx_apiversion_cache');
        }
        //ccz transients from idx-omnibar-get-locations.php
        if (get_transient('idx_cities/combinedActiveMLS_cache')) {
            delete_transient('idx_cities/combinedActiveMLS_cache');
        }
        if (get_transient('idx_counties/combinedActiveMLS_cache')) {
            delete_transient('idx_counties/combinedActiveMLS_cache');
        }
        if (get_transient('idx_zipcodes/combinedActiveMLS_cache')) {
            delete_transient('idx_zipcodes/combinedActiveMLS_cache');
        }
    }

}
