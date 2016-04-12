<?php
namespace IDX;

class Idx_Pages
{

    public function __construct(Idx_Api $idx_api)
    {
        //deletes all IDX pages for troubleshooting purposes
        // $this->delete_all_idx_pages();

        add_action('admin_init', array($this, 'show_idx_pages_metabox_by_default'));

        add_filter('post_type_link', array($this, 'post_type_link_filter_func'), 10, 2);
        add_filter('cron_schedules', array($this, 'add_fifteen_minutes_schedule'));

        //register hook for WP Cron to use
        add_action('idx-update-idx-pages', array($this, 'update_idx_pages'));

        add_action('init', array($this, 'register_idx_page_type'));
        add_action('admin_init', array($this, 'manage_idx_page_capabilities'));
        add_action('save_post', array($this, 'save_idx_page'), 1);
        add_action('save_post', array($this, 'set_wrapper_page'));
        add_action('add_meta_boxes', array($this, 'add_meta_box'));

        //schedule an IDX page update via WP cron
        $this->schedule_idx_page_update();

        $this->idx_api = $idx_api;
    }

    public $idx_api;
    public function add_fifteen_minutes_schedule()
    {
        $schedules['fifteenminutes'] = array(
            'interval' => 60 * 15, //fifteen minutes in seconds
            'display' => 'Fifteen Minutes',
        );

        return $schedules;
    }

   // $single = true for plugin settings refresh. Otherwise schedules hourly
   public static function schedule_idx_page_update($single = false)
   {
       if ($single) {
           return wp_schedule_single_event(time(), 'idx-update-idx-pages');
       }
       if (!wp_next_scheduled('idx-update-idx-pages')) {
           wp_schedule_event(time(), 'fifteenminutes', 'idx-update-idx-pages');
       }
   }

    //to be called on plugin deactivation
    public static function unschedule_idx_page_update()
    {
        wp_clear_scheduled_hook('idx-update-idx-pages');
    }

    //creates new idx pages not yet included and deletes irrelevant ones
    public function update_idx_pages()
    {
        $this->create_idx_pages();
        $this->delete_idx_pages();
    }

    public function register_idx_page_type()
    {

        //post_type labels
        $labels = array(
            'name' => 'IDX Pages',
            'singular_name' => 'IDX Page',
            'add_new' => 'Add IDX Page',
            'add_new_item' => 'Add New IDX Page',
            'edit_item' => 'Edit IDX Page',
            'new_item' => 'New IDX Page',
            'view_item' => 'View IDX Page',
            'search_items' => 'Search IDX Pages',
            'not_found' => 'No idx_pages found',
            'not_found_in_trash' => 'No idx_pages found in Trash',
            'parent_item_colon' => '',
            'parent' => 'Parent IDX Page',
        );

        //disable ability to add new or delete IDX Pages
        $capabilities = array(
            'publish_posts' => false,
            'edit_posts' => 'edit_idx_pages',
            'edit_others_posts' => 'edit_others_idx_pages',
            'delete_posts' => false,
            'delete_others_posts' => false,
            'read_private_posts' => 'read_private_idx_pages',
            'edit_post' => 'edit_idx_page',
            'delete_post' => false,
            'read_post' => 'read_idx_pages',
            'create_posts' => false,
        );

        $args = array(
            'label' => 'IDX Pages',
            'labels' => $labels,
            'public' => true,
            'show_in_menu' => 'idx-broker',
            'show_in_nav_menus' => true,
            'rewrite' => false,
            'capabilities' => $capabilities,
            'capability_type' => array('idx_page', 'idx_pages'),
            'supports' => array('excerpt', 'thumbnail'),
        );
        //register IDX Pages Post Type
        register_post_type('idx_page', $args);

    }

    public function manage_idx_page_capabilities()
    {
        // gets the role to add capabilities to
        $admin = get_role('administrator');
        $editor = get_role('editor');
        // replicate all the remapped capabilites from the custom post type
        $caps = array(
            'edit_idx_page',
            'edit_idx_pages',
            'edit_others_idx_pages',
            'publish_idx_pages',
            'read_idx_pages',
        );
        // give all the capabilities to the administrator
        foreach ($caps as $cap) {
            $admin->add_cap($cap);
        }
        // limited the capabilities to the editor or a custom role
        $editor->add_cap('edit_idx_page');
        $editor->add_cap('edit_idx_pages');
        $editor->add_cap('read_idx_pages');
    }

    public function create_idx_pages()
    {

        $saved_links = $this->idx_api->idx_api_get_savedlinks();
        $system_links = $this->idx_api->idx_api_get_systemlinks();

        if (!is_array($system_links) || !is_array($saved_links)) {
            return;
        }

        $idx_page_chunks = array_chunk(array_merge($saved_links, $system_links), 200);

        $existing_page_urls = $this->get_existing_idx_page_urls();

        foreach ($idx_page_chunks as $idx_page_chunk) {
            //for each chunk, create all idx pages within
            $this->create_pages_from_chunk($idx_page_chunk, $existing_page_urls);
        }
    }

