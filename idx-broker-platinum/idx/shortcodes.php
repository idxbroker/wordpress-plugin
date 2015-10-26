<?php
namespace IDX;

class Shortcodes
{
    public function __construct()
    {
        $this->idx_api = new Idx_Api();
        add_action('init', array($this, 'idx_buttons'));
        //Adding shortcodes
        add_shortcode('idx-platinum-link', array($this, 'show_link'));
        add_shortcode('idx-platinum-saved-link', array($this, 'show_saved_link'));
        add_shortcode('idx-platinum-system-link', array($this, 'show_system_link'));
        add_shortcode('idx-platinum-widget', array($this, 'show_widget'));
    }

    public $idx_api;
    /**
     * registers the buttons for use
     * @param array $buttons
     */
    public function register_idx_buttons($buttons)
    {
        // inserts a separator between existing buttons and our new one
        array_push($buttons, "|", "idx_button");
        return $buttons;
    }

    /**
     * add the button to the tinyMCE bar
     * @param array $plugin_array
     */
    public function add_idx_tinymce_plugin($plugin_array)
    {
        $plugin_array['idx_button'] = plugins_url('../assets/js/idx-buttons.js', __FILE__);
        return $plugin_array;
    }

    /**
     * filters the tinyMCE buttons and adds our custom buttons
     */
    public function idx_buttons()
    {
        // Don't bother doing this stuff if the current user lacks permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // Add only in Rich Editor mode
        if (get_user_option('rich_editing') == 'true') {
            // filter the tinyMCE buttons and add our own
            add_filter("mce_external_plugins", array($this, "add_idx_tinymce_plugin"));
            add_filter('mce_buttons', array($this, 'register_idx_buttons'));
        } // end if rich editing true
    }

    /**
     * Function to show a idx link with shortcode of type:
     * [idx-platinum-link title="widget title here"]
     *
     * @param array $atts
     * @return html code for showing the widget/ bool false
     */
    public function show_widget($atts)
    {
        extract(shortcode_atts(array(
            'id' => null,
        ), $atts));

        if (!is_null($id)) {
            return Widgets\Create_Widgets::get_widget_by_uid($id);
        } else {
            return false;
        }
    }

    /**
     * FUnction to show a idx system link with shortcode of type:
     * [idx-platinum-system-link title="title here"]
     *
     * @param array $atts
     * @return string|boolean
     */
    public function show_system_link($atts)
    {
        extract(shortcode_atts(array(
            'id' => null,
            'title' => null,
        ), $atts));

        if (!is_null($id)) {
            $link = $this->idx_get_link_by_uid($id, 0);
            if (is_object($link)) {
                if (!is_null($title)) {
                    $link->name = $title;
                }
                return '<a href="' . $link->url . '">' . $link->name . '</a>';
            }
        } else {
            return false;
        }
    }

    /**
     * Function to show a idx link with shortcode of type:
     * [idx-platinum-link title="title here"]
     *
     * @param array $atts
     * @return html code for showing the link/ bool false
     */
    public function show_link($atts)
    {
        extract(shortcode_atts(array(
            'title' => null,
        ), $atts));

        if (!is_null($title)) {
            $page = get_page_by_title($title);
            $permalink = get_permalink($page->ID);
            return '<a href="' . get_permalink($page->ID) . '">' . $page->post_title . '</a>';
        } else {
            return false;
        }
    }

/**
 * FUnction to show a idx ssaved link with shortcode of type:
 * [idx-platinum-saved-link title="title here"]
 *
 * @param array $atts
 * @return string|boolean
 */
    public function show_saved_link($atts)
    {
        extract(shortcode_atts(array(
            'id' => null,
            'title' => null,
        ), $atts));

        if (!is_null($id)) {
            $link = $this->idx_get_link_by_uid($id, 1);
            if (is_object($link)) {
                if (!is_null($title)) {
                    $link->name = $title;
                }
                return '<a href="' . $link->url . '">' . $link->name . '</a>';
            }
        } else {
            return false;
        }
    }

