<?php
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}
/**
 * Contains uninstall routines to clean up plugin data on deletion.
 */
function idx_delete_plugin_data() {
	// Delete the assigned dynamic wrapper page ID. Legacy - we remove all idx-wrapper posts later.
	$page_id = get_option( 'idx_broker_dynamic_wrapper_page_id' );
	if ( $page_id ) {
		wp_delete_post( $page_id, true );
	}

	global $wpdb;
	// Delete pseudo-transients.
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%idx_%_cache' ) );
	// Delete omnibar data.
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%idx_omnibar%' ) );
	// Delete middleware widgets.
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%widget_idx%' ) );
	// Delete dismissed notices.
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", '%idx-notice-dismissed%' ) );
	// Delete any other idx_ prefixed options. *Excludes API key.
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s AND option_name NOT LIKE %s", 'idx_%', 'idx_broker_apikey' ) );
	// Delete the autocomplete table (new rest api autcomplete).
	$autocomplete_table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';
	$wpdb->query( "DROP TABLE IF EXISTS $autocomplete_table_name" );

	// Delete all IDX page posts.
	$idx_pages = get_posts(
		array(
			'post_type'   => 'idx_page',
			'numberposts' => -1,
			'post_status' => 'any',
		)
	);
	foreach ( $idx_pages as $post ) {
		wp_delete_post( $post->ID, true );
	}

	// Delete all wrapper posts.
	$idx_wrappers = get_posts(
		array(
			'post_type'   => 'idx-wrapper',
			'numberposts' => -1,
		)
	);
	foreach ( $idx_wrappers as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

// Run cleanup method.
idx_delete_plugin_data();

if ( ! is_plugin_active( 'wp-listings/plugin.php' ) ) {
	include_once 'add-ons/listings/uninstall.php';
}
