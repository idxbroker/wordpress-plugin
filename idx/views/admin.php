<?php
namespace IDX\Views;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

global $api_error;
$idx_api = new \IDX\Idx_Api();

// get wpl options if they exist
$wpl_options = get_option( 'plugin_wp_listings_settings' );

if ( ! $api_error ) {
	$system_links = $idx_api->idx_api_get_systemlinks();
	if ( is_wp_error( $system_links ) ) {
		$api_error = $system_links->get_error_message();
	}
}
/**
 * check wrapper page exist or not
 */
$wrapper_page_id  = get_option( 'idx_broker_dynamic_wrapper_page_id' );
$post_title       = '';
$wrapper_page_url = '';
if ( $wrapper_page_id ) {
	if ( ! get_page_uri( $wrapper_page_id ) ) {
		update_option( 'idx_broker_dynamic_wrapper_page_id', '', false );
		$wrapper_page_id = '';
	} else {
		$post_title       = get_post( $wrapper_page_id )->post_title;
		$wrapper_page_url = get_page_link( $wrapper_page_id );
	}
}

?>

<div id="app"></div>
