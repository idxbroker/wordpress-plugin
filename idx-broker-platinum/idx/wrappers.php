<?php
namespace IDX;

class Wrappers
{
    public function __construct()
    {
        add_action('wp_ajax_create_dynamic_page', array($this, 'idx_ajax_create_dynamic_page'));
        add_action('wp_ajax_delete_dynamic_page', array($this, 'idx_ajax_delete_dynamic_page'));
    }

    public function idx_ajax_create_dynamic_page()
    {

        // default page content
        $post_content = '<div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div>';

        // get theme to check start/stop tag
        $does_theme_include_idx_tag = false;
        $template_root = get_theme_root() . '/' . get_stylesheet();

        $files = scandir($template_root);

        foreach ($files as $file) {
            $path = $template_root . '/' . $file;
            if (is_file($path) && preg_match('/.*\.php/', $file)) {
                $content = file_get_contents($template_root . '/' . $file);
                if (preg_match('/<div[^>\n]+?id=[\'"]idxstart[\'"].*?(\/>|><\/div>)/i', $content)) {
                    if (preg_match('/<div[^>\n]+?id=[\'"]idxstop[\'"].*?(\/>|><\/div>)/i', $content)) {
                        $does_theme_include_idx_tag = true;
                        break;
                    }
                }
            }
        }
        if ($does_theme_include_idx_tag) {
            $post_content = '';
        }

        $post_content .= '<style>.entry-title{display:none;}</style>';
        $post_title = $_POST['post_title'] ? $_POST['post_title'] : 'IDX Dynamic Wrapper Page';
        $new_post = array(
            'post_title' => $post_title,
            'post_name' => $post_title,
            'post_content' => $post_content,
            'post_type' => 'page',
            'post_status' => 'publish',
        );
        if ($_POST['wrapper_page_id']) {
            $new_post['ID'] = $_POST['wrapper_page_id'];
        }
        $wrapper_page_id = wp_insert_post($new_post);
        update_option('idx_broker_dynamic_wrapper_page_name', $post_title);
        update_option('idx_broker_dynamic_wrapper_page_id', $wrapper_page_id);
        Initiate_Plugin::update_tab();
        die(json_encode(array("wrapper_page_id" => $wrapper_page_id, "wrapper_page_name" => $post_title)));
    }

    public function idx_ajax_delete_dynamic_page()
    {
        if ($_POST['wrapper_page_id']) {
            wp_delete_post($_POST['wrapper_page_id'], true);
            wp_trash_post($_POST['wrapper_page_id']);
        }
        Initiate_Plugin::update_tab();
        die();
    }
}
