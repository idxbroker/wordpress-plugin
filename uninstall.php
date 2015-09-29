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
delete_transient('idx_savedlink_cache');
delete_transient('idx_widget_cache');
delete_transient('idx_savedlinks_cache');
delete_transient('idx_widgetsrc_cache');
delete_transient('idx_systemlinks_cache');
delete_transient('idx_apiversion_cache');
delete_transient('idx_cities/combinedActiveMLS_cache');
delete_transient('idx_counties/combinedActiveMLS_cache');
delete_transient('idx_zipcodes/combinedActiveMLS_cache');


// Drop our Custom Tables
global $wpdb;

$idx_posts_table = $wpdb->prefix."posts_idx";
$wpdb->query("DROP TABLE IF EXISTS $idx_posts_table");


// Delete our Dynamic Wrapper Pages
$page_id = get_option('idx_broker_dynamic_wrapper_page_id');
    if($page_id) {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }

