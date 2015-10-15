<?php
namespace IDX;

class Idx_Pages
{

    public function __construct()
    {
        // var_dump($this->get_existing_idx_page_urls());
        // $this->delete_all_idx_pages();
        add_action('admin_init', array($this, 'create_idx_pages'), 10);

        add_filter('post_type_link', array($this, 'post_type_link_filter_func'), 10, 2);

        add_action('init', array($this, 'register_idx_page_type'));

        $this->idx_api = new Idx_Api();
    }

    public $idx_api;

    public function register_idx_page_type()
    {
        //register IDX Pages Post Type
        $args = array(
            'label' => 'IDX Pages',
            'labels' => array('singular_name' => 'IDX Page'),
            'public' => true,
            'show_ui' => false,
            'show_in_nav_menus' => true,
            'rewrite' => false,
        );

        register_post_type('idx_page', $args);
    }

    public function create_idx_pages()
    {

        $saved_links = $this->idx_api->idx_api_get_savedlinks();
        $system_links = $this->idx_api->idx_api_get_systemlinks();

        if (!is_array($system_links) || !is_array($saved_links)) {
            return;
        }

        $idx_links = array_merge($saved_links, $system_links);

        $existing_page_urls = $this->get_existing_idx_page_urls();

        foreach ($idx_links as $link) {
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

        $system_link_urls = $this->idx_api->all_system_link_urls();

        $system_link_names = $this->idx_api->all_system_link_names();

        if (empty($system_link_urls) || empty($system_link_names)) {
            return;
        }

        foreach ($posts as $post) {
            // post_name oddly refers to permalink in the db
            // if an idx hosted page url or title has been changed,
            // delete the page from the wpdb
            // the updated page will be repopulated automatically
            if (!in_array($post->post_name, $system_link_urls) || !in_array($post->post_title, $system_link_names)) {
                wp_delete_post($post->ID);
            }
        }
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
     *
     * @return void
     */
    public function delete_all_idx_pages()
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
}
