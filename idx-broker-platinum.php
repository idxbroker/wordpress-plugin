<?php
/*
Plugin Name: IDX Broker
Plugin URI: http://www.idxbroker.com
Description: Over 600 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!
Version: 1.2.0
Author: IDX Broker
Contributors: IDX, LLC
Author URI: http://www.idxbroker.com/
License: GPLv2 or later
*/

// Report all errors during development. Remember to hash out when sending to production.

//error_reporting(E_ALL);

//Prevent script timeout when API response is slow
set_time_limit(0);

// The function below adds a settings link to the plugin page.
$plugin = plugin_basename(__FILE__);
$api_error = false;


define('SHORTCODE_SYSTEM_LINK', 'idx-platinum-system-link');
define('SHORTCODE_SAVED_LINK', 'idx-platinum-saved-link');
define('SHORTCODE_WIDGET', 'idx-platinum-widget');
define('IDX__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IDX_WP_PLUGIN_VERSION', '1.2.0');
define('IDX_API_DEFAULT_VERSION', '1.2.0');
define('IDX_API_URL', 'https://api.idxbroker.com/');

//Adds a comment declaring the version of the WordPress.
add_action('wp_head', 'display_wpversion');
function display_wpversion() {
    echo "\n\n<!-- Wordpress Version ";
    echo bloginfo('version');
    echo " -->";
}

//Adds legacy start and stop tag function only when original IDX plugin is not installed
add_action('wp_head', 'idx_original_plugin_check');
function idx_original_plugin_check() {
    if (function_exists('idx_start')) {
        echo '';
    } else {
        function idx_start() {
            return '<div id="idxStart" style="display: none;"></div>';
        }
        function idx_stop() {
            return '<div id="idxStop" style="display: none;"></div>';
        }
    }
}

/**  Register Map Libraries in case the user adds a map Widget to their site **/
add_action( 'wp_enqueue_scripts', 'wp_api_script' );
function wp_api_script() {
    wp_register_script( 'custom-scriptBing', '//ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0', __FILE__ ) ;
    wp_register_script( 'custom-scriptLeaf', '//idxdyncdn.idxbroker.com/graphical/javascript/leaflet.js', __FILE__ );
    wp_register_script( 'custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__ );

    wp_enqueue_script( 'custom-scriptBing' );
    wp_enqueue_script( 'custom-scriptLeaf' );
    wp_enqueue_script( 'custom-scriptMQ' );
} // end wp_api_script fn


/**
 * Registers leaflet css
 * @return [type] [description]
 */
add_action('wp_enqueue_scripts', 'idx_register_styles'); // calls the above function
function idx_register_styles () {
    wp_register_style('cssLeaf', '//idxdyncdn.idxbroker.com/graphical/css/leaflet.css');
    wp_enqueue_style('cssLeaf');
}


/** Function that is executed when plugin is activated. **/
register_activation_hook( __FILE__, 'idx_activate');
function idx_activate() {
    global $wpdb;
    if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."posts_idx'") != $wpdb->prefix.'posts_idx') {
        $sql = "CREATE TABLE " . $wpdb->prefix."posts_idx" . " (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) NOT NULL,
                `uid` varchar(255) NOT NULL,
                `link_type` int(11) NOT NULL COMMENT '0 for system link and 1 for saved link',
                PRIMARY KEY (`id`)
                )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    } // end if

    if(! get_option('idx-results-url')){
        add_option('idx-results-url');
    }
    include 'omnibar/idx-omnibar-get-locations.php';
} // end idx_activate fn


register_uninstall_hook(__FILE__, 'idx_uninstall');
function idx_uninstall() {
    $page_id = get_option('idx_broker_dynamic_wrapper_page_id');
    if($page_id) {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }
    idx_clean_transients();
}


//Adds a comment declaring the version of the IDX Broker plugin if it is activated.
add_action('wp_head', 'idx_broker_activated');
function idx_broker_activated() {
    echo "\n<!-- IDX Broker WordPress Plugin ". IDX_WP_PLUGIN_VERSION . " Activated -->\n\n";

    echo "\n<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
    global $post;
    if ($post && $post->ID && $post->ID == get_option('idx_broker_dynamic_wrapper_page_id')) {
        echo "<meta name='idx-robot'>\n";
        echo "<meta name='robots' content='noindex,nofollow'>\n";
    }
}

