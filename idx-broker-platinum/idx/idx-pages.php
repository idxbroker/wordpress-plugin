<?php
namespace IDX;

class Idx_Pages
{

    public function __construct()
    {
        add_filter('get_pages', array($this, 'idx_pages_filter'));
        add_action('init', array($this, 'permalink_update_warning'));
        add_filter('post_link', array($this, 'idxplatinum_filter_links_to_pages'), 20, 2);
        add_filter('page_link', array($this, 'idxplatinum_filter_links_to_pages'), 20, 2);
        add_action('template_redirect', array($this, 'idxplatinum_redirect_links_to_pages'));
        add_filter('wp_list_pages', array($this, 'idxplatinum_page_links_to_highlight_tabs', 9));
        add_action('before_delete_post', array($this, 'idxplatinum_update_pages'));
        add_action('save_post', array($this, 'idxplatinum_plt_save_meta_box'));
        add_action('wp_ajax_idx_update_links', array($this, 'idx_update_links'));
        add_action('wp_ajax_idx_update_systemlinks', array($this, 'idx_update_systemlinks'));
        add_action('wp_ajax_idx_update_savedlinks', array($this, 'idx_update_savedlinks'));
    }

    public function idx_pages_check($page)
    {
        return $page->ID != get_option('idx_broker_dynamic_wrapper_page_id');
    }

    public function idx_pages_filter($pages)
    {
        if (get_option('idx_broker_dynamic_wrapper_page_id')) {
            return array_filter($pages, array($this, "idx_pages_check"));
        } else {
            return $pages;
        }
    }