    //use the chunk to create all the pages within (chunk is 200)
    public function create_pages_from_chunk($idx_page_chunk, $existing_page_urls)
    {
        foreach ($idx_page_chunk as $link) {
            if (!in_array($link->url, $existing_page_urls)) {
                if (!empty($link->name)) {
                    $name = $link->name;
                } else if ($link->linkTitle) {
                    $name = $link->linkTitle;
                }

                $post = array(
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_name' => $link->url,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_title' => $name,
                    'post_type' => 'idx_page',
                );

                // filter sanitize_tite so it returns the raw title
                add_filter('sanitize_title', array($this, 'sanitize_title_filter'), 10, 2);

                wp_insert_post($post);
            }
        }
    }

    /**
     * Removes sanitization on the post_name
     *
     * Without this the ":","/", and "." will be removed from post slugs
     *
     * @return string $raw_title title without sanitization applied
     */
    public function sanitize_title_filter($title, $raw_title)
    {
        return $raw_title;
    }

    /**
     * Deletes IDX pages that dont have a url or title matching a systemlink url or title
     *
     */
    public function delete_idx_pages()
    {

        $posts = get_posts(array('post_type' => 'idx_page', 'numberposts' => -1));

        if (empty($posts)) {
            return;
        }

        $saved_link_urls = $this->idx_api->all_saved_link_urls();

        $saved_link_names = $this->idx_api->all_saved_link_names();

        $system_link_urls = $this->idx_api->all_system_link_urls();

        $system_link_names = $this->idx_api->all_system_link_names();

        if (empty($system_link_urls) || empty($system_link_names) || empty($saved_link_urls) || empty($saved_link_names)) {
            return;
        }

        $idx_urls = array_merge($saved_link_urls, $system_link_urls);
        $idx_names = array_merge($saved_link_names, $system_link_names);

        foreach ($posts as $post) {
            // post_name oddly refers to permalink in the db
            // if an idx hosted page url or title has been changed,
            // delete the page from the wpdb
            // the updated page will be repopulated automatically
            if (!in_array($post->post_name, $idx_urls) || !in_array($post->post_title, $idx_names)) {
                wp_delete_post($post->ID);
            }
        }
    }

    //Keep post name (the idx url) from having slashes stripped out on save in UI
    public function save_idx_page($post_id)
    {
        $post = get_post($post_id);
        //only affect idx_page post type
        if ($post->post_type !== 'idx_page') {
            return;
        }
        //prevent infinite loop
        remove_action('save_post', array($this, 'save_idx_page'), 1);
        //force post_name to not lose slashes
        $update_to_post = array(
            'ID' => $post_id,
            'post_name' => $post->guid,
        );

        add_filter('sanitize_title', array($this, 'sanitize_title_filter'), 10, 2);
        //manually save post
        wp_update_post($update_to_post);

    }

    /**
     * Disables appending of the site url to the post permalink
     *
     * @return string $post_link
     */
    public function post_type_link_filter_func($post_link, $post)
    {

        if ('idx_page' == $post->post_type) {
            return $post->post_name;
        }

        return $post_link;
    }

    /**
     * Deletes all posts of the "idx_page" post type
     * This is called on uninstall of the plugin and when troubleshooting
     *
     * @return void
     */
    public static function delete_all_idx_pages()
    {

        $posts = get_posts(array('post_type' => 'idx_page', 'numberposts' => -1));

        if (empty($posts)) {
            return;
        }

        foreach ($posts as $post) {
            wp_delete_post($post->ID);
        }
    }

    /**
     * Returns an array of existing idx page urls
     *
     * These are the page urls in the wordpress database
     * not from the IDX dashboard
     *
     * @return array $existing urls of existing idx pages if any
     */
    public function get_existing_idx_page_urls()
    {

        $posts = get_posts(array('post_type' => 'idx_page', 'numberposts' => -1));

        $existing = array();

        if (empty($posts)) {
            return $existing;
        }

        foreach ($posts as $post) {
            $existing[] = $post->post_name;
        }

        return $existing;
    }

    public function show_idx_pages_metabox_by_default()
    {

        $user = wp_get_current_user();

        $user_first_login = get_user_meta($user->ID, 'idx_user_first_login', true);

        // Only update the user meta on the first login (after IDX features have been enabled).
        // This ensures that the user can hide the IDX Pages metabox again if they want
        if (!empty($user_first_login)) {
            return;
        }

        $hidden_metaboxes_on_nav_menus_page = (array) get_user_meta($user->ID, 'metaboxhidden_nav-menus', true);

        foreach ($hidden_metaboxes_on_nav_menus_page as $key => $value) {

            if ($value == 'add-idx_page') {
                unset($hidden_metaboxes_on_nav_menus_page[$key]);
            }
        }

        update_user_meta($user->ID, 'metaboxhidden_nav-menus', $hidden_metaboxes_on_nav_menus_page);

        // add a meta field to keep track of the first login
        update_user_meta($user->ID, 'idx_user_first_login', 'user_first_login_false');
    }

