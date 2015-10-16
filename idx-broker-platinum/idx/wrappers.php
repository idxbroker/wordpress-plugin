<?php
namespace IDX;

class Wrappers
{
    public function __construct()
    {
        add_action('wp_ajax_create_dynamic_page', array($this, 'idx_ajax_create_dynamic_page'));
        add_action('wp_ajax_delete_dynamic_page', array($this, 'idx_ajax_delete_dynamic_page'));
        add_action('init', array($this, 'register_wrapper_post_type'));
        add_filter('default_content', array($this, 'idx_wrapper_content'), 10, 2);
    }

    public function register_wrapper_post_type()
    {
        $args = array(
            'public' => true,
            'labels' => array('singular_name' => 'Wrapper'),
            'label' => 'Wrappers',
            'description' => 'Custom Posts Created To Match IDX Pages to the Website',
            'exclude_from_search' => true,
            'show_in_menu' => 'idx-broker',
            'show_in_nav_menus' => false,
            'capability_type' => 'post',
            'has_archive' => false,
        );
        register_post_type('idx-wrapper', $args);
    }

//check if theme includes idxstart and stop tags
    public function does_theme_include_idx_tag()
    {
        // default page content
        $post_content = '<div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div><style>.entry-title, .entry-meta, .adjacent-entry-pagination, .post-navigation {display: none;}</style>';
        // get theme to check start/stop tag
        $does_theme_include_idx_tag = false;
        $template_root = get_theme_root() . DIRECTORY_SEPARATOR . get_stylesheet();
        $files = scandir($template_root);
        foreach ($files as $file) {
            $path = $template_root . DIRECTORY_SEPARATOR . $file;
            if (is_file($path) && preg_match('/.*\.php/', $file)) {
                $content = file_get_contents($template_root . DIRECTORY_SEPARATOR . $file);
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

        return $post_content;
    }

    public function idx_wrapper_content($content, $post)
    {
        if ($post->post_type === 'idx-wrapper') {
            $content = $this->does_theme_include_idx_tag();
            return $content;
        }
    }

    public function idx_ajax_create_dynamic_page()
    {

        // default page content
        $post_content = $this->does_theme_include_idx_tag();

        $post_title = $_POST['post_title'] ? $_POST['post_title'] : 'Properties';
        $new_post = array(
            'post_title' => $post_title,
            'post_name' => $post_title,
            'post_content' => $post_content,
            'post_type' => 'idx-wrapper',
            'post_status' => 'publish',
        );
        if ($_POST['wrapper_page_id']) {
            $new_post['ID'] = $_POST['wrapper_page_id'];
        }
        $wrapper_page_id = wp_insert_post($new_post);
        update_option('idx_broker_dynamic_wrapper_page_name', $post_title);
        update_option('idx_broker_dynamic_wrapper_page_id', $wrapper_page_id);
        $wrapper_page_url = get_permalink($wrapper_page_id);
        $idx_api = new Idx_Api();
        $idx_api->idx_api("dynamicwrapperurl", $idx_api->idx_api_get_apiversion(), 'clients', array('body' => array('dynamicURL' => $wrapper_page_url)), 10, 'post');

        die(json_encode(array("wrapper_page_id" => $wrapper_page_id, "wrapper_page_name" => $post_title)));
    }

    public function idx_ajax_delete_dynamic_page()
    {
        if ($_POST['wrapper_page_id']) {
            wp_delete_post($_POST['wrapper_page_id'], true);
            wp_trash_post($_POST['wrapper_page_id']);
        }
        die();
    }

}