add_filter("plugin_action_links_$plugin", 'idx_broker_platinum_plugin_actlinks' );
function idx_broker_platinum_plugin_actlinks( $links ) {
    // Add a link to this plugin's settings page
    $settings_link = '<a href="options-general.php?page=idx-broker-platinum">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}


add_action('admin_menu', 'idx_broker_platinum_options_init' );


add_action('wp_ajax_idx_refresh_api', 'idx_refreshapi' );
add_action('wp_ajax_idx_update_links', 'idx_update_links' );
add_action('wp_ajax_idx_update_systemlinks', 'idx_update_systemlinks' );
add_action('wp_ajax_idx_update_savedlinks', 'idx_update_savedlinks' );

//Adding shortcodes

add_shortcode('idx-platinum-link', 'show_link');

add_shortcode('idx-platinum-saved-link', 'show_saved_link');
add_shortcode('idx-platinum-widget', 'show_widget');

//Register the idx button
add_action('init', 'idx_buttons');

/**
 * registers the buttons for use
 * @param array $buttons
 */
function register_idx_buttons($buttons) {
    // inserts a separator between existing buttons and our new one
    array_push($buttons, "|", "idx_button");
    return $buttons;
}

/**
 * filters the tinyMCE buttons and adds our custom buttons
 */
function idx_buttons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
        return;
    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true' ) {
        // filter the tinyMCE buttons and add our own
        add_filter("mce_external_plugins", "add_idx_tinymce_plugin");
        add_filter('mce_buttons', 'register_idx_buttons');
    } // end if rich editing true
} // end idx_buttons fn

/**
 * add the button to the tinyMCE bar
 * @param array $plugin_array
 */
function add_idx_tinymce_plugin($plugin_array) {
    $plugin_array['idx_button'] = plugins_url('js/idx-buttons.js', __FILE__);
    return $plugin_array;
}

/**
 * This adds the options page to the WP admin.
 *
 * @params void
 * @return Admin Menu
 */
add_action('admin_menu', 'idx_broker_platinum_menu');
function idx_broker_platinum_menu() {
    add_options_page('IDX Broker Plugin Options', 'IDX Broker', 'administrator', 'idx-broker-platinum', 'idx_broker_platinum_admin_page');
}

//Include dependecy files for IDX plugin
if (file_exists(IDX__PLUGIN_DIR. '/idx-broker-platinum-api.php')) {
    include IDX__PLUGIN_DIR . '/idx-broker-platinum-api.php';
} else {
    echo '<!-- Couldn\'t find the form template. //-->' . "\n";
}

if (file_exists(IDX__PLUGIN_DIR . '/idx-broker-widgets.php')) {
    include IDX__PLUGIN_DIR . '/idx-broker-widgets.php';
} else {
    echo '<!-- Couldn\'t find the form template. //-->' . "\n";
}

/**
* This function runs on plugin activation.  It sets up all options that will need to be
* saved that we know of on install, including cid, pass, domain, and main nav links from
* the idx broker system.
*
* @params void
* @return void
*/

function idx_broker_platinum_options_init() {
    global $api_error;
    //register our settings
    register_setting( 'idx-platinum-settings-group', "idx_broker_apikey" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_name" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_id" );
    register_setting( 'idx-platinum-settings-group', "idx_broker_admin_page_tab" );

    /*
     *  Since we have custom links that can be added and deleted inside
     *  the IDX Broker admin, we need to grab them and set up the options
     *  to control them here.  First let's grab them, if the API is not blank.
     */

    if (get_option('idx_broker_apikey') != '') {
        $systemlinks = idx_api_get_systemlinks();
        if( is_wp_error($systemlinks) ) {
            $api_error = $systemlinks->get_error_message();
            $systemlinks = '';
        }

        $savedlinks = idx_api_get_savedlinks();

        if( is_wp_error($savedlinks) ) {
            $api_error = $savedlinks->get_error_message();
            $savedlinks = '';
        }

        if(isset($_COOKIE["api_refresh"]) && $_COOKIE["api_refresh"] == 1) {
            if (! empty($systemlinks)) {
                update_system_page_links($systemlinks);
            }
            if (! empty($savedlinks)) {
                update_saved_page_links($savedlinks);
            }
        }
    }
}

/**
 *  Function to add javascript and css into idx setting page
 *  @param string $page: the current page
 */
add_action( 'admin_enqueue_scripts', 'idx_inject_script_and_style' );
function idx_inject_script_and_style($page)
{
    if( 'settings_page_idx-broker-platinum' != $page ) {
        return;
    }
    wp_enqueue_script('idxjs', plugins_url('js/idxbroker.js', __FILE__), 'jquery');
    wp_enqueue_style('idxcss', plugins_url('css/idxbroker.css', __FILE__));
}


