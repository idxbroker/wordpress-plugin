<?php
/*
Plugin Name: IMPress for IDX Broker
Plugin URI: http://www.idxbroker.com
Description: Over 600 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!
Version: 2.3.5
Author: IDX Broker
Contributors: IDX, LLC
Author URI: http://www.idxbroker.com/
License: GPLv2 or later
 */

// Report all errors during development. Remember to hash out when sending to production.

// error_reporting(E_ALL);

new Idx_Broker_Plugin();
class Idx_Broker_Plugin
{
    //placed here for convenient updating
    const IDX_WP_PLUGIN_VERSION = '2.3.5';

    public function __construct()
    {
        define( 'IMPRESS_IDX_URL', plugin_dir_url( __FILE__ ) );
        define( 'IMPRESS_IDX_DIR', plugin_dir_path( __FILE__ ) );

        if ($this->php_version_check()) {
            //idx autoloader
            require_once 'idx' . DIRECTORY_SEPARATOR . 'autoloader.php';
            //composer autoload classes
            require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            
            new IDX\Initiate_Plugin();
            /** Function that is executed when plugin is activated. **/
            register_activation_hook(__FILE__, array($this, 'idx_activate'));
            register_deactivation_hook(__FILE__, array($this, 'idx_deactivate'));
            register_uninstall_hook(__FILE__, array('idx-broker-platinum', 'idx_uninstall'));
        }
    }

    /*
     * Check for versions less than PHP5.3 and display error
     */
    public function php_version_check()
    {
        if (PHP_VERSION < 5.4) {
            add_action('admin_init', array($this, 'idx_deactivate_plugin'));
            add_action('admin_notices', array($this, 'incompatible_message'));
            return false;
        } else {
            return true;
        }
    }

    public static function incompatible_message()
    {
        echo "<div class=\"error\"><br><div>You are using a deprecated version
            of PHP. This is incompatable with the IDX Broker plugin.
            For security reasons, please contact your host and upgrade to the
            latest stable version of PHP they offer. We recommend a minimum
            of PHP 5.5.<br>For more information on what versions of PHP are
            supported with security updates, see <a
            href=\"http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin\">
            this knowledgebase article</a> and PHP.net's
            <a href=\"http://php.net/supported-versions.php\"
            target=\"_blank\">supported versions page.</a>
            </div><br></div>";
    }

    public static function idx_deactivate_plugin()
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    public static function idx_activate()
    {
        if (!get_option('idx_results_url')) {
            add_option('idx_results_url');
        }

        if (get_site_option('idx_dismiss_review_prompt') != false) {
            delete_option('idx_dismiss_review_prompt');
        }

        if (get_site_option('idx_review_prompt_time') != false) {
            delete_option('idx_review_prompt_time');
        }

        //avoid 404 errors on custom posts such as wrappers by registering them then refreshing the permalink rules
        $idx_api = new \IDX\Idx_Api();
        $wrappers = new \IDX\Wrappers($idx_api);
        $wrappers->register_wrapper_post_type();

        flush_rewrite_rules();
    } // end idx_activate fn

    //deactivate hook
    public static function idx_deactivate()
    {
        //disable scheduled update for omnibar
        wp_clear_scheduled_hook('idx_omnibar_get_locations');

        //disable scheduled IDX Page Update as well
        \IDX\Idx_Pages::unschedule_idx_page_update();
    }

    public static function idx_uninstall()
    {
        $page_id = get_option('idx_broker_dynamic_wrapper_page_id');
        if ($page_id) {
            wp_delete_post($page_id, true);
            wp_trash_post($page_id);
        }
        //clear transients made by the plugin
        $idx_api = \IDX\Idx_Api;
        $idx_api->idx_clean_transients();
        //clean up db by removing all idx pages
        \IDX\Idx_Pages::delete_all_idx_pages();
    }
}
