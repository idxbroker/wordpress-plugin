<?php

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Delete our Options
delete_option( 'idx_broker_apikey' );
delete_option( 'idx_broker_admin_page_tab' );
delete_option( 'idx-results-url' );
delete_option( 'idx_savedlink_group' );
delete_option( 'idx_systemlink_group' );

// Delete our Transients
idx_clean_transients();

// Drop our Custom Tables
global $wpdb;
$wpdb->query( $wpdb->prepare('"DROP TABLE IF EXISTS '. $wpdb->prefix.'posts_idx'" ) );


// Delete our Dynamic Wrapper Pages
$page_id = get_option('idx_broker_dynamic_wrapper_page_id');
    if($page_id) {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }

