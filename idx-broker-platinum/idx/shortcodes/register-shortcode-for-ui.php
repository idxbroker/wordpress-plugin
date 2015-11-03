<?php
namespace IDX\Shortcodes;

class Register_Shortcode_For_Ui
{

    public function __construct()
    {
        $this->idx_api = new \IDX\Idx_Api();
        add_action('wp_ajax_idx_shortcode_options', array($this, 'get_shortcode_options'));
    }

    public $idx_api;

    public function default_shortcodes()
    {
        return array(
            'system_links' => array('name' => 'System Links', 'short_name' => 'system_links', 'icon' => null),
            'saved_links' => array('name' => 'Saved Links', 'short_name' => 'saved_links', 'icon' => null),
            'widgets' => array('name' => 'IDX Widgets', 'short_name' => 'widgets', 'icon' => null),
            // for version 2.0
            // 'IMPress Lead Login Widget' =>  array('title' => ''),
            // 'IMPress Lead Signup Widget' =>  array('title' => ''),
            // 'IMPress City Links' =>  array('title' => ''),
            // 'IMPress Property Showcase' =>  array('title' => ''),
            // 'IMPress Property Carousel' => array('title' => ''),
        );

    }

    public function get_shortcode_options()
    {
        $shortcode_type = sanitize_text_field($_POST['idx_shortcode_type']);
        if ($shortcode_type === 'system_links') {
            echo $this->show_link_short_codes(0);
        } elseif ($shortcode_type === 'saved_links') {
            echo $this->show_link_short_codes(1);
        } elseif ($shortcode_type === 'widgets') {
            echo $this->get_widget_html();
        }
        //return html for the desired type for 3rd party plugins
        do_action('idx-get-shortcode-options');
        wp_die();
    }

    public function get_shortcodes_for_ui()
    {
        //add any other types from 3rd party plugins to this interface
        //mimic the default_shortcodes array to make it work.
        $other_shortcodes = do_action('idx-register-shortcode-ui');
        if (empty($other_shortcodes)) {
            $other_shortcodes = array();
        }
        return array_merge($this->default_shortcodes(), $other_shortcodes);

    }

    public function show_link_short_codes($link_type = 0)
    {
        $available_shortcodes = '';
        $available_shortcodes .= "<label for=\"title\">Title</label><input type=\"text\" name=\"title\" id=\"title\">";

        if ($link_type === 0) {
            $short_code = Register_Shortcodes::SHORTCODE_SYSTEM_LINK;
            $idx_links = $this->idx_api->idx_api_get_systemlinks();
        } elseif ($link_type == 1) {
            $short_code = Register_Shortcodes::SHORTCODE_SAVED_LINK;
            $idx_links = $this->idx_api->idx_api_get_savedlinks();
        } else {
            return false;
        }

        if (count($idx_links) > 0 && is_array($idx_links)) {
            foreach ($idx_links as $idx_link) {
                if ($link_type === 0) {
                    $available_shortcodes .= $this->get_system_link_html($idx_link);
                }
                if ($link_type == 1) {
                    $available_shortcodes .= $this->get_saved_link_html($idx_link);
                }
            }
        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
        }
        return $available_shortcodes;
    }

    public function get_system_link_html($idx_link)
    {
        $available_shortcodes = "";

        if ($idx_link->systemresults != 1) {
            $link_short_code = '[' . Register_Shortcodes::SHORTCODE_SYSTEM_LINK . ' id ="' . $idx_link->uid . '" title ="' . $idx_link->name . '"]';
            $available_shortcodes .= '<div class="each_shortcode_row">';
            $available_shortcodes .= '<input type="hidden" id=\'' . $idx_link->uid . '\' value=\'' . $link_short_code . '\'>';
            $available_shortcodes .= '<span>' . $idx_link->name . '&nbsp;<a name="' . $idx_link->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $idx_link->uid . '\')" class="shortcode_link">insert</a>
        &nbsp;<a href="?uid=' . urlencode($idx_link->uid) . '&current_title=' . urlencode($idx_link->name) . '&short_code=' . urlencode($link_short_code) . '">change title</a>
        </span>';
            $available_shortcodes .= '</div>';
        }
        return $available_shortcodes;
    }

    public function get_saved_link_html($idx_link)
    {
        $available_shortcodes = "";
        $link_short_code = '[' . Register_Shortcodes::SHORTCODE_SAVED_LINK . ' id ="' . $idx_link->uid . '" title ="' . $idx_link->linkTitle . '"]';
        $available_shortcodes .= '<div class="each_shortcode_row">';
        $available_shortcodes .= '<input type="hidden" id=\'' . $idx_link->uid . '\' value=\'' . $link_short_code . '\'>';
        $available_shortcodes .= '<span>' . $idx_link->linkTitle . '&nbsp;<a name="' . $idx_link->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $idx_link->uid . '\')" class="shortcode_link">insert</a>
    &nbsp;<a href="?uid=' . urlencode($idx_link->uid) . '&current_title=' . urlencode($idx_link->linkTitle) . '&short_code=' . urlencode($link_short_code) . '">change title</a>
    </span>';

        $available_shortcodes .= '</div>';

        return $available_shortcodes;
    }

    public function get_widget_html()
    {
        $idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
        $available_shortcodes = '';

        if ($idx_widgets) {
            $available_shortcodes .= "<label for=\"title\">Title</label><input type=\"text\" name=\"title\" id=\"title\">";
            foreach ($idx_widgets as $widget) {
                $widget_shortcode = '[' . Register_Shortcodes::SHORTCODE_WIDGET . ' id ="' . $widget->uid . '"]';
                $available_shortcodes .= '<div class="each_shortcode_row">';
                $available_shortcodes .= '<input type="hidden" id=\'' . $widget->uid . '\' value=\'' . $widget_shortcode . '\'>';
                $available_shortcodes .= '<span>' . $widget->name . '&nbsp;<a name="' . $widget->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $widget->uid . '\')">insert</a></span>';
                $available_shortcodes .= '</div>';
            }
        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
        }
        return $available_shortcodes;
    }
}
