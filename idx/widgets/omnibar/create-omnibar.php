<?php
namespace IDX\Widgets\Omnibar;

class Create_Omnibar
{
    public function __construct()
    {
        $this->register_shortcodes();
        $this->register_widgets();

    }

    public function idx_omnibar_basic($plugin_dir, $idx_url, $styles = 1)
    {
        $mlsPtIDs = $this->idx_omnibar_default_property_types();
        $placeholder = get_option('idx_omnibar_placeholder');
        if (empty($placeholder)) {
            $placeholder = 'City, Postal Code, Address, or Listing ID';
        }
        $sort_order = get_option('idx_omnibar_sort', 'newest');

        $upload_dir = wp_upload_dir();
        $idx_dir_url = $upload_dir['baseurl'] . '/idx_cache';

        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
        if (!empty($styles)) {
            wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/widgets/idx-omnibar.min.css', dirname(__FILE__)));
        }
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', dirname(__FILE__)));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_localize_script('idx-omnibar-js', 'sortOrder', $sort_order);
        wp_localize_script('idx-omnibar-js', 'mlsPtIDs', $mlsPtIDs);
        wp_localize_script('idx-omnibar-js', 'idxOmnibarPlaceholder', $placeholder);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', $idx_dir_url . '/locationlist.js');

        return <<<EOD
        <form class="idx-omnibar-form idx-omnibar-original-form">
          <label for="omnibar" class="screen-reader-text">$placeholder</label>
          <input id="omnibar" class="idx-omnibar-input" type="text" placeholder="$placeholder"><button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
          <div class="idx-omnibar-extra idx-omnibar-price-container" style="display: none;"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container" style="display: none;"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container" style="display: none;"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01"></div>
        </form>
EOD;
    }

    public function idx_omnibar_extra($plugin_dir, $idx_url, $styles = 1, $min_price = 0)
    {
        $mlsPtIDs = $this->idx_omnibar_default_property_types();
        $placeholder = get_option('idx_omnibar_placeholder');
        if (empty($placeholder)) {
            $placeholder = 'City, Postal Code, Address, or Listing ID';
        }
        $sort_order = get_option('idx_omnibar_sort', 'newest');

        $upload_dir = wp_upload_dir();
        $idx_dir_url = $upload_dir['baseurl'] . '/idx_cache';
        
        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');
        if (!empty($styles)) {
            wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/widgets/idx-omnibar.min.css', dirname(__FILE__)));
        }
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', dirname(__FILE__)));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_localize_script('idx-omnibar-js', 'sortOrder', $sort_order);
        wp_localize_script('idx-omnibar-js', 'mlsPtIDs', $mlsPtIDs);
        wp_localize_script('idx-omnibar-js', 'idxOmnibarPlaceholder', $placeholder);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', $idx_dir_url . '/locationlist.js');

        $price_field = $this->price_field($min_price);

        return <<<EOD
    <form class="idx-omnibar-form idx-omnibar-extra-form">
      <label for="omnibar" class="screen-reader-text">$placeholder</label>
      <input id="omnibar" class="idx-omnibar-input idx-omnibar-extra-input" type="text" placeholder="$placeholder">
      $price_field<div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01" title="Only numbers and decimals are allowed"></div>
      <button class="idx-omnibar-extra-button" type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
    </form>
EOD;
    }

    public function price_field($min_price)
    {
        if(empty($min_price)){
            $price_field = '<div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div>';
        } else {
            $price_field = '<div class="idx-omnibar-extra idx-omnibar-price-container idx-omnibar-min-price-container"><label>Price Min</label><input class="idx-omnibar-min-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-price-container idx-omnibar-max-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div>';
        }

        return $price_field;
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
            'min_price' => 0,
            'styles' => 1,
            'extra' => 0,
        ), $atts));

        $idx_url = get_option('idx_results_url');
        $plugin_dir = plugins_url();

        if (!empty($extra)) {
            return $this->idx_omnibar_extra($plugin_dir, $idx_url, $styles, $min_price);
        } else {
            return $this->idx_omnibar_basic($plugin_dir, $idx_url, $styles);
        }
    }

    public function add_omnibar_extra_shortcode($atts)
    {
        extract(shortcode_atts(array(
            'min_price' => 0,
            'styles' => 1,
        ), $atts));

        $idx_url = get_option('idx_results_url');
        $plugin_dir = plugins_url();

        return $this->idx_omnibar_extra($plugin_dir, $idx_url, $styles, $min_price);
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

    public function register_impress_omnibar_widgets()
    {
        register_widget('\IDX\Widgets\Omnibar\IDX_Omnibar_Widget');
    }

    public function register_widgets()
    {
        //for PHP5.3 compatibility
        $scope = $this;

        add_action('widgets_init', function () use ($scope) {$scope->register_impress_omnibar_widgets();});

    }

}
