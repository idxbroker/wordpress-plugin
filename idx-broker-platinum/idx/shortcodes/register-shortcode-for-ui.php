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
            'system_links' => array('name' => 'System Links', 'short_name' => 'system_links', 'icon' => "fa fa-star"),
            'saved_links' => array('name' => 'Saved Links', 'short_name' => 'saved_links', 'icon' => "fa fa-floppy-o"),
            'widgets' => array('name' => 'IDX Widgets', 'short_name' => 'widgets', 'icon' => "fa fa-cog"),
            // for version 2.0
            // 'impress_lead_login_widget' => array('name' => 'Impress Lead Login Widget', 'short_name' => 'impress_lead_login_widget', 'icon' => 'fa fa-users'),
            // 'impress_lead_login_widget' => array('name' => 'IMPress Lead Signup Widget', 'short_name' => 'impress_lead_login_widget', 'icon' => 'fa fa-user-plus'),
            // 'impress_city_links' => array('name' => 'IMPress City Links', 'short_name' => 'impress_city_links', 'icon' => 'fa fa-link'),
            // 'impress_property_showcase' => array('name' => 'IMPress Property Showcase', 'short_name' => 'impress_property_showcase', 'icon' => 'fa fa-home'),
            // 'impress_property_carousel' => array('name' => 'IMPress Property Carousel', 'short_name' => 'impress_property_carousel', 'icon' => 'dashicons dashicons-admin-multisite'),
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

        if ($link_type === 0) {
            $short_code = Register_Shortcodes::SHORTCODE_SYSTEM_LINK;
            $idx_links = $this->idx_api->idx_api_get_systemlinks();
            $available_shortcodes .= "<div class=\"idx-modal-shortcode-field\"><label for=\"system-link\">Select a System Link</label><select id=\"idx-select-subtype\" style=\"width: 100%;\">";
        } elseif ($link_type == 1) {
            $short_code = Register_Shortcodes::SHORTCODE_SAVED_LINK;
            $idx_links = $this->idx_api->idx_api_get_savedlinks();
            $available_shortcodes .= "<div class=\"idx-modal-shortcode-field\"><label for=\"saved-link\">Select a Saved Link</label><select id=\"idx-select-subtype\" style=\"width: 100%;\">";
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
        $available_shortcodes .= "</select></div><div class=\"idx-modal-shortcode-field\"><label for=\"title\">Change the Title?</label><input type=\"text\" name=\"title\" id=\"title\"></div>";

        return $available_shortcodes;
    }

    public function get_system_link_html($idx_link)
    {
        $available_shortcodes = "";

        if ($idx_link->systemresults != 1) {
            $link_short_code = Register_Shortcodes::SHORTCODE_SYSTEM_LINK;
            $available_shortcodes .= "<option id=\"" . $idx_link->uid . "\" value=\"" . $link_short_code . "\">";
            $available_shortcodes .= $idx_link->name . "</option>";
        }
        return $available_shortcodes;
    }

    public function get_saved_link_html($idx_link)
    {
        $available_shortcodes = "";
        $link_short_code = Register_Shortcodes::SHORTCODE_SAVED_LINK;
        $available_shortcodes .= "<option id=\"" . $idx_link->uid . "\" value=\"" . $link_short_code . "\">";
        $available_shortcodes .= $idx_link->linkTitle . "</option>";
        return $available_shortcodes;
    }

    public function get_widget_html()
    {
        $idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
        $available_shortcodes = '';

        if ($idx_widgets) {
            $available_shortcodes .= "<div class=\"idx-modal-shortcode-field\"><label for=\"widget\">Select a Widget</label><select id=\"idx-select-subtype\" style=\"width: 100%;\">";
            foreach ($idx_widgets as $widget) {
                $widget_shortcode = Register_Shortcodes::SHORTCODE_WIDGET;
                $available_shortcodes .= "<option id=\"" . $widget->uid . "\" value=\"" . $widget_shortcode . "\">" . $widget->name . "</option>";
            }
            $available_shortcodes .= "</select></div>";

        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
        }
        return $available_shortcodes;
    }
}