add_action( 'wp_ajax_create_dynamic_page', 'idx_ajax_create_dynamic_page' );
function idx_ajax_create_dynamic_page()
{

    // default page content
    $post_content = '<div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div>';

    // get theme to check start/stop tag
    $isThemeIncludeIdxTag = false;
    $template_root = get_theme_root().'/'.get_stylesheet();

    $files = scandir( $template_root );

    foreach ($files as $file)
    {
        $path = $template_root . '/' . $file;
        if (is_file($path) && preg_match('/.*\.php/',$file))
        {
            $content = file_get_contents($template_root . '/' . $file);
            if (preg_match('/<div[^>\n]+?id=[\'"]idxstart[\'"].*?(\/>|><\/div>)/i', $content))
            {
                if(preg_match('/<div[^>\n]+?id=[\'"]idxstop[\'"].*?(\/>|><\/div>)/i',$content))
                {
                    $isThemeIncludeIdxTag = true;
                    break;
                }
            }
        }
    }
    if ($isThemeIncludeIdxTag)
        $post_content = '';
    $post_content .= '<style>.entry-title{display:none;}</style>';
    $post_title = $_POST['post_title'] ? $_POST['post_title'] : 'IDX Dynamic Wrapper Page';
    $new_post = array(
        'post_title' => $post_title,
        'post_name' => $post_title,
        'post_content' => $post_content,
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    if ($_POST['wrapper_page_id'])
    {
        $new_post['ID'] = $_POST['wrapper_page_id'];
    }
    $wrapper_page_id = wp_insert_post($new_post);
    update_option('idx_broker_dynamic_wrapper_page_name', $post_title);
    update_option('idx_broker_dynamic_wrapper_page_id', $wrapper_page_id);
    update_tab();
    die(json_encode(array("wrapper_page_id"=>$wrapper_page_id, "wrapper_page_name" => $post_title))) ;
}

add_action( 'wp_ajax_delete_dynamic_page', 'idx_ajax_delete_dynamic_page' );
function idx_ajax_delete_dynamic_page() {
    if ($_POST['wrapper_page_id'])
    {
        wp_delete_post($_POST['wrapper_page_id'], true);
        wp_trash_post($_POST['wrapper_page_id']);
    }
    update_tab();
    die();
}

add_filter( 'get_pages','idx_pages_filter');

function idx_pages_check($page) {
    return $page->ID != get_option('idx_broker_dynamic_wrapper_page_id');
};

function idx_pages_filter($pages) {
    if (get_option('idx_broker_dynamic_wrapper_page_id')) {
        return array_filter($pages, "idx_pages_check");
    } else {
        return $pages;
    }
}

/**
 * Function to updated the system links data in posts and postmeta table
 * @param object $systemlinks
 */
function update_system_page_links($systemlinks) {
    global $wpdb;
    foreach($systemlinks as $systemlink){
        $post_id = $wpdb->get_var("SELECT post_id from ".$wpdb->prefix."posts_idx WHERE uid = '$systemlink->uid' AND link_type = 0");
        if($post_id) {
            //update the system links
            $rows_updated = $wpdb->update($wpdb->postmeta, array('meta_value' => $systemlink->url), array('post_id' => $post_id));
            $post_title = str_replace('_', ' ', $systemlink->name);
            $post_name = str_replace('', '_', $systemlink->name);
            $wpdb->update($wpdb->posts, array('post_title' => $post_title,
                    'post_name' => $post_name), array('ID' => $post_id));
        }
    }
}

/**
 * Function to updated the saved links data in posts and postmeta table
 * @param object $savedlinks
 */
function update_saved_page_links($savedlinks) {
    global $wpdb;
    foreach($savedlinks as $savedlink){
        $post_id = $wpdb->get_var("SELECT post_id from ".$wpdb->prefix."posts_idx WHERE uid = '$savedlink->uid' AND link_type = 1");
        if($post_id) {
            //update the saved links
            $wpdb->update($wpdb->postmeta, array('meta_value' => $savedlink->url), array('post_id' => $post_id));
            $post_title = str_replace('_', ' ', $savedlink->linkTitle);
            $post_name = str_replace('', '_', $savedlink->linkName);
            $wpdb->update($wpdb->posts, array('post_title' => $post_title,
                    'post_name' => $post_name), array('ID' => $post_id));
        }
    }
}

/**
 * This is tiggered and is run by idx_broker_menu, it's the actual IDX Broker Admin page and display.
 *
 * @params void
 * @return void
*/
function idx_broker_platinum_admin_page() {
    include(IDX__PLUGIN_DIR . '/views/admin.php');
}

/**
 * Function to delete existing cache. So API response in cache will be deleted
 *
 * @param void
 * @return void
 *
 */
function idx_refreshapi()
{
    idx_clean_transients();
    update_option('idx_broker_apikey',$_REQUEST['idx_broker_apikey']);
    setcookie("api_refresh", 1, time()+20);
    update_tab();
    include 'omnibar/idx-omnibar-get-locations.php';
    die();
}
/**
 * Clean IDX cached data
 *
 * @param void
 * @return void
 */
function idx_clean_transients()
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
}

/**
 *
 * Function to update the links from IDX API
 * Based upon button click the respective sections of links saved to database and create pages
 *
 * @param void
 * @return void
 */
