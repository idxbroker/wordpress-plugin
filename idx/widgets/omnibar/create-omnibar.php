<?php
namespace IDX\Widgets\Omnibar;

class Create_Omnibar
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->register_shortcodes();
        $this->register_widgets();
    }

    public $app;

    public function idx_omnibar_basic($plugin_dir, $idx_url, $styles = 1)
    {
        $mlsPtIDs = $this->idx_omnibar_default_property_types();
        $placeholder = get_option('idx_omnibar_placeholder');
        if (empty($placeholder)) {
            $placeholder = 'City, Postal Code, Address, or Listing ID';
        }
        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
        if (!empty($styles)) {
            wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/widgets/idx-omnibar.min.css', dirname(__FILE__)));
        }
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', dirname(__FILE__)));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_localize_script('idx-omnibar-js', 'mlsPtIDs', $mlsPtIDs);
        wp_localize_script('idx-omnibar-js', 'idxOmnibarPlaceholder', $placeholder);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', plugins_url('../../assets/js/locationlist.js', dirname(__FILE__)));

        return <<<EOD
        <form class="idx-omnibar-form idx-omnibar-original-form">
          <input class="idx-omnibar-input" type="text" placeholder="$placeholder"><button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
          <div class="idx-omnibar-extra idx-omnibar-price-container" style="display: none;"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container" style="display: none;"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container" style="display: none;"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.50"></div>
        </form>
EOD;
    }

    public function idx_omnibar_extra($plugin_dir, $idx_url, $styles = 1)
    {
        $mlsPtIDs = $this->idx_omnibar_default_property_types();
        $placeholder = get_option('idx_omnibar_placeholder');
        if (empty($placeholder)) {
            $placeholder = 'City, Postal Code, Address, or Listing ID';
        }
        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
        if (!empty($styles)) {
            wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/widgets/idx-omnibar.min.css', dirname(__FILE__)));
        }
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', dirname(__FILE__)));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_localize_script('idx-omnibar-js', 'mlsPtIDs', $mlsPtIDs);
        wp_localize_script('idx-omnibar-js', 'idxOmnibarPlaceholder', $placeholder);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', plugins_url('../../assets/js/locationlist.js', dirname(__FILE__)));

        return <<<EOD
    <form class="idx-omnibar-form idx-omnibar-extra-form">
      <input class="idx-omnibar-input idx-omnibar-extra-input" type="text" placeholder="$placeholder">
      <div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0" title="No commas or dollar signs are allowed."></div><div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.50" title="Only numbers and decimals are allowed"></div>
      <button class="idx-omnibar-extra-button" type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
    </form>
EOD;
    }

    public function idx_omnibar_default_property_types()
    {
        $mlsPtIDs = get_option('idx_default_property_types');
        //if no default pts have been set, add dummy values to prevent js errors
        if (empty($mlsPtIDs)) {
            $mlsPtIDs = array(
                array(
                    'idxID' => '',
                    'mlsPtID' => 1,
                ),
            );
        }
        return $mlsPtIDs;
    }

    public function add_omnibar_shortcode($atts)
    {
        extract(shortcode_atts(array(
            'styles' => 1,
            'extra' => 0,
        ), $atts));

        $idx_url = get_option('idx_results_url');
        $plugin_dir = plugins_url();

        if (!empty($extra)) {
            return $this->idx_omnibar_extra($plugin_dir, $idx_url, $styles);
        } else {
            return $this->idx_omnibar_basic($plugin_dir, $idx_url, $styles);
        }
    }

    public function add_omnibar_extra_shortcode($atts)
    {
        extract(shortcode_atts(array(
            'styles' => 1,
        ), $atts));

        $idx_url = get_option('idx_results_url');
        $plugin_dir = plugins_url();

        return $this->idx_omnibar_extra($plugin_dir, $idx_url, $styles);
    }

    //use our own register function to allow dependency injection via the IoC container
    public function register_widget($widget_name)
    {
        global $wp_widget_factory;

        $widget_class = $this->app->make($widget_name);

        $wp_widget_factory->widgets[$widget_name] = $widget_class;
    }


    public static function show_omnibar_shortcodes($type, $name)
    {
        $widget_shortcode = '[' . $type . ']';
        $available_shortcodes = '<div class="each_shortcode_row">';
        $available_shortcodes .= '<input type="hidden" id=\'' . $type . '\' value=\'' . $widget_shortcode . '\'>';
        $available_shortcodes .= '<span>' . $name . ' &nbsp;<a name="' . $type . '" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\'' . $type . '\')">insert</a></span>';
        $available_shortcodes .= '</div>';

        echo $available_shortcodes;
    }

    public function register_shortcodes()
    {
        add_shortcode('idx-omnibar', array($this, 'add_omnibar_shortcode'));
        add_shortcode('idx-omnibar-extra', array($this, 'add_omnibar_extra_shortcode'));
    }

    public function register_widgets()
    {
        //for PHP5.3 compatibility
        $scope = $this;
        //Initialize Instances of Widget Classes
        add_action('widgets_init', function () use ($scope) {$scope->register_widget('\IDX\Widgets\Omnibar\IDX_Omnibar_Widget');});
        add_action('widgets_init', function () use ($scope) {$scope->register_widget('\IDX\Widgets\Omnibar\IDX_Omnibar_Widget_Extra');});


    }

}