    /**
     * Function to updated the system links data in posts and postmeta table
     * @param object $systemlinks
     */
    public function update_system_page_links($systemlinks)
    {
        global $wpdb;
        foreach ($systemlinks as $systemlink) {
            $post_id = $wpdb->get_var("SELECT post_id from " . $wpdb->prefix . "posts_idx WHERE uid = '$systemlink->uid' AND link_type = 0");
            if ($post_id) {
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
    public function update_saved_page_links($savedlinks)
    {
        global $wpdb;
        foreach ($savedlinks as $savedlink) {
            $post_id = $wpdb->get_var("SELECT post_id from " . $wpdb->prefix . "posts_idx WHERE uid = '$savedlink->uid' AND link_type = 1");
            if ($post_id) {
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
     *
     * Function to update the links from IDX API
     * Based upon button click the respective sections of links saved to database and create pages
     *
     * @param void
     * @return void
     */
    public function idx_update_links()
    {
        if (isset($_REQUEST['idx_savedlink_group']) && $_REQUEST['idx_savedlink_group'] == 'on') {
            update_option('idx_savedlink_group', 1);
        } else {
            update_option('idx_savedlink_group', 0);
        }

        if (isset($_REQUEST['idx_systemlink_group']) && $_REQUEST['idx_systemlink_group'] == 'on') {
            update_option('idx_systemlink_group', 1);
        } else {
            update_option('idx_systemlink_group', 0);
        }
        $this->update_systemlinks();
        $this->update_savedlinks();
        Initiate_Plugin::update_tab();
        die();
    }

    /**
     * This public function will allow users to create page using saved links and
     * display in their main navigation.
     *
     *  @params void
     *  @return void
     */
    public function idx_update_systemlinks()
    {
        $this->update_systemlinks();
        Initiate_Plugin::update_tab();
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
    public function update_systemlinks()
    {
        global $wpdb;
        if (isset($_REQUEST['idx_systemlink_group'])) {
            update_option('idx_systemlink_group', 1);
        } else {
            update_option('idx_systemlink_group', 0);
        }
        if (!isset($wpdb->posts_idx)) {
            $wpdb->posts_idx = $wpdb->prefix . 'posts_idx';
        }

        $my_links = $this->get_my_system_links();
        $new_links = array();
        unset($_REQUEST['idx_systemlink_group']);
        unset($_REQUEST['idx_savedlink_group']);

        $system_links = array();
        $system_link_names = array();
        $system_links_string = urldecode($_REQUEST['idx_system_links']);
        $system_link_names_string = urldecode($_REQUEST['idx_system_links_names']);
        if ($system_links_string != '') {
            $post_variables = explode('&', $system_links_string);
            foreach ($post_variables as $link) {
                list($key, $val) = explode('=', $link);
                $system_links[$key] = $val;
            }
        }
        if ($system_link_names_string != '') {
            $post_variables = explode('&', $system_link_names_string);
            foreach ($post_variables as $name) {
                list($key, $val) = explode('=', $name);
                $system_link_names[$key] = $val;
            }
        }
        foreach ($system_links as $submitted_link_name => $url) {
            //Checkbox is checked
            if ($this->check_system_link($submitted_link_name)) {
                $uid = str_replace('idx_platinum_system_', '', $submitted_link_name);
                preg_match('/.+\/.+/', $url, $matches);
                $name = $system_link_names[$submitted_link_name . '_name'];
                $new_links[] = $uid;
                if ($row = $wpdb->get_row("SELECT id,post_id FROM " . $wpdb->prefix . "posts_idx WHERE uid = '$uid' ", ARRAY_A)) {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_title' => $name,
                            'post_type' => 'page',
                            'post_name' => $name,
                        ),
                        array(
                            'ID' => $row['post_id'],
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                        ),
                        array(
                            '%d',
                        )
                    );
                    $wpdb->update(
                        $wpdb->postmeta,
                        array(
                            'meta_key' => '_links_to',
                            'meta_value' => $url,
                        ),
                        array(
                            'post_id' => $row['post_id'],
                        ),
                        array(
                            '%s',
                            '%s',
                        ),
                        array(
                            '%d',
                        )
                    );
                } else {
                    // Insert into post table
                    $wpdb->insert(
                        $wpdb->posts,
                        array(
                            'post_title' => $name,
                            'post_type' => 'page',
                            'post_name' => $name,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                        )
                    );
                    $post_id = $wpdb->insert_id;

                    // Insert into post meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        array(
                            'meta_key' => '_links_to',
                            'meta_value' => $url,
                            'post_id' => $wpdb->insert_id,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%d',
                        )
                    );

                    //Insert into mapping table
                    $wpdb->insert(
                        $wpdb->posts_idx,
                        array(
                            'post_id' => $post_id,
                            'uid' => $uid,
                            'link_type' => 0,
                        ),
                        array(
                            '%d',
                            '%s',
                            '%d',
                        )
                    );
                }
            }
        }
        $uids_to_delete = array_diff($my_links, $new_links);
        if ($uids_to_delete > 0) {
            $this->delete_pages_byuid($uids_to_delete, 0);
        }
    }

    /**
     * FUnction to check if a link is system link or not
     * @param link name $link_name
     */
    public function check_system_link($link_name)
    {
        if (strpos($link_name, 'idx_platinum_system') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function check_saved_link($link_name)
    {
        if (strpos($link_name, 'idx_platinum_saved') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * FUnction to get current system links
     */
    public function get_my_system_links()
    {
        global $wpdb;
        return $wpdb->get_col("SELECT uid from " . $wpdb->prefix . "posts_idx where link_type = 0");
    }

    /**
     * FUnction to delete pages by passing uid(from API).
     *
     * @param string $uids
     * @param int $link_type type of link 0 for system and 1 for saved
     */
    public function delete_pages_byuid($uids, $link_type = 0)
    {
        global $wpdb;
        $uid_string = "";

        if (count($uids) > 0) {
            foreach ($uids as $uid) {
                $uid_string .= "'$uid',";
            }
            $uid_string = rtrim($uid_string, ',');
            $pages_to_delete = $wpdb->get_col("SELECT post_id from " . $wpdb->prefix . "posts_idx where uid IN ($uid_string) AND link_type = $link_type");
            if ($wpdb->query("DELETE from " . $wpdb->prefix . "posts_idx where uid IN ($uid_string) AND link_type = $link_type") !== false) {
                foreach ($pages_to_delete as $page) {
                    wp_delete_post($page, true);
                    $wpdb->query("DELETE from " . $wpdb->prefix . "postmeta where post_id = $page");
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
    public function idx_update_savedlinks()
    {
        $this->update_savedlinks();
        Initiate_Plugin::update_tab();
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
    public function update_savedlinks()
    {
        global $wpdb;

        if (isset($_REQUEST['idx_savedlink_group'])) {
            update_option('idx_savedlink_group', 1);
        } else {
            update_option('idx_savedlink_group', 0);
        }
        if (!isset($wpdb->posts_idx)) {
            $wpdb->posts_idx = $wpdb->prefix . 'posts_idx';
        }
        $my_links = $this->get_my_saved_links();
        $new_links = array();

        unset($_REQUEST['idx_savedlink_group']);
        unset($_REQUEST['idx_systemlink_group']);
        $saved_links = array();
        $saved_link_names = array();
        $saved_links_string = urldecode($_REQUEST['idx_saved_links']);
        $saved_link_names_string = urldecode($_REQUEST['idx_saved_links_names']);
        if ($saved_links_string != '') {
            $post_variables = explode('&', $saved_links_string);
            foreach ($post_variables as $link) {
                list($key, $val) = explode('=', $link);
                $saved_links[$key] = $val;
            }
        }
        if ($saved_link_names_string != '') {
            $post_variables = explode('&', $saved_link_names_string);
            foreach ($post_variables as $names) {
                list($key, $val) = explode('=', $names);
                $saved_link_names[$key] = urldecode($val);
            }
        }
        foreach ($saved_links as $submitted_link_name => $url) {
            //Checkbox is checked
            if ($this->check_saved_link($submitted_link_name)) {
                $uid = str_replace('idx_platinum_saved_', '', $submitted_link_name);
                preg_match('/i\/.+/', $url, $matches);
                $name = $saved_link_names[$submitted_link_name . '_name'];
                $new_links[] = $uid;
                if ($row = $wpdb->get_row("SELECT id,post_id FROM " . $wpdb->prefix . "posts_idx WHERE uid = '$uid' ", ARRAY_A)) {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_title' => $name,
                            'post_type' => 'page',
                            'post_name' => $name,
                        ),
                        array(
                            'ID' => $row['post_id'],
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                        ),
                        array(
                            '%d',
                        )
                    );
                    $wpdb->update(
                        $wpdb->postmeta,
                        array(
                            'meta_key' => '_links_to',
                            'meta_value' => $url,
                        ),
                        array(
                            'post_id' => $row['post_id'],
                        ),
                        array(
                            '%s',
                            '%s',
                        ),
                        array(
                            '%d',
                        )
                    );
                } else {
                    // Insert into post table
                    $wpdb->insert(
                        $wpdb->posts,
                        array(
                            'post_title' => $name,
                            'post_type' => 'page',
                            'post_name' => $name,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%s',
                        )
                    );
                    $post_id = $wpdb->insert_id;

                    // Insert into post meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        array(
                            'meta_key' => '_links_to',
                            'meta_value' => $url,
                            'post_id' => $wpdb->insert_id,
                        ),
                        array(
                            '%s',
                            '%s',
                            '%d',
                        )
                    );

                    //Insert into mapping table
                    $wpdb->insert(
                        $wpdb->posts_idx,
                        array(
                            'post_id' => $post_id,
                            'uid' => $uid,
                            'link_type' => 1,
                        ),
                        array(
                            '%d',
                            '%s',
                            '%d',
                        )
                    );
                }
            }
        }
        $uids_to_delete = array_diff($my_links, $new_links);

        if ($uids_to_delete > 0) {
            $this->delete_pages_byuid($uids_to_delete, 1);
        }
    }

    /**
     * FUnction to get current saved links
     */
    public function get_my_saved_links()
    {
        global $wpdb;
        return $wpdb->get_col("SELECT uid from " . $wpdb->prefix . "posts_idx where link_type = 1");
    }

    /**
     * Function to get meta data of created pages uisng IDX settings page
     *
     * @params void
     * @return String Page/Post URL
     */
    public function idxplatinum_get_page_links_to_meta()
    {
        global $wpdb, $page_links_to_cache, $blog_id;

        if (!isset($page_links_to_cache[$blog_id])) {
            $links_to = $this->idxplatinum_get_post_meta_by_key('_links_to');
        } else {
            return $page_links_to_cache[$blog_id];
        }

        if (!$links_to) {
            $page_links_to_cache[$blog_id] = false;
            return false;
        }

        foreach ((array) $links_to as $link) {
            $page_links_to_cache[$blog_id][$link->post_id] = $link->meta_value;
        }

        return $page_links_to_cache[$blog_id];
    }

    /**
     * Function to override permalink tab in post/page section of Wordpress
     *
     * @params string $link
     * @params object post details
     * @return string Page/Post URL
     */
    public function idxplatinum_filter_links_to_pages($link, $post)
    {
        $page_links_to_cache = $this->idxplatinum_get_page_links_to_meta();

        // Really strange, but page_link gives us an ID and post_link gives us a post object
        $id = isset($post->ID) ? $post->ID : $post;
        if (isset($page_links_to_cache[$id])) {
            $link = esc_url($page_links_to_cache[$id]);
        }
        return $link;
    }

    /**
     * Function to redirect the page based upon _links_to_ attribute
     *
     * @param void
     * @return void
     */
    public function idxplatinum_redirect_links_to_pages()
    {
        if (!is_single() && !is_page()) {
            return;
        }

        global $wp_query;

        $link = get_post_meta($wp_query->post->ID, '_links_to', true);
        if (!$link) {
            return;
        }

        $redirect_type = get_post_meta($wp_query->post->ID, '_links_to_type', true);
        $redirect_type = ($redirect_type = '302') ? '302' : '301';
        wp_redirect($link, $redirect_type);

        exit;
    }

    /**
     * Function to highlight the page links
     *
     * @param array $pages
     * @return array $pages
     */
    public function idxplatinum_page_links_to_highlight_tabs($pages)
    {
        // remove wrapper page
        $page_links_to_cache = $this->idxplatinum_get_page_links_to_meta();
        $page_links_to_target_cache = $this->idxplatinum_get_page_links_to_targets();
        if (!$page_links_to_cache && !$page_links_to_target_cache) {
            return $pages;
        }

        $this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $targets = array();

        foreach ((array) $page_links_to_cache as $id => $page) {
            if (isset($page_links_to_target_cache[$id])) {
                $targets[$page] = $page_links_to_target_cache[$id];
            }

            if (str_replace('http://www.', 'http://', $this_url) == str_replace('http://www.', 'http://', $page) || (is_home() && str_replace('http://www.', 'http://', trailingslashit(get_bloginfo('url'))) == str_replace('http://www.', 'http://', trailingslashit($page)))) {
                $highlight = true;
                $current_page = esc_url($page);
            }
        }

        if (count($targets)) {
            foreach ($targets as $p => $t) {
                $p = esc_url($p);
                $t = esc_attr($t);
                $pages = str_replace('<a href="' . $p . '" ', '<a href="' . $p . '" target="' . $t . '" ', $pages);
            }
        }

        global $highlight;

        if ($highlight) {
            $pages = preg_replace('| class="([^"]+)current_page_item"|', ' class="$1"', $pages); // Kill default highlighting
            $pages = preg_replace('|<li class="([^"]+)"><a href="' . $current_page . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages);
        }

        return $pages;
    }

    /**
     * Function to get page _link _to_ targets
     *
     * @param void
     * @return string page meta value
     */
    public function idxplatinum_get_page_links_to_targets()
    {
        global $wpdb, $page_links_to_target_cache, $blog_id;

        if (!isset($page_links_to_target_cache[$blog_id])) {
            $links_to = $this->idxplatinum_get_post_meta_by_key('_links_to_target');
        } else {
            return $page_links_to_target_cache[$blog_id];
        }

        if (!$links_to) {
            $page_links_to_target_cache[$blog_id] = false;
            return false;
        }

        foreach ((array) $links_to as $link) {
            $page_links_to_target_cache[$blog_id][$link->post_id] = $link->meta_value;
        }

        return $page_links_to_target_cache[$blog_id];
    }

    /**
     * Functiom to get post meta by key
     *
     * @param string $key
     * @return string meta value
     */
    public function idxplatinum_get_post_meta_by_key($key)
    {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key));
    }

    /**
     * Function to delete saved IDX page IDs from option table
     *
     * @param integer page_id
     * @return void
     *
     */
    public function idxplatinum_update_pages($post_ID)
    {
        global $wpdb;

        $wpdb->query("DELETE from " . $wpdb->prefix . "posts_idx where post_id = $post_ID");
        delete_post_meta($post_ID, '_links_to');
        delete_post_meta($post_ID, '_links_to_target');
        delete_post_meta($post_ID, '_links_to_type');
    }

    /**
     * Function to delete meta table if post/page is deleted by user
     *
     * @param integer $post_ID
     * @return integer $post_ID
     */
    public function idxplatinum_plt_save_meta_box($post_ID)
    {
        if (wp_verify_nonce(isset($_REQUEST['_idx_pl2_nonce']), 'idxplatinum_plt')) {
            if (isset($_POST['idx_links_to']) && strlen($_POST['idx_links_to']) > 0 && $_POST['idx_links_to'] !== 'http://') {
                $link = stripslashes($_POST['idx_links_to']);

                if (0 === strpos($link, 'www.')) {
                    $link = 'http://' . $link;
                }
                // Starts with www., so add http://

                update_post_meta($post_ID, '_links_to', $link);

                if (isset($_POST['idx_links_to_new_window'])) {
                    update_post_meta($post_ID, '_links_to_target', '_blank');
                } else {
                    delete_post_meta($post_ID, '_links_to_target');
                }

                if (isset($_POST['idx_links_to_302'])) {
                    update_post_meta($post_ID, '_links_to_type', '302');
                } else {
                    delete_post_meta($post_ID, '_links_to_type');
                }

            } else {
                delete_post_meta($post_ID, '_links_to');
                delete_post_meta($post_ID, '_links_to_target');
                delete_post_meta($post_ID, '_links_to_type');
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
    public function idxplatinum_notice()
    {
        global $current_screen;
        echo '<div id="message" class="error"><p><strong>Note that your IDX Broker page links are not governed by WordPress Permalinks. To apply changes to your IDX Broker URLs, you must login to your IDX Broker Control Panel.</strong></p></div>';
    }

    /**
     * Function to generate permalink warning message
     *
     * @param void
     * @return void
     */

    public function permalink_update_warning()
    {
        if (isset($_POST['permalink_structure']) || isset($_POST['category_base'])) {
            add_action('admin_notices', array($this, 'idxplatinum_notice'));
        }
    }

}