function idx_update_links() {
    if(isset($_REQUEST['idx_savedlink_group']) && $_REQUEST['idx_savedlink_group'] == 'on') {
        update_option('idx_savedlink_group', 1);
    } else {
        update_option('idx_savedlink_group', 0);
    }

    if(isset($_REQUEST['idx_systemlink_group']) && $_REQUEST['idx_systemlink_group'] == 'on') {
        update_option('idx_systemlink_group', 1);
    } else {
        update_option('idx_systemlink_group', 0);
    }
    update_systemlinks();
    update_savedlinks();
    update_tab();
    die();
}

/**
 * This function will allow users to create page using saved links and
 * display in their main navigation.
 *
 *  @params void
 *  @return void
 */
function idx_update_systemlinks() {
    update_systemlinks();
    update_tab();
    die();
}

/**
 *
 * Function to update System links from IDX API
 * Based upon click, the links saved to database and create pages
 *
 * @param void
 * @return void
 */
function update_systemlinks() {
    global $wpdb;
    if(isset($_REQUEST['idx_systemlink_group'])) {
        update_option('idx_systemlink_group', 1);
    } else {
        update_option('idx_systemlink_group', 0);
    }
    if (!isset($wpdb->posts_idx)) {
        $wpdb->posts_idx = $wpdb->prefix . 'posts_idx';
    }

    $my_links = get_my_system_links();
    $new_links = array();
    unset($_REQUEST['idx_systemlink_group']);
    unset($_REQUEST['idx_savedlink_group']);

    $systemLink = array();
    $systemLinkNames = array();
    $systemLinkStr = urldecode($_REQUEST['idx_system_links']);
    $systemLinkNamesStr = urldecode($_REQUEST['idx_system_links_names']);
    if ($systemLinkStr != '') {
        $postVariables = explode('&', $systemLinkStr);
        foreach ($postVariables as $link) {
            list($key,$val) = explode('=',$link);
            $systemLink[$key] = $val;
        }
    }
    if ($systemLinkNamesStr != '') {
        $postVariables = explode('&', $systemLinkNamesStr);
        foreach ($postVariables as $name) {
            list($key,$val) = explode('=',$name);
            $systemLinkNames[$key] = $val;
        }
    }
    foreach ($systemLink as $submitted_link_name => $url) {
        //Checkbox is checked
        if (check_system_link($submitted_link_name)) {
            $uid = str_replace('idx_platinum_system_', '', $submitted_link_name);
            preg_match('/.+\/.+/', $url, $matches);
            $name = $systemLinkNames[$submitted_link_name.'_name'];
            $new_links[] = $uid;
            if($row = $wpdb->get_row("SELECT id,post_id FROM ".$wpdb->prefix."posts_idx WHERE uid = '$uid' ", ARRAY_A) ) {
                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_title' => $name,
                        'post_type' => 'page',
                        'post_name' => $name
                    ),
                    array(
                        'ID' => $row['post_id']
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s'
                    ),
                    array(
                        '%d'
                    )
                );
                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_key' => '_links_to',
                        'meta_value' => $url,
                    ),
                    array(
                        'post_id' => $row['post_id']
                    ),
                    array(
                        '%s',
                        '%s'
                    ),
                    array(
                        '%d'
                    )
                );
            }
            else {
                // Insert into post table
                $wpdb->insert(
                    $wpdb->posts,
                    array(
                        'post_title' => $name,
                        'post_type' => 'page',
                        'post_name' => $name
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s'
                    )
                );
                $post_id = $wpdb->insert_id;

                // Insert into post meta
                $wpdb->insert(
                    $wpdb->postmeta,
                    array(
                        'meta_key' => '_links_to',
                        'meta_value' => $url,
                        'post_id' => $wpdb->insert_id
                    ),
                    array(
                        '%s',
                        '%s',
                        '%d'
                    )
                );

                //Insert into mapping table
                $wpdb->insert(
                    $wpdb->posts_idx,
                    array(
                            'post_id' => $post_id,
                            'uid' => $uid,
                            'link_type' => 0
                    ),
                    array(
                        '%d',
                        '%s',
                        '%d'
                    )
                );
            }
        }
    }
    $uids_to_delete = array_diff($my_links, $new_links);
    if($uids_to_delete > 0) {
        delete_pages_byuid($uids_to_delete, 0);
    }
}

/**
 * FUnction to check if a link is system link or not
 * @param link name $link_name
 */
function check_system_link($link_name) {
    if(strpos($link_name, 'idx_platinum_system') !== false) {
        return true;
    } else {
        return false;
    }
}


