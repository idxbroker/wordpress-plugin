<?php
// anytime this file is called, we reset the static wrapper cache as long as we have a valid API key, this is done in the background so returning a response isn't necessary
if (get_option('idx_broker_apikey')) {
	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);
	//$response = $request->request('https://api.idxbroker.com/clients/wrappercache', array('sslverify'=>false, 'headers' => $headers, 'method'=>'DELETE'));
	//$response = (array)$response;
}

/**
 * 
 * Using our web services function, lets get the system links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */
function idx_platinum_get_systemlinks () { 
	if(!get_option('idx_broker_apikey'))
		return false;
		//return new WP_Error('idx_api_error', __('Error Generic: Missing API Key, this is accessible from within the IDX Broker Platinum Dashboard'));

	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);

	$response = $request->request('https://api.idxbroker.com/clients/systemlinks', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;

	extract(apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
	if ($error !== false) {
		if ($code == 401)
			delete_transient('idx_systemlinks_cache');
		return new WP_Error("idx_api_error", __("Error {$code}: $error"));
	}
	else {
		$system_links = ($code == 200 && isset($response['body'])) ? json_decode($response['body']) : array();
		set_transient('idx_systemlinks_cache', $system_links, 3600);
		return $system_links;		
	}
} // end system links API call fn

/**
 * 
 * Using our web services function, lets get saved links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */
function idx_platinum_get_savedlinks () { 
	if(!get_option('idx_broker_apikey'))
		return false;
		//return new WP_Error('idx_api_error', __('Missing API Key, this is accessible from within the IDX Broker Platinum Dashboard'));

	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);

	$response = $request->request('https://api.idxbroker.com/clients/savedlinks', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;

	extract(apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
	if ($error !== false) {
		if ($code == 401)
			delete_transient('idx_savedlink_cache');
		return new WP_Error("idx_api_error", __("Error {$code}: $error"));
	}
	else {
		$saved_links = ($code == 200 && isset($response['body'])) ? json_decode($response['body']) : array();
		set_transient('idx_savedlink_cache', $saved_links, 3600);
		return $saved_links;		
	}
} // end saved links api call fn

/**
 * 
 * Using our web services function, lets get the widget details built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */
function idx_platinum_get_widgets () { 
	if(!get_option('idx_broker_apikey'))
		return false;
		
		//return new WP_Error('idx_api_error', __('Missing API Key, this is accessible from within the IDX Broker Platinum Dashboard'));

	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);

	$response = $request->request('https://api.idxbroker.com/clients/widgetsrc', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;

	extract(apiResponse($response)); // get code and error message if any, assigned to vars $code and $error
	if ($error !== false) {
		if ($code == 401)
			delete_transient('idx_widget_cache');
		return new WP_Error("idx_api_error", __("Error {$code}: $error"));
	}
	else {
		$idx_widgets = ($code == 200 && isset($response['body'])) ? json_decode($response['body']) : array();
		set_transient('idx_widget_cache', $idx_widgets, 3600);
		return $idx_widgets;		
	}
} // end get platinum widgets API call fn

/**
 * apiResponse handles the various replies we get from the IDX Broker API and returns appropriate error messages.
 * @param  [array] $response [response header from API call]
 * @return [array]           [keys: 'code' => response code, 'error' => false (default), or error message if one is found]
 */
function apiResponse ($response) {
	if (!is_array($response) || !$response)
		return array("code" => "Generic", "error" => "Unable to complete API call.");
	if (!function_exists('curl_init'))
		return array("code" => "PHP", "error" => "The cURL extension for PHP is not enabled on your server.<br />Please contact your developer and/or hosting provider.");
	
	$responseCode = $response['response']['code'];
	$errMessage = false;
   	if (is_numeric($responseCode)) {
   		switch ($responseCode) {
   			case 401:	$errMessage = 'Access key is invalid or has been revoked, please ensure there are no spaces in your key.<br />If the problem persists, please reset your API key in the IDX Broker Platinum Dashboard or call 800-421-9668.'; break;
   			case 403:
   			case 403.4:	$errMessage = 'API call generated from WordPress is not using SSL (HTTPS) to communicate.<br />Please contact your developer and/or hosting provider.';	break;
			case 405:
			case 409: 	$errMessage = 'Invalid request sent to IDX Broker API, please re-install the IDX Broker Platinum plugin'; break;
			case 406: 	$errMessage = 'Access key is missing. To obtain an access key, please visit your IDX Broker Platinum Dashboard'; break;
			case 412: 	$errMessage = 'Your account has exceeded the hourly access limit for your API key.<br />You may either wait and try again later, reset your API key in the IDX Broker Platinum Dashboard, or call 800-421-9668.'; break;
			case 500: 	$errMessage = 'General system error when attempting to communicate with the IDX Broker API, please try again in a few moments or contact 800-421-9668 if the problem persists.'; break;
			case 503: 	$errMessage = 'IDX Broker API is currently undergoing maintenance. Please try again in a few moments or call 800-421-9668 if the problem persists.'; break;
   		}
   	}
	return array("code" => $responseCode, "error" => $errMessage);
} // end apiResponse fn

function check_curl_enabled() {
	return function_exists('curl_init');
}