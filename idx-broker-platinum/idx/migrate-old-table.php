<?php
namespace IDX;

//Migrate Legacy Plugin Pages to New Custom Post Type
class Migrate_Old_Table
{
    public function __construct()
    {
        $post_info = $this->grab_post_ids();
        if (!empty($post_info)) {
            $this->migrate_old_pages($post_info);
        }
        $this->drop_old_table();
        $this->migrate_old_wrapper();
    }

    public function grab_post_ids()
    {
        global $wpdb;
        $post_ids = $wpdb->get_col("SELECT post_id FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'");
        $links = $wpdb->get_col("SELECT meta_value FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'");
        return array(
            'post_ids' => $post_ids,
            'links' => $links,
        );
    }

    public function update_post_type($post_id, $link, $post_type)
    {
        if ($post_type === 'idx_page') {
            $this->find_and_remove_duplicate_posts($link);
        } else {
            $post = get_post($post_id);
            $link = $post->post_name;
        }
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . "posts",
            array(
                'post_type' => $post_type,
                'post_name' => $link,
            ),
            array(
                'ID' => $post_id,
            )
        );

    }

    public function migrate_old_pages($post_info)
    {
        $post_ids = $post_info['post_ids'];
        $links = $post_info['links'];
        for ($i = 0; $i < count($post_ids); $i++) {
            $this->update_post_type($post_ids[$i], $links[$i], 'idx_page');
            print_r($post_ids[$i]);
        }
    }

    public function drop_old_table()
    {
        global $wpdb;
        $posts_idx = $wpdb->prefix . "posts_idx";
        $postmeta = $wpdb->prefix . "postmeta";
        $wpdb->query("DROP TABLE IF EXISTS $posts_idx");
        $wpdb->delete($postmeta,
            array(
                'meta_key' => '_links_to',
            )
        );
    }

    public function remove_duplicate_posts($page_id)
    {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }

    public function find_and_remove_duplicate_posts($link)
    {
        $args = array(
            'post_type' => 'idx_page',
            'posts_per_page' => -1,
        );
        $posts_array = get_posts($args);
        foreach ($posts_array as $post) {
            if ($post->post_name === $link) {
                $page_id = $post->ID;
                $this->remove_duplicate_posts($page_id);
            }
        }
    }

    public function migrate_old_wrapper()
    {
        $page_id = get_option('idx_broker_dynamic_wrapper_page_id');
        if (!empty($page_id)) {
            //update post type to wrappers
            $this->update_post_type($page_id, null, 'wrappers');

            //update global wrapper
            $wrapper_page_url = get_permalink($page_id);
            $idx_api = new Idx_Api();
            $idx_api->idx_api("dynamicwrapperurl", $idx_api->idx_api_get_apiversion(), 'clients', array('body' => array('dynamicURL' => $wrapper_page_url)), 10, 'post');
        }
    }
}