function check_saved_link($link_name)
{
    if(strpos($link_name, 'idx_platinum_saved') !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * FUnction to get current system links
 */
function get_my_system_links() {
    global $wpdb;
    return $wpdb->get_col("SELECT uid from ".$wpdb->prefix."posts_idx where link_type = 0");
}

/**
 * FUnction to delete pages by passing uid(from API).
 *
 * @param string $uids
 * @param int $link_type type of link 0 for system and 1 for saved
 */
function delete_pages_byuid($uids,$link_type = 0) {
    global $wpdb;
    $uid_string = "";

    if(count($uids) > 0) {
        foreach($uids as $uid) {
            $uid_string .= "'$uid',";
        }
        $uid_string = rtrim($uid_string,',');
        $pages_to_delete = $wpdb->get_col("SELECT post_id from ".$wpdb->prefix."posts_idx where uid IN ($uid_string) AND link_type = $link_type");
        if($wpdb->query("DELETE from ".$wpdb->prefix."posts_idx where uid IN ($uid_string) AND link_type = $link_type") !== false) {
            foreach($pages_to_delete as $page) {
                wp_delete_post($page,true);
                $wpdb->query("DELETE from ".$wpdb->prefix."postmeta where post_id = $page");
            }
        }
        return true;
    }
    return false;
}

/**
 *
 * Function to update Saved links from IDX API
 * Based upon click, the links saved to database and create pages
 *
 * @param void
 * @return void
 */
function idx_update_savedlinks() {
    update_savedlinks();
    update_tab();
    die();
}

/**
 *
 * Function to update System links from IDX API
 * Based upon click, the links saved to database and create pages
 *
 * @param void
 * @return void
 */
function update_savedlinks() {
    global $wpdb;

    if(isset($_REQUEST['idx_savedlink_group'])) {
        update_option('idx_savedlink_group', 1);
    } else {
        update_option('idx_savedlink_group', 0);
    }
    if (!isset($wpdb->posts_idx)) {
        $wpdb->posts_idx = $wpdb->prefix . 'posts_idx';
    }
    $my_links = get_my_saved_links();
    $new_links = array();

    unset($_REQUEST['idx_savedlink_group']);
    unset($_REQUEST['idx_systemlink_group']);
    $saveLinks = array();
    $saveLinksNames = array();
    $saveLinksStr = urldecode($_REQUEST['idx_saved_links']);
    $saveLinksNamesStr = urldecode($_REQUEST['idx_saved_links_names']);
    if ($saveLinksStr != '')
    {
        $postVariables = explode('&', $saveLinksStr);
        foreach ($postVariables as $link) {
            list($key,$val) = explode('=',$link);
            $saveLinks[$key] = $val;
        }
    }
    if ($saveLinksNamesStr != '')
    {
        $postVariables = explode('&', $saveLinksNamesStr);
        foreach ($postVariables as $names) {
            list($key,$val) = explode('=',$names);
            $saveLinksNames[$key] = urldecode($val);
        }
    }
    foreach ($saveLinks as $submitted_link_name => $url) {
        //Checkbox is checked
        if (check_saved_link($submitted_link_name)) {
            $uid = str_replace('idx_platinum_saved_', '', $submitted_link_name);
            preg_match('/i\/.+/', $url, $matches);
            $name = $saveLinksNames[$submitted_link_name . '_name'];
            $new_links[] = $uid;
            if($row = $wpdb->get_row("SELECT id,post_id FROM ".$wpdb->prefix."posts_idx WHERE uid = '$uid' ", ARRAY_A) ) {
                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_title' => $name,
                        'post_type' => 'page',
                        'post_name' => $name
                    ),
                    array(
                        'ID' => $row['post_id']
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s'
                    ),
                    array(
                        '%d'
                    )
                );
                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_key' => '_links_to',
                        'meta_value' => $url,
                    ),
                    array(
                        'post_id' => $row['post_id']
                    ),
                    array(
                        '%s',
                        '%s'
                    ),
                    array(
                        '%d'
                    )
                );
            } else {
                // Insert into post table
                $wpdb->insert(
                    $wpdb->posts,
                    array(
                        'post_title' => $name,
                        'post_type' => 'page',
                        'post_name' => $name
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s'
                    )
                );
                $post_id = $wpdb->insert_id;

                // Insert into post meta
                $wpdb->insert(
                    $wpdb->postmeta,
                    array(
                        'meta_key' => '_links_to',
                        'meta_value' => $url,
                        'post_id' => $wpdb->insert_id
                    ),
                    array(
                        '%s',
                        '%s',
                        '%d'
                    )
                );

                //Insert into mapping table
                $wpdb->insert(
                    $wpdb->posts_idx,
                    array(
                        'post_id' => $post_id,
                        'uid' => $uid,
                        'link_type' => 1
                    ),
                    array(
                        '%d',
                        '%s',
                        '%d'
                    )
                );
            }
        }
    }
    $uids_to_delete = array_diff($my_links, $new_links);

    if($uids_to_delete > 0) {
        delete_pages_byuid($uids_to_delete, 1);
    }
}


function update_tab()
{
    if ($_REQUEST['idx_broker_admin_page_tab']) {
        update_option('idx_broker_admin_page_tab', $_REQUEST['idx_broker_admin_page_tab']);
    }
}

