<?php
namespace IDX;

class Initiate_Plugin
{
    public function __construct()
    {
        $this->set_defaults();
        include 'backwards-compatibility.php';
        add_action('init', array($this, 'update_triggered'));
        add_action('wp_head', array($this, 'display_wpversion'));
        add_action('wp_head', array($this, 'idx_broker_activated'));
        add_filter("plugin_action_links_" . plugin_basename(dirname(dirname(__FILE__))) . '/idx-broker-platinum.php', array($this, 'idx_broker_platinum_plugin_actlinks'));
        //Setting the priority to 9 for admin_menu makes the Wrappers post type UI below the Settings link
        add_action('admin_menu', array($this, 'add_menu'), 9);
        add_action('admin_enqueue_scripts', array($this, 'idx_inject_script_and_style'));
        add_action('wp_ajax_idx_refresh_api', array($this, 'idx_refreshapi'));
        add_action('admin_menu', array($this, 'idx_broker_platinum_options_init'));
        add_action('wp_loaded', array($this, 'schedule_omnibar_update'));
        add_action('wp_loaded', array($this, 'migrate_old_table'));
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

    public function migrate_old_table()
    {
        global $wpdb;
        if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "posts_idx';") !== null) {
            new Migrate_Old_Table();
        }
    }

    public function plugin_updated()
    {
        if (get_option('idx-broker-plugin-version') < \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION) {
            return true;
        }
    }

    public function update_triggered()
    {
        if ($this->plugin_updated()) {
            $this->idx_omnibar_get_locations();
        }
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
        new \IDX\Omnibar\Get_Locations();
    }

    //Adds a comment declaring the version of the WordPress.
    public function display_wpversion()
    {
        echo "\n\n<!-- Wordpress Version ";
        echo bloginfo('version');
        echo " -->";
    }

    //Adds a comment declaring the version of the IDX Broker plugin if it is activated.
    public function idx_broker_activated()
    {
        echo "\n<!-- IDX Broker WordPress Plugin " . \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION . " Activated -->\n";

        echo "<!-- IDX Broker WordPress Plugin Wrapper Meta-->\n\n";
        global $post;
        //If wrapper, add noindex tag which is stripped out by our system
        if ($post && $post->post_type === 'wrappers') {
            echo "<meta name='idx-robot'>\n";
            echo "<meta name='robots' content='noindex,nofollow'>\n";
        }
    }

    public function idx_broker_platinum_plugin_actlinks($links)
    {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="admin.php?page=idx-broker">Settings</a>';
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

        /*
         *  Since we have custom links that can be added and deleted inside
         *  the IDX Broker admin, we need to grab them and set up the options
         *  to control them here.  First let's grab them, if the API is not blank.
         */

        if (get_option('idx_broker_apikey') != '') {
            $systemlinks = $this->Idx_Api->idx_api_get_systemlinks();
            if (is_wp_error($systemlinks)) {
                $api_error = $systemlinks->get_error_message();
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
        $this->Idx_Api->idx_clean_transients();
        update_option('idx_broker_apikey', $_REQUEST['idx_broker_apikey']);
        setcookie("api_refresh", 1, time() + 20);
        new \IDX\Omnibar\Get_Locations();
        die();
    }

/**
 * This adds the options page to the WP admin.
 *
 * @params void
 * @return Admin Menu
 */

    public function add_menu()
    {
        add_menu_page('IDX Broker Plugin Options', 'IDX Broker', 'administrator', 'idx-broker', array($this, 'idx_broker_platinum_admin_page'), 'dashicons-admin-home', 55.572);
        add_submenu_page('idx-broker', 'IDX Broker Plugin Options', 'Initial Settings', 'administrator', 'idx-broker', array($this, 'idx_broker_platinum_admin_page'));
    }

/**
 *  Function to add javascript and css into idx setting page
 *  @param string $page: the current page
 */
    public function idx_inject_script_and_style($page)
    {
        if ('toplevel_page_idx-broker' !== $page) {
            return;
        }
        add_action('admin_notices', array($this, 'idx_instructions'));
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

    public function idx_instructions()
    {
        echo '<div class="updated">';
        echo '<p>';
        echo 'Not sure how to integrate IDX content? See <a href="http://support.idxbroker.com/customer/portal/articles/1917460-wordpress-plugin">this knowledgebase article.</a>';
        echo '</p>';
        echo '</div>';
    }
}
