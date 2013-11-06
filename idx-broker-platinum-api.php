<?php
/**
 * 
 * Using our web services function, lets get the system links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */

function idx_platinum_get_systemlinks () { 
	
	if(!get_option('idx_broker_apikey')) {
		return false;
	}
	
	$system_links = array();
	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);
	
	$response = $request->request('https://api.idxbroker.com/clients/systemlinks', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;
	// did it error out?
   	if ($response['response']['code'] < 200 || $response['response']['code'] > 300 ) {
   		if($response['response']['code'] == 401) {
   			delete_transient('idx_widget_cache');	
   			return new WP_Error( 'idx_api_error',	__( 'The key entered is not a valid API key.<br/>Please remove any extra spaces from your key, or call 800-421-9668 to re-enable your account.' ));
   		} else {
   			if(function_exists('curl_init')) {
   				return new WP_Error( 'idx_api_error',	__( 'The API server is currently unavailable.<br />Please try again in several minutes. If the problem continues, please submit a ticket through our online knowledgebase at kb.idxbroker.com.' ));
   			} else {
   				return new WP_Error( 'idx_api_error',	__( 'PHP Curl extension is not enabled in your server.<br />Please enable it and try again. If the problem continues, please submit a ticket through our online knowledgebase at kb.idxbroker.com.' ));
   			}
   		}
		// display an error message here  
	} elseif (is_array($response['response']) && $response['response']['code'] == 200 && isset($response['body'])) {   
		$system_links = json_decode($response['body']);
	}
	set_transient('idx_systemlinks_cache', $system_links, 3600);

	return $system_links;
}


/**
 * 
 * Using our web services function, lets get saved links built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */

function idx_platinum_get_savedlinks () { 

	if(!get_option('idx_broker_apikey')) {
		return false;
	}
	
	$saved_links = array();
	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);

	$response = $request->request('https://api.idxbroker.com/clients/savedlinks', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;
	// did it error out?
   	if ($response['response']['code'] < 200 || $response['response']['code'] > 300 ) {
   		
   		if($response['response']['code'] == 401) {
   			delete_transient('idx_widget_cache');	
   			return new WP_Error( 'idx_api_error',	__( 'The key entered is not a valid API key.<br/>Please remove any extra spaces from your key, or call 800-421-9668 to re-enable your account.' ));
   		} else {
   		   	if(function_exists('curl_init')) {
   				return new WP_Error( 'idx_api_error',	__( 'The API server is currently unavailable.<br />Please try again in several minutes. If the problem continues, please submit a ticket through our online knowledgebase at kb.idxbroker.com.' ));
   			} else {
   				return new WP_Error( 'idx_api_error',	__( 'PHP Curl extension is not enabled in your server.<br />Please enable it and try again. If the problem continues, please submit a ticket through our online knowledgebase at kb.idxbroker.com.' ));
   			}   		
   		}
		// display an error message here  
	} elseif (is_array($response['response']) && $response['response']['code'] == 200 && isset($response['body'])) {   
		$saved_links = json_decode($response['body']);
	}
	set_transient('idx_savedlink_cache', $saved_links, 3600);
	
	return $saved_links;

}

/**
 * 
 * Using our web services function, lets get the widget details built in the middleware,
 * clean and prepare them, and return them in a new array for use.
 * 
 */

function idx_platinum_get_widgets () { 
	
	if(!get_option('idx_broker_apikey')) {
		return false;
	}
	
	$idx_widgets = array();
	$request = new WP_Http;
	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'accesskey' => get_option('idx_broker_apikey'),
		'outputtype' => 'json'  
	);

	$response = $request->request('https://api.idxbroker.com/clients/widgetsrc', array( 'sslverify' => false, 'headers' => $headers ));
	$response = (array)$response;
	// did it error out?
   	if ($response['response']['code'] < 200 || $response['response']['code'] > 300 ) {
   		delete_transient('idx_widget_cache');	
		// display an error message here  
	} elseif (is_array($response['response']) && $response['response']['code'] == 200 && isset($response['body'])) {   
		$idx_widgets = json_decode($response['body']);
	}  
	set_transient('idx_widget_cache', $idx_widgets, 3600);

	return $idx_widgets;
}

function check_curl_enabled() {
	return function_exists('curl_init');
}