/**
 * FUnction to get current saved links
 */
function get_my_saved_links() {
    global $wpdb;
    return $wpdb->get_col("SELECT uid from ".$wpdb->prefix."posts_idx where link_type = 1");
}

// Compat functions for WP < 2.8
if ( !function_exists( 'esc_attr' ) ) {
    function esc_attr( $attr ) {
        return attribute_escape( $attr );
    }
    function esc_url( $url ) {
        return clean_url( $url );
    }
}

/**
 * Function to get meta data of created pages uisng IDX settings page
 *
 * @params void
 * @return String Page/Post URL
 */
function idxplatinum_get_page_links_to_meta () {
    global $wpdb, $page_links_to_cache, $blog_id;

    if ( !isset( $page_links_to_cache[$blog_id] ) )
        $links_to = idxplatinum_get_post_meta_by_key( '_links_to' );
    else
        return $page_links_to_cache[$blog_id];

    if ( !$links_to ) {
        $page_links_to_cache[$blog_id] = false;
        return false;
    }

    foreach ( (array) $links_to as $link )
        $page_links_to_cache[$blog_id][$link->post_id] = $link->meta_value;

    return $page_links_to_cache[$blog_id];
}

/**
 * Function to override permalink tab in post/page section of Wordpress
 *
 * @params string $link
 * @params object post details
 * @return string Page/Post URL
 */
function idxplatinum_filter_links_to_pages ($link, $post) {
    $page_links_to_cache = idxplatinum_get_page_links_to_meta();

    // Really strange, but page_link gives us an ID and post_link gives us a post object
    $id = isset( $post->ID ) ? $post->ID : $post;
    if ( isset($page_links_to_cache[$id]) )
        $link = esc_url( $page_links_to_cache[$id] );

    return $link;
}

/**
 * Function to redirect the page based upon _links_to_ attribute
 *
 * @param void
 * @return void
 */
add_action( 'template_redirect', 'idxplatinum_redirect_links_to_pages');
function idxplatinum_redirect_links_to_pages() {
    if ( !is_single() && !is_page() )
        return;

    global $wp_query;

    $link = get_post_meta( $wp_query->post->ID, '_links_to', true );
    if ( !$link )
        return;

    $redirect_type = get_post_meta( $wp_query->post->ID, '_links_to_type', true );
    $redirect_type = ( $redirect_type = '302' ) ? '302' : '301';
    wp_redirect( $link, $redirect_type );

    exit;
}

/**
 * Function to highlight the page links
 *
 * @param array $pages
 * @return array $pages
 */
function idxplatinum_page_links_to_highlight_tabs( $pages ) {
    // remove wrapper page
    $page_links_to_cache = idxplatinum_get_page_links_to_meta();
    $page_links_to_target_cache = idxplatinum_get_page_links_to_targets();
    if ( !$page_links_to_cache && !$page_links_to_target_cache )
        return $pages;

    $this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $targets = array();

    foreach ( (array) $page_links_to_cache as $id => $page ) {
        if ( isset( $page_links_to_target_cache[$id] ) )
            $targets[$page] = $page_links_to_target_cache[$id];
        if ( str_replace( 'http://www.', 'http://', $this_url ) == str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) == str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {
            $highlight = true;
            $current_page = esc_url( $page );
        }
    }

    if ( count( $targets ) ) {
        foreach ( $targets as  $p => $t ) {
            $p = esc_url( $p );
            $t = esc_attr( $t );
            $pages = str_replace( '<a href="' . $p . '" ', '<a href="' . $p . '" target="' . $t . '" ', $pages );
        }
    }

    global $highlight;

    if ( $highlight ) {
        $pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting
        $pages = preg_replace( '|<li class="([^"]+)"><a href="' . $current_page . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );
    }

    return $pages;
}

/**
 * Function to get page _link _to_ targets
 *
 * @param void
 * @return string page meta value
 */
function idxplatinum_get_page_links_to_targets () {
    global $wpdb, $page_links_to_target_cache, $blog_id;

    if ( !isset( $page_links_to_target_cache[$blog_id] ) )
        $links_to = idxplatinum_get_post_meta_by_key( '_links_to_target' );
    else
        return $page_links_to_target_cache[$blog_id];

    if ( !$links_to ) {
        $page_links_to_target_cache[$blog_id] = false;
        return false;
    }

    foreach ( (array) $links_to as $link )
        $page_links_to_target_cache[$blog_id][$link->post_id] = $link->meta_value;

    return $page_links_to_target_cache[$blog_id];
}

/**
 * Functiom to get post meta by key
 *
 * @param string $key
 * @return string meta value
 */
function idxplatinum_get_post_meta_by_key( $key ) {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );
}

/**
 * Function to delete saved IDX page IDs from option table
 *
 * @param integer page_id
 * @return void
 *
 */
