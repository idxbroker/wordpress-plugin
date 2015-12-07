<?php
namespace IDX;

class Initiate_Plugin
{
    public function __construct()
    {
        $this->set_defaults();
        $this->Idx_Api = new Idx_Api();

        add_action('init', array($this, 'update_triggered'));
        add_action('wp_head', array($this, 'display_wpversion'));
        add_action('wp_head', array($this, 'idx_broker_activated'));
        add_filter("plugin_action_links_" . plugin_basename(dirname(dirname(__FILE__))) . '/idx-broker-platinum.php', array($this, 'idx_broker_platinum_plugin_actlinks'));
        //Setting the priority to 9 for admin_menu makes the Wrappers post type UI below the Settings link
        add_action('admin_menu', array($this, 'add_menu'), 9);
        add_action('admin_menu', array($this, 'idx_broker_platinum_options_init'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 999.125);
        add_action('admin_enqueue_scripts', array($this, 'idx_inject_script_and_style'));
        add_action('wp_ajax_idx_refresh_api', array($this, 'idx_refreshapi'));

        add_action('wp_loaded', array($this, 'schedule_omnibar_update'));
        add_action('idx_omnibar_get_locations', array($this, 'idx_omnibar_get_locations'));
        add_action('idx_migrate_old_table', array($this, 'migrate_old_table'));

        include 'backwards-compatibility.php';

        //Instantiate Classes
        new Wrappers();
        new Idx_Pages();
        new Widgets\Create_Idx_Widgets();
        new Shortcodes\Register_Idx_Shortcodes();
        new Widgets\Create_Impress_Widgets();
        new Shortcodes\Register_Impress_Shortcodes();
        new Widgets\Omnibar\Create_Omnibar();
        new Shortcodes\Shortcode_Ui();
        new Help();
    }

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

    public function schedule_migrate_old_table()
    {
        global $wpdb;
        if ($wpdb->get_var("SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'") !== null) {
            if (!wp_get_schedule('idx_migrate_old_table')) {
                wp_schedule_single_event(time(), 'idx_migrate_old_table');
            }
        }
    }

    public function migrate_old_table()
    {
        new Migrate_Old_Table();
    }

    public function plugin_updated()
    {
        if (!get_option('idx-broker-plugin-version') || get_option('idx-broker-plugin-version') < \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION) {
            return true;
        }
    }

    public function update_triggered()
    {
        if ($this->plugin_updated()) {
            //update db option and update omnibar data
            update_option('idx-broker-plugin-version', \Idx_Broker_Plugin::IDX_WP_PLUGIN_VERSION);
            //clear old api cache
            $idx_api = new Idx_Api();
            $idx_api->idx_clean_transients();
            $this->idx_omnibar_get_locations();
            return add_action('wp_loaded', array($this, 'schedule_migrate_old_table'));
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
        new \IDX\Widgets\Omnibar\Get_Locations();
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
        if ($post && $post->post_type === 'idx-wrapper') {
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
        new \IDX\Widgets\Omnibar\Get_Locations();
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
        add_menu_page('IMPress for IDX Broker Settings', 'IMPress', 'administrator', 'idx-broker', array($this, 'idx_broker_platinum_admin_page'), 'dashicons-admin-home', 55.572);
        add_submenu_page('idx-broker', 'IMPress for IDX Broker Plugin Options', 'Initial Settings', 'administrator', 'idx-broker', array($this, 'idx_broker_platinum_admin_page'));
        $this->add_upgrade_center_link();
    }

/**
 * This adds the idx menu items to the admin bar for quick access.
 *
 * @params void
 * @return Admin Menu
 */
    public function add_admin_bar_menu($wp_admin_bar)
    {
        $args = array(
            'id' => 'idx_admin_bar_menu',
            'title' => '<span class="dashicons-before dashicons-admin-home impress-admin-bar-menu" style="vertical-align:bottom;margin-right:5px;top:5px;position:relative;"></span>IMPress',
            'parent' => false,
            'href' => admin_url('admin.php?page=idx-broker'),
            'meta' => array('html' => '<style>.impress-admin-bar-menu{color: rgba(240,245,250,.6); #wp-admin-bar-idx_admin_bar_menu:hover { color: #00b9eb; }</style>'),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'idx_admin_bar_menu_item_1',
            'title' => 'IDX Control Panel',
            'parent' => 'idx_admin_bar_menu',
            'href' => 'https://middleware.idxbroker.com/mgmt/login.php',
            'meta' => array('target' => '_blank'),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'idx_admin_bar_menu_item_2',
            'title' => 'Knowledgebase',
            'parent' => 'idx_admin_bar_menu',
            'href' => 'http://support.idxbroker.com',
            'meta' => array('target' => '_blank'),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'idx_admin_bar_menu_item_3',
            'title' => 'Initial Settings',
            'parent' => 'idx_admin_bar_menu',
            'href' => admin_url('admin.php?page=idx-broker'),
        );
        $wp_admin_bar->add_node($args);
        $args = array(
            'id' => 'idx_admin_bar_menu_item_4',
            'title' => "Upgrade Account<i class=\"fa fa-arrow-up update-plugins\" style=\"padding: 0 5px 0 4px;font-weight: 100;margin-left:2.6px;font-family: FontAwesome;background-color: #d54e21;    border-radius: 10px; color: #fff;font-size: 9px; line-height: 17px;\"></i>",
            'parent' => 'idx_admin_bar_menu',
            'href' => 'https://middleware.idxbroker.com/mgmt/upgrade',
            'meta' => array(
                'target' => '_blank',
            ),
        );
        if (!$this->Idx_Api->platinum_account_type()) {
            $wp_admin_bar->add_node($args);
        }
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

    /**
     * As WP does not allow external links in the admin menu, this JavaScript adds a link manually.
     *
     * @params void
     * @return void
     */
    public function add_upgrade_center_link()
    {
        //Only load if account is not Platinum level
        if (!$this->Idx_Api->platinum_account_type()) {
            wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
            $html = "<li><a href=\"https://middleware.idxbroker.com/mgmt/upgrade\" target=\"_blank\">Upgrade Account<i class=\"fa fa-arrow-up update-plugins\" style=\"padding: 0 5px 0 4px;font-weight: 100;margin: 0 0 0 2.6px;\"></i></a>";
            echo <<<EOD
            <script>window.addEventListener('DOMContentLoaded',function(){
                document.querySelector('.wp-has-submenu.toplevel_page_idx-broker ul').innerHTML += '$html';
            });</script>
EOD;
        }
    }
}
