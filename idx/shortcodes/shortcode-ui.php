<?php
namespace IDX\Shortcodes;

class Shortcode_Ui
{

    public function __construct(Register_Shortcode_For_Ui $shortcodes_for_ui)
    {
        add_action('media_buttons_context', array($this, 'add_idx_button'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_shortcode_js'));
        $this->shortcodes_for_ui = $shortcodes_for_ui;
    }

    public $shortcodes_for_ui;

    public function add_idx_button($context)
    {
        $this->modal();
        return $context .= "<button id=\"idx-shortcode\" class=\"button thickbox\">Add IDX Shortcode</button>";
    }

    public function enqueue_shortcode_js($hook)
    {
        if ('post.php' != $hook && 'post-new.php' != $hook) {
            return;
        }

        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', 'jquery');
        wp_enqueue_script('idx-shortcode', plugins_url('../assets/js/idx-shortcode.min.js', dirname(__FILE__)), array('jquery'));
        wp_enqueue_style('idx-shortcode', plugins_url('../assets/css/idx-shortcode.css', dirname(__FILE__)));
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
        //javascript for map search widget preview
        wp_enqueue_script('custom-scriptLeaf', '//idxdyncdn.idxbroker.com/graphical/javascript/leaflet.js', __FILE__);
        wp_enqueue_script('custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__);
        wp_enqueue_style('cssLeaf', '//idxdyncdn.idxbroker.com/graphical/css/leaflet.css');
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
        echo "<div class=\"idx-back-button\"><a href=\"#\">← Back to Overview</a></div>";
        echo "<div class=\"idx-modal-inner-content\"><div class=\"idx-modal-tabs-router\"><div class=\"idx-modal-tabs\"><a class=\"idx-active-tab\" href=\"#\">Edit</a><a href=\"#\">Preview</a></div></div>";
        echo "<div class=\"idx-modal-inner-overview\">";

        $shortcodes = $this->shortcodes_for_ui->get_shortcodes_for_ui();
        foreach ($shortcodes as $shortcode) {
            echo "<div class=\"idx-shortcode-type\" data-short-name=\"" . $shortcode['short_name'] . "\">";
            echo "<div class=\"idx-shortcode-type-icon\"><i class=\"" . $shortcode['icon'] . "\"></i></div>";
            echo "<div class=\"idx-shortcode-name\">" . $shortcode['name'] . "</div>";
            echo "</div>";
        }
        echo "</div><div class=\"idx-modal-shortcode-edit\"></div><div class=\"idx-modal-shortcode-preview\"></div>";
        echo "</div>";
        echo "<div class=\"idx-toolbar-primary\"><div class=\"separator\"></div><button class=\"button button-primary\">Insert Shortcode</button></div>";
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