function idxplatinum_update_pages($post_ID) {
    global $wpdb;

    $wpdb->query("DELETE from ".$wpdb->prefix."posts_idx where post_id = $post_ID");
    delete_post_meta( $post_ID, '_links_to' );
    delete_post_meta( $post_ID, '_links_to_target' );
    delete_post_meta( $post_ID, '_links_to_type' );
}

/**
 * Function to delete meta table if post/page is deleted by user
 *
 * @param integer $post_ID
 * @return integer $post_ID
 */
function idxplatinum_plt_save_meta_box( $post_ID ) {
    if ( wp_verify_nonce( isset($_REQUEST['_idx_pl2_nonce']), 'idxplatinum_plt' ) ) {
        if ( isset( $_POST['idx_links_to'] ) && strlen( $_POST['idx_links_to'] ) > 0 && $_POST['idx_links_to'] !== 'http://' ) {
            $link = stripslashes( $_POST['idx_links_to'] );

            if ( 0 === strpos( $link, 'www.' ) )
                $link = 'http://' . $link; // Starts with www., so add http://

            update_post_meta( $post_ID, '_links_to', $link );

            if ( isset( $_POST['idx_links_to_new_window'] ) )
                update_post_meta( $post_ID, '_links_to_target', '_blank' );
            else
                delete_post_meta( $post_ID, '_links_to_target' );

            if ( isset( $_POST['idx_links_to_302'] ) )
                update_post_meta( $post_ID, '_links_to_type', '302' );
            else
                delete_post_meta( $post_ID, '_links_to_type' );
        } else {
            delete_post_meta( $post_ID, '_links_to' );
            delete_post_meta( $post_ID, '_links_to_target' );
            delete_post_meta( $post_ID, '_links_to_type' );
        }
    }
    return $post_ID;
}

/**
 * Function to display warning message in permalink page
 *
 * @param void
 * @return void
 *
 */
function idxplatinum_notice() {
    global $current_screen;
    echo '<div id="message" class="error"><p><strong>Note that your IDX Broker page links are not governed by WordPress Permalinks. To apply changes to your IDX Broker URLs, you must login to your IDX Broker Control Panel.</strong></p></div>';
}

/**
 * Function to generate permalink warning message
 *
 * @param void
 * @return void
 */
function permalink_update_warning () {
    if(isset($_POST['permalink_structure']) || isset($_POST['category_base'])) {
        add_action('admin_notices', 'idxplatinum_notice');
    }
}

/**
 * Function to show a idx link with shortcode of type:
 * [idx-platinum-link title="title here"]
 *
 * @param array $atts
 * @return html code for showing the link/ bool false
 */
function show_link($atts) {
    extract( shortcode_atts( array(
            'title' => NULL
    ), $atts ) );

    if(!is_null($title)) {
        $page = get_page_by_title($title);
        $permalink = get_permalink($page->ID);
        return '<a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a>';
    } else {
        return false;
    }
}

/**
 * FUnction to show a idx system link with shortcode of type:
 * [idx-platinum-system-link title="title here"]
 *
 * @param array $atts
 * @return string|boolean
 */
add_shortcode('idx-platinum-system-link', 'show_system_link');
function show_system_link($atts) {
    extract( shortcode_atts( array(
            'id' => NULL,
            'title' => NULL,
    ), $atts ) );

    if(!is_null($id)) {
        $link = idx_get_link_by_uid($id, 0);
        if(is_object($link)) {
            if(!is_null($title)) {
                $link->name = $title;
            }
            return '<a href="'.$link->url.'">'.$link->name.'</a>';
        }
    } else {
        return false;
    }
}

/**
 * FUnction to show a idx ssaved link with shortcode of type:
 * [idx-platinum-saved-link title="title here"]
 *
 * @param array $atts
 * @return string|boolean
 */
function show_saved_link($atts) {
    extract( shortcode_atts( array(
            'id' => NULL,
            'title' => NULL
    ), $atts ) );

    if(!is_null($id)) {
        $link = idx_get_link_by_uid($id, 1);
        if(is_object($link)) {
            if(!is_null($title)) {
                $link->name = $title;
            }
            return '<a href="'.$link->url.'">'.$link->name.'</a>';
        }
    } else {
        return false;
    }
}

/**
 * Function to get the widget code by title
 *
 * @param string $title
 * @return html code for showing the widget
 */
function idx_get_link_by_uid($uid, $type = 0) {
    if($type == 0) {
        // if the cache has expired, send an API request to update them. Cache expires after 2 hours.
        if (! get_transient('idx_systemlinks_cache') )
            idx_api_get_systemlinks();
        $idx_links = get_transient('idx_systemlinks_cache');
    } elseif ($type == 1) {
        if (get_transient('idx_savedlinks_cache')) {
            delete_transient('idx_savedlinks_cache');
        }
        $idx_links = idx_api_get_savedlinks();
    }

    $selected_link = '';

    if($idx_links) {
        foreach($idx_links as $link) {
            if(strcmp($link->uid, $uid) == 0) {
                $selected_link = $link;
            }
        }
    }
    return $selected_link;
}