    public function display_wrapper_dropdown()
    {
        //only show dropdown if Platinum account or not saved link
        //(Lite does not support saved link wrappers)
        if ($this->idx_api->platinum_account_type() ||
            !$this->is_saved_link(get_the_ID())) {
            return true;
        }
    }

    public function wrapper_page_dropdown()
    {
        $post_id = get_the_ID();
        echo "<select class=\"idx-wrapper-page\" name=\"idx-wrapper-page\" style=\"width: 100%;\">";
        echo "<option value=\"none\"" .
        selected('none', get_post_meta($post_id, 'idx-wrapper-page', true)) .
        ">None</option>";
        $args = array(
            'numberposts' => -1,
            'post_type' => 'idx-wrapper',
        );
        $wrappers = get_posts($args);
        foreach ($wrappers as $wrapper) {
            $id = $wrapper->ID;
            $name = $wrapper->post_title;
            echo "<option value=\"$id\"" .
            selected($id, get_post_meta($post_id, 'idx-wrapper-page', true)) .
            ">$name</option>";
        }
        echo '</select>';
    }

    //dynamic wrapper requires the pageID, not the UID,
    //so we must strip out the account number from the UID
    public function convert_uid_to_id($uid)
    {
        return substr($uid, strpos($uid, '-') + 1);
    }

    public function wrapper_page_ui()
    {
        // add metabox interface when editing an IDX page (with none as an option)
        // This UI should display the current wrapper set
        // when saving a post, save the meta of which wrapper is set
        wp_nonce_field('idx-wrapper-page', 'idx-wrapper-page-nonce');
        $this->wrapper_page_dropdown();
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', 'jquery');
        wp_enqueue_script('idx-wrapper', plugins_url('../assets/js/idx-wrappers.min.js', __FILE__));
    }

    public function find_idx_url($post_id)
    {
        $post = get_post($post_id);
        $url = $post->post_name;
        $system_links = $this->idx_api->idx_api_get_systemlinks();
        $saved_links = $this->idx_api->idx_api_get_savedlinks();
        foreach ($system_links as $link) {
            if ($link->url === $url) {
                $uid = $link->uid;
                $name = $link->name;
                $id = $this->convert_uid_to_id($uid);
                return $id;
            }
        }
        foreach ($saved_links as $link) {
            if ($link->url === $url) {
                $id = $link->id;
                $name = $link->linkTitle;
                return $id;
            }
        }
    }

    public function is_saved_link($post_id)
    {
        $post = get_post($post_id);
        $url = $post->post_name;
        $saved_links = $this->idx_api->idx_api_get_savedlinks();
        foreach ($saved_links as $link) {
            if ($link->url === $url) {
                return true;
            }
        }
    }

    public function add_meta_box($post_type)
    {
        $post_types = array('idx_page'); //limit meta box to certain post types
        if (in_array($post_type, $post_types) && $this->display_wrapper_dropdown()) {
            add_meta_box(
                'set_wrapper_page',
                'Apply Page Wrapper',
                array($this, 'wrapper_page_ui'),
                $post_type,
                'side',
                'low'
            );
        }
    }

    public function set_wrapper_page($post_id)
    {
        //saved idx page ID
        if (empty($_POST)) {
            return;
        }
        if (empty($_POST['idx-wrapper-page'])) {
            return;
        }
        if (!$this->verify_permissions()) {
            return $post_id;
        }

        $meta_value = $_POST['idx-wrapper-page'];
        $meta_value = sanitize_text_field($meta_value);
        //Find the IDX Page ID by matching URLs
        $idx_page_id = $this->find_idx_url($post_id);

        //do not update wrapper if wrapper is none
        if ($meta_value === 'none') {
            return;
        } elseif ($meta_value === 'global') {
            $this->idx_api->set_wrapper($idx_page_id, '');
        }

        $wrapper_page_url = get_permalink($meta_value);

        //logic for what type of idx page is in Idx_Api class
        $this->idx_api->set_wrapper($idx_page_id, $wrapper_page_url);
        update_post_meta($post_id, 'idx-wrapper-page', $meta_value);
    }

    public function verify_permissions()
    {
        // Check if our nonce is set.
        if (!isset($_POST['idx-wrapper-page-nonce'])) {
            return false;
        }
        $nonce = $_POST['idx-wrapper-page-nonce'];
        if (!wp_verify_nonce($nonce, 'idx-wrapper-page')) {
            return false;
        }
        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }
        return true;
    }
}
