<?php
namespace IDX\Backward_Compatibility;

use \IDX\Idx_Api;

//Migrate Legacy Plugin Pages from version <1.3
class Migrate_Old_Table
{
    public function __construct()
    {
        $this->idx_api = new \IDX\Idx_Api();

        $post_info = $this->grab_post_ids();
        if (empty($post_info)) {
            return;
        }
        $this->migrate_old_pages($post_info);
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
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . "posts",
                array(
                    'post_name' => $link,
                    'post_type' => 'idx_page',
                ),
                array(
                    'ID' => $post_id,
                )
            );
        } else {
            $post = get_post($post_id);
            $link = $post->post_name;
            set_post_type($post_id, $post_type);
        }
    }

    public function migrate_old_pages($post_info)
    {
        $post_ids = $post_info['post_ids'];
        $links = $post_info['links'];
        $args = array(
            'post_type' => 'idx_page',
            'posts_per_page' => -1,
        );
        $custom_posts_array = get_posts($args);
        //delete duplicates first
        foreach ($links as $link) {
            $this->find_and_remove_duplicate_posts($link, $custom_posts_array);
        }
        //update existing idx pages to custom post type
        for ($i = 0; $i < count($post_ids); $i++) {
            $this->update_post_type($post_ids[$i], $links[$i], 'idx_page');
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
        return update_option('idx_migrated_old_table', true);
    }

    public function remove_duplicate_posts($page_id)
    {
        wp_delete_post($page_id, true);
        wp_trash_post($page_id);
    }

    public function find_and_remove_duplicate_posts($link, $custom_posts_array)
    {
        foreach ($custom_posts_array as $post) {
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
            $this->update_post_type($page_id, null, 'idx-wrapper');

            //update global wrapper
            $wrapper_page_url = get_permalink($page_id);
            $this->idx_api->set_wrapper('global', $wrapper_page_url);
        }
        flush_rewrite_rules();
    }
}