/**
 * Function to show a idx link with shortcode of type:
 * [idx-platinum-link title="widget title here"]
 *
 * @param array $atts
 * @return html code for showing the widget/ bool false
 */
function show_widget($atts) {
    extract( shortcode_atts( array(
            'id' => NULL
    ), $atts ) );

    if(!is_null($id)) {
        return get_widget_by_uid($id);
    } else {
        return false;
    }
}

/**
 * Function to get the widget code by title
 *
 * @param string $title
 * @return html code for showing the widget
 */
function get_widget_by_uid($uid) {
    $idx_widgets = idx_api_get_widgetsrc();
    $idx_widget_code = null;

    if($idx_widgets) {
        foreach($idx_widgets as $widget) {
            if(strcmp($widget->uid, $uid) == 0) {
                $idx_widget_link = $widget->url;
                $idx_widget_code =  '<script src="'.$idx_widget_link.'"></script>';
                return $idx_widget_code;
            }
        }
    } else {
        return $idx_widget_code;
    }
}

/**
 * Function to print the system/saved link shortcodes.
 *
 * @param int $link_type 0 for system link and 1 for saved link
 */
function show_link_short_codes($link_type = 0) {
    $available_shortcodes = '';

    if($link_type === 0) {
        $short_code = SHORTCODE_SYSTEM_LINK;
        $idx_links = idx_api_get_systemlinks();
    } elseif($link_type == 1) {
        $short_code = SHORTCODE_SAVED_LINK;
        $idx_links = idx_api_get_savedlinks();
    } else {
        return false;
    }

    if(count($idx_links) > 0 && is_array($idx_links)) {
        foreach ($idx_links as $idx_link) {
            if ($link_type === 0) {
                $available_shortcodes .= get_system_link_html($idx_link);
            }
            if($link_type == 1) {
                $available_shortcodes .= get_saved_link_html($idx_link);
            }
        }
    } else {
        $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
    }
    echo $available_shortcodes;
}

/**
 * Function to return the HTM for displaying each system link
 * @param object $idx_link
 * @return string
 */
function get_system_link_html($idx_link) {
    $available_shortcodes = "";

    if ($idx_link->systemresults != 1) {
        $link_short_code = '['.SHORTCODE_SYSTEM_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->name.'"]';
        $available_shortcodes .= '<div class="each_shortcode_row">';
        $available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';
        $available_shortcodes .= '<span>'.$idx_link->name.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>
        &nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->name).'&short_code='.urlencode($link_short_code).'">change title</a>
        </span>';
        $available_shortcodes .= '</div>';
    }
    return $available_shortcodes;
}

/**
 * Function to return the HTM for displaying each saved link
 * @param object $idx_link
 * @return string
 */
function get_saved_link_html($idx_link) {
    $available_shortcodes = "";
    $link_short_code = '['.SHORTCODE_SAVED_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->linkName.'"]';
    $available_shortcodes .= '<div class="each_shortcode_row">';
    $available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';
    $available_shortcodes .= '<span>'.$idx_link->linkName.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>
    &nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->linkName).'&short_code='.urlencode($link_short_code).'">change title</a>
    </span>';

    $available_shortcodes .= '</div>';

    return $available_shortcodes;
}

/**
 * Function to print the shortcodes of all the widgets
 */
function show_widget_shortcodes() {
    $idx_widgets = get_transient('idx_widgetsrc_cache');
    $available_shortcodes = '';

    if($idx_widgets) {
        foreach($idx_widgets as $widget) {
            $widget_shortcode = '['.SHORTCODE_WIDGET.' id ="'.$widget->uid.'"]';
            $available_shortcodes .= '<div class="each_shortcode_row">';
            $available_shortcodes .= '<input type="hidden" id=\''.$widget->uid.'\' value=\''.$widget_shortcode.'\'>';
            $available_shortcodes .= '<span>'.$widget->name.'&nbsp;<a name="'.$widget->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$widget->uid.'\')">insert</a></span>';
            $available_shortcodes .= '</div>';
        }
    } else {
        $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
    }
    echo $available_shortcodes;
}


add_action('save_post', 'idxplatinum_plt_save_meta_box');
add_action('before_delete_post', 'idxplatinum_update_pages');
add_action('init', 'permalink_update_warning');
add_filter('wp_list_pages', 'idxplatinum_page_links_to_highlight_tabs', 9);
add_filter('page_link', 'idxplatinum_filter_links_to_pages', 20, 2);
add_filter('post_link', 'idxplatinum_filter_links_to_pages', 20, 2);

/**
* Add Omnibar Search Widget:
*/
include 'omnibar/idx-omnibar-widget.php';

