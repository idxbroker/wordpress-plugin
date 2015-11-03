<?php
namespace IDX\Shortcodes;

class Shortcode_Ui
{

    public function __construct()
    {
        add_action('media_buttons_context', array($this, 'add_idx_button'));
        add_action('wp_enqueue_media', array($this, 'enqueue_shorcode_js'));
        $this->shortcodes_for_ui = new Register_Shortcode_For_Ui();

    }

    public $shortcodes_for_ui;

    public function add_idx_button($context)
    {
        $icon = plugins_url('../assets/images/icon.png', dirname(__FILE__));
        $this->modal();

        return $context .= "<button id=\"idx-shortcode\" class=\"button thickbox\"><span><img src=\"$icon\"></span>Add IDX Shortcode</button>";
    }

    public function enqueue_shorcode_js()
    {
        wp_enqueue_script('idx-shortcode', plugins_url('../assets/js/idx-shortcode.js', dirname(__FILE__)), array('jquery'));
        wp_enqueue_style('idx-shortcode', plugins_url('../assets/css/idx-shortcode.css', dirname(__FILE__)));
    }

    public function modal()
    {
        echo "<div id=\"idx-shortcode-modal\" style=\"display:none;\"><div class=\"idx-modal-content\">";
        echo "<button type=\"button\" class=\"button-link media-modal-close\"><span class=\"media-modal-icon\"><span class=\"screen-reader-text\">Close media panel</span></span></button>";
        $this->modal_overview();
        echo "</div></div>";
        echo "<div id=\"idx-overlay\" style=\"display: none;\"></div>";

    }

    public function modal_overview()
    {
        echo "<h1>Insert IDX Shortcode</h1>";
        echo "<div class=\"separator\"></div>";
        echo "<div class=\"idx-modal-inner-content\">";
        echo "<div class=\"idx-modal-inner-overview\">";

        $shortcodes = $this->shortcodes_for_ui->get_shortcodes_for_ui();
        foreach ($shortcodes as $shortcode) {
            echo "<div class=\"idx-shortcode-type\" data-short-name=\"" . $shortcode['short_name'] . "\">";
            echo "<img src=\"" . $shortcode['icon'] . "\">";
            echo "<div class=\"idx-shortcode-name\">" . $shortcode['name'] . "</div>";
            echo "</div>";
        }
        echo "</div><div class=\"idx-modal-shortcode-edit\"></div>";
        echo "</div><div class=\"separator\"></div>";
        echo "<div class=\"idx-toolbar-primary\"><button class=\"button button-primary\">Insert Shortcode</button></div>";
    }

    public function get_shortcodes_for_ui()
    {
        $other_shortcodes = do_action('idx-register-shortcode-ui');
        if (empty($other_shortcodes)) {
            $other_shortcodes = array();
        }
        return array_merge($shortcodes_for_ui->default_shortcodes(), $other_shortcodes);

    }
}
