<?php
namespace IDX\Omnibar;

class Get_Locations
{
    public function __construct()
    {
        $api = get_option('idx_broker_apikey');
        if (empty($api)) {
            return;
        } else {
            $this->initiate_get_locations();
        }
    }

    private function initiate_get_locations()
    {
        $idx_api = new \IDX\Idx_Api();
        $system_links_call = $idx_api->idx_api_get_systemlinks();

        //grab responses and add JSON object container for easier parsing later
        $cities = '"cities" : ' . json_encode(
            $idx_api->idx_api(
                'cities/combinedActiveMLS',
                \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION,
                'clients',
                array(),
                10
            )
        );
        $counties = ', "counties" : ' . json_encode(
            $idx_api->idx_api(
                'counties/combinedActiveMLS',
                \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION,
                'clients',
                array(),
                10
            )
        );
        $zipcodes = ', "zipcodes" : ' . json_encode(
            $idx_api->idx_api(
                'zipcodes/combinedActiveMLS',
                \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION,
                'clients',
                array(),
                10
            )
        );
        //location lists together
        $locations = 'idxOmnibar({' . $cities . $counties . $zipcodes . '})';
        //test to confirm API call worked properly before updating JSON file etc.
        if (!empty($system_links_call) && empty($system_links_call->errors)) {
            file_put_contents(dirname(dirname(dirname(__FILE__))) . '/assets/js/locationlist.js', $locations);

            //update database with new results url
            //get base Url for client's results page for use on omnibar.js front end
            update_option('idx-results-url', $idx_api->system_results_url());
            //If invalid API key, display error
        } else {
            echo "<div class='error'><p>Invalid API Key. Please enter a valid API key in the IDX Broker Plugin Settings.</p></div>";
        }
    }

}
