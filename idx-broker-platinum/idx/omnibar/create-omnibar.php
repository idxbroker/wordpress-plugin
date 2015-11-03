<?php
namespace IDX\Omnibar;

class Create_Omnibar
{
    public function __construct()
    {
        $this->register_shortcodes();
        $this->register_widgets();
    }

    public function idx_omnibar_basic($plugin_dir, $idx_url)
    {
        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.3.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css');
        wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/idx-omnibar.min.css', __FILE__));
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', __FILE__));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', plugins_url('../../assets/js/locationlist.json', __FILE__));

        return <<<EOD
        <form class="idx-omnibar-form idx-omnibar-original-form">
          <input class="idx-omnibar-input" type="text" placeholder="City, Postal Code, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'City, Postal Code, Address, or Listing ID';}" onfocus="if (this.value == 'City, Postal Code, Address, or Listing ID') {this.value = '';}"><button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
          <div class="idx-omnibar-extra idx-omnibar-price-container" style="display: none;"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container" style="display: none;"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container" style="display: none;"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01"></div>
        </form>
EOD;
    }

    public function idx_omnibar_extra($plugin_dir, $idx_url)
    {
        //css and js have been minified and combined to help performance
        wp_enqueue_style('font-awesome-4.3.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css');
        wp_enqueue_style('idx-omnibar', plugins_url('../../assets/css/idx-omnibar.min.css', __FILE__));
        wp_register_script('idx-omnibar-js', plugins_url('../../assets/js/idx-omnibar.min.js', __FILE__));
        //inserts inline variable for the results page url
        wp_localize_script('idx-omnibar-js', 'idxUrl', $idx_url);
        wp_enqueue_script('idx-omnibar-js');
        wp_enqueue_script('idx-location-list', plugins_url('../../assets/js/locationlist.json', __FILE__));

        return <<<EOD
    <form class="idx-omnibar-form idx-omnibar-extra-form">
      <input class="idx-omnibar-input" type="text" placeholder="City, Postal Code, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'City, Postal Code, Address, or Listing ID';}" onfocus="if (this.value == 'City, Postal Code, Address, or Listing ID') {this.value = '';}">
      <div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0" title="No commas or dollar signs are allowed."></div><div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01" title="Only numbers and decimals are allowed"></div>
      <button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
    </form>
EOD;
    }

    public function add_omnibar_shortcode()
    {
        $idx_url = get_option('idx-results-url');
        $plugin_dir = plugins_url();

        return $this->idx_omnibar_basic($plugin_dir, $idx_url);
    }

    public function add_omnibar_extra_shortcode()
    {
        $idx_url = get_option('idx-results-url');
        $plugin_dir = plugins_url();

        return $this->idx_omnibar_extra($plugin_dir, $idx_url);
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
        //Initialize Instances of Widget Classes
        add_action('widgets_init', create_function('', 'return register_widget("\IDX\Omnibar\IDX_Omnibar_Widget");'));
        add_action('widgets_init', create_function('', 'return register_widget("\IDX\Omnibar\IDX_Omnibar_Widget_Extra");'));

    }

}