    public function idx_get_link_by_uid($uid, $type = 0)
    {
        if ($type == 0) {
            // if the cache has expired, send an API request to update them. Cache expires after 2 hours.
            if (!$this->idx_api->get_transient('idx_systemlinks_cache')) {
                $this->idx_api->idx_api_get_systemlinks();
            }

            $idx_links = $this->idx_api->get_transient('idx_systemlinks_cache');
        } elseif ($type == 1) {
            if (!get_transient('idx_savedlinks_cache')) {
                $this->idx_api->idx_api_get_savedlinks();
            }

            $idx_links = $this->idx_api->get_transient('idx_savedlinks_cache');
        }

        $selected_link = '';

        if ($idx_links) {
            foreach ($idx_links as $link) {
                if (strcmp($link->uid, $uid) == 0) {
                    $selected_link = $link;
                }
            }
        }
        return $selected_link;
    }

/**
 * Function to print the system/saved link shortcodes.
 *
 * @param int $link_type 0 for system link and 1 for saved link
 */
    public function show_link_short_codes($link_type = 0)
    {
        $available_shortcodes = '';

        if ($link_type === 0) {
            $short_code = Initiate_Plugin::SHORTCODE_SYSTEM_LINK;
            $idx_links = $this->idx_api->idx_api_get_systemlinks();
        } elseif ($link_type == 1) {
            $short_code = Initiate_Plugin::SHORTCODE_SAVED_LINK;
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
        echo $available_shortcodes;
    }

/**
 * Function to return the HTM for displaying each system link
 * @param object $idx_link
 * @return string
 */
    public static function get_system_link_html($idx_link)
    {
        $available_shortcodes = "";

        if ($idx_link->systemresults != 1) {
            $link_short_code = '[' . Initiate_Plugin::SHORTCODE_SYSTEM_LINK . ' id ="' . $idx_link->uid . '" title ="' . $idx_link->name . '"]';
            $available_shortcodes .= '<div class="each_shortcode_row">';
            $available_shortcodes .= '<input type="hidden" id=\'' . $idx_link->uid . '\' value=\'' . $link_short_code . '\'>';
            $available_shortcodes .= '<span>' . $idx_link->name . '&nbsp;<a name="' . $idx_link->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $idx_link->uid . '\')" class="shortcode_link">insert</a>
        &nbsp;<a href="?uid=' . urlencode($idx_link->uid) . '&current_title=' . urlencode($idx_link->name) . '&short_code=' . urlencode($link_short_code) . '">change title</a>
        </span>';
            $available_shortcodes .= '</div>';
        }
        return $available_shortcodes;
    }

/**
 * Function to return the HTM for displaying each saved link
 * @param object $idx_link
 * @return string
 */
    public static function get_saved_link_html($idx_link)
    {
        $available_shortcodes = "";
        $link_short_code = '[' . Initiate_Plugin::SHORTCODE_SAVED_LINK . ' id ="' . $idx_link->uid . '" title ="' . $idx_link->linkTitle . '"]';
        $available_shortcodes .= '<div class="each_shortcode_row">';
        $available_shortcodes .= '<input type="hidden" id=\'' . $idx_link->uid . '\' value=\'' . $link_short_code . '\'>';
        $available_shortcodes .= '<span>' . $idx_link->linkTitle . '&nbsp;<a name="' . $idx_link->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $idx_link->uid . '\')" class="shortcode_link">insert</a>
    &nbsp;<a href="?uid=' . urlencode($idx_link->uid) . '&current_title=' . urlencode($idx_link->linkTitle) . '&short_code=' . urlencode($link_short_code) . '">change title</a>
    </span>';

        $available_shortcodes .= '</div>';

        return $available_shortcodes;
    }

/**
 * Function to print the shortcodes of all the widgets
 */
    public static function show_widget_shortcodes()
    {
        $idx_api = new Idx_Api();
        $idx_widgets = $idx_api->get_transient('idx_widgetsrc_cache');
        $available_shortcodes = '';

        if ($idx_widgets) {
            foreach ($idx_widgets as $widget) {
                $widget_shortcode = '[' . Initiate_Plugin::SHORTCODE_WIDGET . ' id ="' . $widget->uid . '"]';
                $available_shortcodes .= '<div class="each_shortcode_row">';
                $available_shortcodes .= '<input type="hidden" id=\'' . $widget->uid . '\' value=\'' . $widget_shortcode . '\'>';
                $available_shortcodes .= '<span>' . $widget->name . '&nbsp;<a name="' . $widget->uid . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $widget->uid . '\')">insert</a></span>';
                $available_shortcodes .= '</div>';
            }
        } else {
            $available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';
        }
        echo $available_shortcodes;
    }

}
