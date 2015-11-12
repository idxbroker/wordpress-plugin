<?php
namespace IDX\Shortcodes;

class Register_Shortcode_For_Ui
{

    public function __construct()
    {
        $this->idx_api = new \IDX\Idx_Api();
        add_action('wp_ajax_idx_shortcode_options', array($this, 'get_shortcode_options'));
        add_action('wp_ajax_idx_shortcode_preview', array($this, 'shortcode_preview'));
    }

    public $idx_api;

    public function default_shortcodes()
    {
        return array(
            'system_links' => array('name' => 'System Links', 'short_name' => 'system_links', 'icon' => 'fa fa-star'),
            'saved_links' => array('name' => 'Saved Links', 'short_name' => 'saved_links', 'icon' => 'fa fa-floppy-o'),
            'widgets' => array('name' => 'IDX Widgets', 'short_name' => 'widgets', 'icon' => 'fa fa-cog'),
            'omnibar' => array('name' => 'IDX Omnibar', 'short_name' => 'omnibar', 'icon' => 'fa fa-search'),
            'omnibar_extra' => array('name' => 'IDX Omnibar With Extra Fields', 'short_name' => 'omnibar_extra', 'icon' => 'fa fa-search-plus'),

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
        $system_links_check = $this->idx_api->idx_api_get_systemlinks();

        if (empty($system_links_check) || !empty($system_links_check->errors)) {
            echo '<p class="error" style="display:block;">' . $system_links_check->get_error_message() . '</p>';
            wp_die();
        }

        if ($shortcode_type === 'system_links') {
            echo $this->show_link_short_codes(0);
        } elseif ($shortcode_type === 'saved_links') {
            echo $this->show_link_short_codes(1);
        } elseif ($shortcode_type === 'widgets') {
            echo $this->get_widget_html();
        } elseif ($shortcode_type === 'omnibar') {
            echo $this->get_omnibar();
        } elseif ($shortcode_type === 'omnibar_extra') {
            echo $this->get_omnibar_extra();
        }
        //return html for the desired type for 3rd party plugins
        do_action('idx-get-shortcode-options');
        wp_die();
    }

    public function shortcode_preview()
    {
        //output shortcode for shortcode preview
        $shortcode = sanitize_text_field($_POST['idx_shortcode']);
        echo do_shortcode(stripslashes($shortcode));
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
            $short_code = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
            $idx_links = $this->idx_api->idx_api_get_systemlinks();
        } elseif ($link_type == 1) {
            $short_code = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
            $idx_links = $this->idx_api->idx_api_get_savedlinks();
        } else {
            return false;
        }

        if (count($idx_links) > 0 && is_array($idx_links)) {
            $available_shortcodes .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"" . $short_code . "\"><label for=\"saved-link\">Select a Link</label><select id=\"idx-select-subtype\" data-short-name=\"id\" style=\"width: 100%;\">";
            foreach ($idx_links as $idx_link) {
                if ($link_type === 0) {
                    $available_shortcodes .= $this->get_system_link_html($idx_link);
                }
                if ($link_type == 1) {
                    $available_shortcodes .= $this->get_saved_link_html($idx_link);
                }
            }
            $available_shortcodes .= "</select></div><div class=\"idx-modal-shortcode-field\"><label for=\"title\">Change the Title?</label><input type=\"text\" name=\"title\" id=\"title\" data-short-name=\"title\"></div>";
        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.<br>For instructions on creating Saved Links, see <a href="http://support.idxbroker.com/customer/portal/articles/1913083" target="_blank">this article</a> from our knowledgebase.</div>';
        }

        return $available_shortcodes;
    }

    public function get_system_link_html($idx_link)
    {
        $available_shortcodes = "";

        if ($idx_link->systemresults != 1) {
            $link_short_code = Register_Idx_Shortcodes::SHORTCODE_SYSTEM_LINK;
            $available_shortcodes .= "<option id=\"" . $link_short_code . "\" value=\"" . $idx_link->uid . "\">";
            $available_shortcodes .= $idx_link->name . "</option>";
        }
        return $available_shortcodes;
    }

    public function get_saved_link_html($idx_link)
    {
        $available_shortcodes = "";
        $link_short_code = Register_Idx_Shortcodes::SHORTCODE_SAVED_LINK;
        $available_shortcodes .= "<option id=\"" . $link_short_code . "\" value=\"" . $idx_link->uid . "\">";
        $available_shortcodes .= $idx_link->linkTitle . "</option>";
        return $available_shortcodes;
    }

    public function get_widget_html()
    {
        $idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
        $available_shortcodes = '';
        $widget_shortcode = Register_Idx_Shortcodes::SHORTCODE_WIDGET;

        if ($idx_widgets) {
            $available_shortcodes .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"" . $widget_shortcode . "\"><label for=\"widget\">Select a Widget</label><select id=\"idx-select-subtype\" data-short-name=\"id\" style=\"width: 100%;\">";
            foreach ($idx_widgets as $widget) {
                $available_shortcodes .= "<option id=\"" . $widget_shortcode . "\" value=\"" . $widget->uid . "\">" . $widget->name . "</option>";
            }
            $available_shortcodes .= "</select></div>";

        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
        }
        return $available_shortcodes;
    }

    public function get_omnibar()
    {
        $html = "<style>.idx-modal-tabs a:nth-of-type(1){display: none;}</style>";
        $html .= "<link type=\"text/css\" href=\"" . plugins_url('/assets/css/idx-omnibar.min.css', dirname(dirname(__FILE__))) . "\">";
        $html .= "<script>";
        $html .= "openPreviewTab(event, false); previewTabButton.removeEventListener('click', openPreviewTab); previewTab.innerHTML = '<img src=\"" . plugins_url('/assets/images/omnibar.png', dirname(dirname(__FILE__))) . "\">';";
        $html .= "</script>";
        $html .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"idx-omnibar\"></div>";
        return $html;
    }

    public function get_omnibar_extra()
    {
        $html = "<style>.idx-modal-tabs a:nth-of-type(1){display: none;}</style>";
        $html .= "<script>";
        $html .= "openPreviewTab(event, false); previewTabButton.removeEventListener('click', openPreviewTab); previewTab.innerHTML = '<img src=\"" . plugins_url('/assets/images/omnibar-extra.png', dirname(dirname(__FILE__))) . "\">';";
        $html .= "</script>";
        $html .= "<div class=\"idx-modal-shortcode-field\" data-shortcode=\"idx-omnibar-extra\"></div>";
        return $html;
    }

}
