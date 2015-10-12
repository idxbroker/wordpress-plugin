<?php
namespace IDX;

class Initiate_Plugin
{
    public function __construct()
    {
        $this->set_defaults();
        add_action('wp_enqueue_scripts', array($this, 'wp_api_script'));
        add_action('wp_head', array($this, 'display_wpversion'));
        add_action('wp_head', array($this, 'idx_broker_activated'));
        add_action('wp_enqueue_scripts', array($this, 'idx_register_styles'));
        add_filter("plugin_action_links_" . plugin_basename(dirname(__FILE__)) . '/idx-broker-platinum.php', array($this, 'idx_broker_platinum_plugin_actlinks'));
        add_action('admin_menu', array($this, 'idx_broker_platinum_menu'));
        add_action('admin_enqueue_scripts', array($this, 'idx_inject_script_and_style'));
        add_action('wp_ajax_idx_refresh_api', array($this, 'idx_refreshapi'));
        add_action('admin_menu', array($this, 'idx_broker_platinum_options_init'));
        add_action('wp_loaded', array($this, 'schedule_omnibar_update'));
        add_action('idx_omnibar_get_locations', array($this, 'idx_omnibar_get_locations'));

        //Instantiate Classes
        new Wrappers();
        new Idx_Pages();
        new Shortcodes();
        new Widgets\Create_Widgets();
        new Omnibar\Create_Omnibar();

        $this->Idx_Api = new Idx_Api();

    }

    const SHORTCODE_SYSTEM_LINK = 'idx-platinum-system-link';
    const SHORTCODE_SAVED_LINK = 'idx-platinum-saved-link';
    const SHORTCODE_WIDGET = 'idx-platinum-widget';
    const IDX_API_DEFAULT_VERSION = '1.2.0';
    const IDX_API_URL = 'https://api.idxbroker.com/';

    private function set_defaults()
    {
        //Prevent script timeout when API response is slow
        set_time_limit(0);

        // The function below adds a settings link to the plugin page.
        $plugin = plugin_basename(__FILE__);
        $api_error = false;
    }

    public function schedule_omnibar_update()
    {
        if (!wp_get_schedule('idx_omnibar_get_locations')) {
            //refresh omnibar fields once a day
            wp_schedule_event(time(), 'daily', 'idx_omnibar_get_locations');
        }
    }

    public function idx_omnibar_get_locations()
    {
        new \IDX\Omnibar\Get_Locations;
    }

    //Adds a comment declaring the version of the WordPress.
    public function display_wpversion()
    {
        echo "\n\n<!-- Wordpress Version ";
        echo bloginfo('version');
        echo " -->";
    }

/**  Register Map Libraries in case the user adds a map Widget to their site **/

    public function wp_api_script()
    {
        wp_register_script('custom-scriptBing', '//ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0', __FILE__);
        wp_register_script('custom-scriptLeaf', '//idxdyncdn.idxbroker.com/graphical/javascript/leaflet.js', __FILE__);
        wp_register_script('custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__);

        wp_enqueue_script('custom-scriptBing');
        wp_enqueue_script('custom-scriptLeaf');
        wp_enqueue_script('custom-scriptMQ');
    } // end wp_api_script fn

    //Adds a comment declaring the version of the IDX Broker plugin if it is activated.
    public function idx_broker_activated()
    {
        echo "\n<!-- IDX Broker WordPress Plugin " . \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION . " Activated -->\n\n";

        echo "\n<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
        global $post;
        if ($post && $post->ID && $post->ID == get_option('idx_broker_dynamic_wrapper_page_id')) {
            echo "<meta name='idx-robot'>\n";
            echo "<meta name='robots' content='noindex,nofollow'>\n";
        }
    }

    /**
     * Registers leaflet css
     * @return [type] [description]
     */
    public function idx_register_styles()
    {
        wp_register_style('cssLeaf', '//idxdyncdn.idxbroker.com/graphical/css/leaflet.css');
        wp_enqueue_style('cssLeaf');
    }

    public function idx_broker_platinum_plugin_actlinks($links)
    {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=idx-broker-platinum">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function idx_broker_platinum_options_init()
    {
        global $api_error;
        //register our settings
        register_setting('idx-platinum-settings-group', "idx_broker_apikey");
        register_setting('idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_name");
        register_setting('idx-platinum-settings-group', "idx_broker_dynamic_wrapper_page_id");
        register_setting('idx-platinum-settings-group', "idx_broker_admin_page_tab");

        /*
         *  Since we have custom links that can be added and deleted inside
         *  the IDX Broker admin, we need to grab them and set up the options
         *  to control them here.  First let's grab them, if the API is not blank.
         */

        if (get_option('idx_broker_apikey') != '') {
            $systemlinks = $this->Idx_Api->idx_api_get_systemlinks();
            $idx_pages = new Idx_Pages();
            if (is_wp_error($systemlinks)) {
                $api_error = $systemlinks->get_error_message();
                $systemlinks = '';
            }

            $savedlinks = $this->Idx_Api->idx_api_get_savedlinks();

            if (is_wp_error($savedlinks)) {
                $api_error = $savedlinks->get_error_message();
                $savedlinks = '';
            }

            if (isset($_COOKIE["api_refresh"]) && $_COOKIE["api_refresh"] == 1) {
                if (!empty($systemlinks)) {
                    $idx_pages->update_system_page_links($systemlinks);
                }
                if (!empty($savedlinks)) {
                    $idx_pages->update_saved_page_links($savedlinks);
                }
            }
        }
    }

/**
 * Function to delete existing cache. So API response in cache will be deleted
 *
 * @param void
 * @return void
 *
 */
    public function idx_refreshapi()
    {
        Idx_Api::idx_clean_transients();
        update_option('idx_broker_apikey', $_REQUEST['idx_broker_apikey']);
        setcookie("api_refresh", 1, time() + 20);
        $this->update_tab();
        new Omnibar\Get_Locations();
        die();
    }

/**
 * This adds the options page to the WP admin.
 *
 * @params void
 * @return Admin Menu
 */

    public function idx_broker_platinum_menu()
    {
        add_options_page('IDX Broker Plugin Options', 'IDX Broker', 'administrator', 'idx-broker-platinum', array($this, 'idx_broker_platinum_admin_page'));
    }

/**
 *  Function to add javascript and css into idx setting page
 *  @param string $page: the current page
 */
    public function idx_inject_script_and_style($page)
    {
        if ('settings_page_idx-broker-platinum' != $page) {
            return;
        }
        wp_enqueue_script('idxjs', plugins_url('../assets/js/idx-broker.js', __FILE__), 'jquery');
        wp_enqueue_style('idxcss', plugins_url('../assets/css/idx-broker.css', __FILE__));
    }

/**
 * This is tiggered and is run by idx_broker_menu, it's the actual IDX Broker Admin page and display.
 *
 * @params void
 * @return void
 */
    public function idx_broker_platinum_admin_page()
    {
        include plugin_dir_path(__FILE__) . 'views/admin.php';
    }

    public static function update_tab()
    {
        if ($_REQUEST['idx_broker_admin_page_tab']) {
            update_option('idx_broker_admin_page_tab', $_REQUEST['idx_broker_admin_page_tab']);
        }
    }
}
