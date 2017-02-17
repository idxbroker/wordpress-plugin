<?php
namespace IDX\Shortcodes;

class Register_Impress_Shortcodes
{
    public $idx_api;

    public function __construct()
    {
        $this->idx_api = new \IDX\Idx_Api();
        add_shortcode('impress_lead_login', array($this, 'lead_login_shortcode'));
        if ($this->idx_api->platinum_account_type()) {
            add_action('wp_loaded', array($this, 'lead_signup_shortcode'));
        }
        add_shortcode('impress_property_showcase', array($this, 'property_showcase_shortcode'));
        add_shortcode('impress_property_carousel', array($this, 'property_carousel_shortcode'));
        add_shortcode('impress_city_links', array($this, 'city_links_shortcode'));

    }


    public function lead_login_shortcode($atts)
    {
        extract(shortcode_atts(array(
            'styles' => 1,
            'new_window' => 0,
        ), $atts));

        if (!empty($styles)) {
            wp_enqueue_style('impress-lead-login', plugins_url('../assets/css/widgets/impress-lead-login.css', dirname(__FILE__)));
        }

        if (!isset($new_window)) {
            $new_window = 0;
        }

        $target = $this->target($new_window);

        $widget = sprintf('
            <form action="%1$sajax/userlogin.php" class="impress-lead-login" method="post" target="%2$s" name="leadLoginForm">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="loginWidget" value="true">
                <label for="impress-widgetEmail">Email Address:</label>
                <input id="impress-widgetEmail" type="text" name="email" placeholder="Enter your email address"><input id="impress-widgetPassword" type="hidden" name="password" value=""><input id="impress-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
            </form>', $this->idx_api->subdomain_url(), $target);

        return $widget;
    }

    public function lead_signup_shortcode()
    {
        new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode();
        
    }

    public function property_showcase_shortcode($atts = array())
    {
        extract(shortcode_atts(array(
            'max' => 4,
            'use_rows' => 1,
            'num_per_row' => 4,
            'show_image' => 1,
            'order' => 'high-low',
            'property_type' => 'featured',
            'saved_link_id' => '',
            'styles' => 1,
            'new_window' => 0,
        ), $atts));

        if (!empty($styles)) {
            wp_enqueue_style('impress-showcase', plugins_url('../assets/css/widgets/impress-showcase.css', dirname(__FILE__)));
        }

        if (($property_type) == 'savedlinks') {
            $properties = $this->idx_api->saved_link_properties($saved_link_id);
        } else {
            $properties = $this->idx_api->client_properties($property_type);
        }
        //If no properties or an error, load message
        if (empty($properties) || (isset($properties) && $properties === 'No results returned') || gettype($properties) === 'object') {
            return 'No properties found';
        }

        $total = count($properties);
        $count = 0;

        $output = '';

        $column_class = '';

        if (1 == $use_rows) {
            // Max of four columns
            $number_columns = ($num_per_row > 4) ? 4 : (int) $num_per_row;

            // column class
            switch ($number_columns) {
                case 0:
                    $column_class = 'columns small-12 large-12';
                    break;
                case 1:
                    $column_class = 'columns small-12 large-12';
                    break;
                case 2:
                    $column_class = 'columns small-12 medium-6 large-6';
                    break;
                case 3:
                    $column_class = 'columns small-12 medium-4 large-4';
                    break;
                case 4:
                    $column_class = 'columns small-12 medium-3 large-3';
                    break;
            }
        }

        if (!isset($new_window)) {
            $new_window = 0;
        }

        $target = $this->target($new_window);

        //Force type as Array.
        $properties = json_encode($properties);
        $properties = json_decode($properties, true);

        // sort low to high
        usort($properties, array($this->idx_api, 'price_cmp'));

        if ('high-low' == $order) {
            $properties = array_reverse($properties);
        }

        foreach ($properties as $prop) {

            if (!empty($max) && $count == $max) {
                return $output;
            }

           $prop_image_url = (isset($prop['image']['0']['url'])) ? $prop['image']['0']['url'] : '//mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png';

            if (1 == $use_rows && $count == 0 && $max != '1') {
                $output .= '<div class="shortcode impress-property-showcase impress-row">';
            }

            if (empty($prop['propStatus'])) {
                $prop['propStatus'] = 'none';
            }

            $count++;

            //Add Disclaimer when applicable.
            if(isset($prop['disclaimer']) && !empty($prop['disclaimer'])) {
                foreach($prop['disclaimer'] as $disclaimer) {
                    if(in_array('widget', $disclaimer)) {
                        $disclaimer_text = $disclaimer['text'];
                        $disclaimer_logo = $disclaimer['logoURL'];
                    }
                }
            }
            //Add Courtesy when applicable.
            if(isset($prop['courtesy']) && !empty($prop['courtesy'])) {
                foreach($prop['courtesy'] as $courtesy) {
                    if(in_array('widget', $courtesy)) {
                        $courtesy_text = $courtesy['text'];
                    }
                }
            }

            $prop = $this->set_missing_core_fields($prop);

            if (1 == $show_image) {
                $output .= sprintf('<div class="impress-showcase-property %12$s">
                        <a href="%3$s" class="impress-showcase-photo" target="%13$s">
                            <img src="%4$s" alt="%5$s" title="%6$s %7$s %8$s %9$s %10$s, %11$s" />
                            <span class="impress-price">%1$s</span>
                            <span class="impress-status">%2$s</span>
                        </a>
                        <a href="%3$s" target="%13$s">
                            <p class="impress-address">
                                <span class="impress-street">%6$s %7$s %8$s %9$s</span>
                                <span class="impress-cityname">%10$s</span>,
                                <span class="impress-state"> %11$s</span>
                            </p>
                        </a>

                        ',
                    $prop['listingPrice'],
                    $prop['propStatus'],
                    $this->idx_api->details_url() . '/' . $prop['detailsURL'],
                    $prop_image_url,
                    htmlspecialchars($prop['remarksConcat']),
                    $prop['streetNumber'],
                    $prop['streetName'],
                    $prop['streetDirection'],
                    $prop['unitNumber'],
                    $prop['cityName'],
                    $prop['state'],
                    $column_class,
                    $target
                );

                $output .= '<p class="beds-baths-sqft">';
                $output .= $this->hide_empty_fields('beds', 'Beds', $prop['bedrooms']);
                $output .= $this->hide_empty_fields('baths', 'Baths', $prop['totalBaths']);
                $output .= $this->hide_empty_fields('sqft', 'SqFt', $prop['sqFt']);
                $output .= "</p>";

                //Add Disclaimer and Courtesy.
                $output .= '<div class="disclaimer">';
                (isset($disclaimer_text)) ? $output .= '<p style="display: block !important; visibility: visible !important; opacity: 1 !important; position: static !important;">' . $disclaimer_text . '</p>' : '';
                (isset($disclaimer_logo)) ? $output .= '<img class="logo" src="' . $disclaimer_logo . '" style="opacity: 1 !important; position: static !important;" />' : '';
                (isset($courtesy_text)) ? $output .= '<p class="courtesy" style="display: block !important; visibility: visible !important;">' . $courtesy_text . '</p>' : '';
                $output .= "</div>";

                $output .= "</div>";
            } else {
                $output .= sprintf(
                    '<li class="impress-showcase-property-list %9$s">
                        <a href="%2$s" target="%10$s">
                            <p>
                                <span class="impress-price">%1$s</span>
                                <span class="impress-address">
                                    <span class="impress-street">%3$s %4$s %5$s %6$s</span>
                                    <span class="impress-cityname">%7$s</span>,
                                    <span class="impress-state"> %8$s</span>
                                </span>',
                    $prop['listingPrice'],
                    $this->idx_api->details_url() . '/' . $prop['detailsURL'],
                    $prop['streetNumber'],
                    $prop['streetName'],
                    $prop['streetDirection'],
                    $prop['unitNumber'],
                    $prop['cityName'],
                    $prop['state'],
                    $column_class,
                    $target
                );

                $output .= '<span class="impress-beds-baths-sqft">';
                $output .= $this->hide_empty_fields('beds', 'Beds', $prop['bedrooms']);
                $output .= $this->hide_empty_fields('baths', 'Baths', $prop['totalBaths']);
                $output .= $this->hide_empty_fields('sqft', 'SqFt', $prop['sqFt']);
                $output .= "</span></p></a>";
                $output .= "</li>";
            }

            if (1 == $use_rows && $count != 1) {

                // close a row if..
                // num_per_row is a factor of count OR
                // count is equal to the max number of listings to show OR
                // count is equal to the total number of listings available
                if ($count % $num_per_row == 0 || $count == $total || $count == $max) {
                    $output .= '</div> <!-- .impress-row -->';
                }

                // open a new row if..
                // num per row is a factor of count AND
                // count is not equal to max AND
                // count is not equal to total
                if ($count % $num_per_row == 0 && $count != $max && $count != $total) {
                    $output .= '<div class="impress-row shortcode impress-property-showcase">';
                }
            }
        }

        return $output;

    }

    //Hide fields that have no data to avoid fields such as 0 Baths from displaying
    public function hide_empty_fields($field, $display_name, $value)
    {
        if ($value <= 0) {
            return '';
        } else {
            return "<span class=\"impress-$field\">$value $display_name</span> ";
        }
    }

    public function set_missing_core_fields($prop)
    {
        $name_values = array(
            'image',
            'remarksConcat',
            'detailsURL',
            'streetNumber',
            'streetName',
            'streetDirection',
            'unitNumber',
            'cityName',
            'state',
        );
        $number_values = array(
            'listingPrice',
            'bedrooms',
            'totalBaths',
            'sqFt',
        );
        foreach ($name_values as $field) {
            if (empty($prop[$field])) {
                $prop[$field] = '';
            }
        }
        foreach ($number_values as $field) {
            if (empty($prop[$field])) {
                $prop[$field] = 0;
            }
        }
        return $prop;

    }

    public function target($new_window)
    {
        if (!empty($new_window)) {
            //if enabled, open links in new tab/window
            return '_blank';
        } else {
            return '_self';
        }
    }

    public function property_carousel_shortcode($atts = array())
    {
        wp_enqueue_style('font-awesome-4.4.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.css');

        extract(shortcode_atts(array(
            'max' => 4,
            'display' => 3,
            'autoplay' => 1,
            'order' => 'high-low',
            'property_type' => 'featured',
            'saved_link_id' => '',
            'styles' => 1,
            'new_window' => 0,
        ), $atts));

        wp_enqueue_style('owl-css', plugins_url('../assets/css/widgets/owl.carousel.css', dirname(__FILE__)));
        wp_enqueue_script('owl', plugins_url('../assets/js/owl.carousel.min.js', dirname(__FILE__)));

        if ($styles) {
            wp_enqueue_style('impress-carousel', plugins_url('../assets/css/widgets/impress-carousel.css', dirname(__FILE__)));
        }

        if (!isset($new_window)) {
            $new_window = 0;
        }

        $target = $this->target($new_window);

        $prev_link = apply_filters('idx_listing_carousel_prev_link', $idx_listing_carousel_prev_link_text = __('<i class=\"fa fa-caret-left\"></i><span>Prev</span>', 'idxbroker'));
        $next_link = apply_filters('idx_listing_carousel_next_link', $idx_listing_carousel_next_link_text = __('<i class=\"fa fa-caret-right\"></i><span>Next</span>', 'idxbroker'));

        if (($property_type) === 'savedlinks') {
            $properties = $this->idx_api->saved_link_properties($saved_link_id);
        } else {
            $properties = $this->idx_api->client_properties($property_type);
        }
        //If no properties or an error, load message
        if (empty($properties) || (isset($properties) && $properties === 'No results returned') || gettype($properties) === 'object') {
            return 'No properties found';
        }

        //Force type as array.
        $properties = json_encode($properties);
        $properties = json_decode($properties, true);

        // sort low to high
        usort($properties, array($this->idx_api, 'price_cmp'));

        if ('high-low' == $order) {
            $properties = array_reverse($properties);
        }

        if ($autoplay == 1) {
            $autoplay_param = 'autoPlay: true,';
        } else {
            $autoplay_param = '';
        }

        //All Instance Values are strings for shortcodes but not widgets.
        if ($display === "1") {
            $output = '
            <script>
            jQuery(function( $ ){
                jQuery(".impress-listing-carousel-' . $display . '").owlCarousel({
                    singleItem: true,
                    ' . $autoplay_param . '
                    navigation: true,
                    navigationText: ["' . $prev_link . '", "' . $next_link . '"],
                    pagination: false,
                    lazyLoad: true,
                    addClassActive: true,
                    itemsScaleUp: true
                });
            });
            </script>
            ';
        } else {
            $output = '
            <script>
            jQuery(function( $ ){
                jQuery(".impress-listing-carousel-' . $display . '").owlCarousel({
                    items: ' . $display . ',
                    ' . $autoplay_param . '
                    navigation: true,
                    navigationText: ["' . $prev_link . '", "' . $next_link . '"],
                    pagination: false,
                    lazyLoad: true,
                    addClassActive: true,
                    itemsScaleUp: true
                });
            });
            </script>
            ';
        }

        $count = 0;

        $output .= sprintf('<div class="impress-carousel impress-listing-carousel-%s impress-carousel-shortcode">', $display);

        foreach ($properties as $prop) {

            if (!empty($max) && $count == $max) {
                $output .= '</div><!-- end .impress-listing-carousel -->';
                return $output;
            }

            $prop_image_url = (isset($prop['image']['0']['url'])) ? $prop['image']['0']['url'] : '//mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png';

            $count++;

            //Add Disclaimer when applicable.
            if(isset($prop['disclaimer']) && !empty($prop['disclaimer'])) {
                foreach($prop['disclaimer'] as $disclaimer) {
                    if(in_array('widget', $disclaimer)) {
                        $disclaimer_text = $disclaimer['text'];
                        $disclaimer_logo = $disclaimer['logoURL'];
                    }
                }
            }
            //Add Courtesy when applicable.
            if(isset($prop['courtesy']) && !empty($prop['courtesy'])) {
                foreach($prop['courtesy'] as $courtesy) {
                    if(in_array('widget', $courtesy)) {
                        $courtesy_text = $courtesy['text'];
                    }
                }
            }

            $prop = $this->set_missing_core_fields($prop);

            $output .= sprintf(
                '<div class="impress-carousel-property">
                    <a href="%2$s" class="impress-carousel-photo" target="%11$s">
                        <img class="lazyOwl" data-src="%3$s" alt="%4$s" title="%5$s %6$s %7$s %8$s %9$s, %10$s" />
                        <span class="impress-price">%1$s</span>
                    </a>
                    <a href="%2$s" target="%11$s">
                        <p class="impress-address">
                            <span class="impress-street">%5$s %6$s %7$s %8$s</span>
                            <span class="impress-cityname">%9$s</span>,
                            <span class="impress-state"> %10$s</span>
                        </p>
                    </a>',
                $prop['listingPrice'],
                $this->idx_api->details_url() . '/' . $prop['detailsURL'],
                $prop_image_url,
                htmlspecialchars($prop['remarksConcat']),
                $prop['streetNumber'],
                $prop['streetName'],
                $prop['streetDirection'],
                $prop['unitNumber'],
                $prop['cityName'],
                $prop['state'],
                $target
            );

            $output .= '<p class="impress-beds-baths-sqft">';
            $output .= $this->hide_empty_fields('beds', 'Beds', $prop['bedrooms']);
            $output .= $this->hide_empty_fields('baths', 'Baths', $prop['totalBaths']);
            $output .= $this->hide_empty_fields('sqft', 'SqFt', $prop['sqFt']);
            $output .= "</p>";

            //Add Disclaimer and Courtesy.
            $output .= '<div class="disclaimer">';
            (isset($disclaimer_text)) ? $output .= '<p style="display: block !important; visibility: visible !important; opacity: 1 !important; position: static !important;">' . $disclaimer_text . '</p>' : '';
            (isset($disclaimer_logo)) ? $output .= '<img class="logo" src="' . $disclaimer_logo . '" style="opacity: 1 !important; position: static !important;" />' : '';
            (isset($courtesy_text)) ? $output .= '<p class="courtesy" style="display: block !important; visibility: visible !important;">' . $courtesy_text . '</p>' : '';
            $output .= "</div>";

            $output .= "</div>";
        }

        $output .= '</div><!-- end .impress-listing-carousel -->';

        return $output;
    }

    public function city_links_shortcode($atts = array())
    {
        extract(shortcode_atts(array(
            'city_list' => 'combinedActiveMLS',
            'mls' => 'a000',
            'use_columns' => 1,
            'number_columns' => 4,
            'styles' => 1,
            'new_window' => 0,
        ), $atts));

        if (!empty($styles)) {
            wp_enqueue_style('impress-city-links', plugins_url('../assets/css/widgets/impress-city-links.css', dirname(__FILE__)));
        }

        if (!isset($new_window)) {
            $new_window = 0;
        }

        $target = $this->target($new_window);

        $city_links = "<div class=\"impress-city-links\">";
        $city_links .= \IDX\Widgets\Impress_City_Links_Widget::city_list_links($city_list, $mls, $use_columns, $number_columns, $target, $this->idx_api);
        $city_links .= "</div>";

        if (false == $city_links) {
            return 'City list ID or MLS ID not found';
        }
        $city_links .= '<style>.impress-city-list-links ul {margin-left: 0;}</style>';
        return $city_links;
    }

    /**
     * Add support for Shortcake (Shortcode UI)
     *
     * @see  https://github.com/fusioneng/Shortcake
     * @since 1.5
     *
     */
    public function register_shortcake()
    {
        if (function_exists('shortcode_ui_register_for_shortcode')) {
            //* Lead Login
            shortcode_ui_register_for_shortcode(
                'lead_login',
                array(
                    'label' => 'Lead Login',
                    'listItemImage' => 'dashicons-admin-network',
                )
            );

            //* Lead Signup
            if ($this->idx_api->platinum_account_type()) {
                shortcode_ui_register_for_shortcode(
                    'lead_signup',
                    array(
                        'label' => 'Lead Signup',
                        'listItemImage' => 'dashicons-admin-users',
                        'attrs' => array(
                            array(
                                'label' => 'Require Phone?',
                                'attr' => 'phone',
                                'type' => 'radio',
                                'value' => 0,
                                'options' => array(
                                    1 => 'Yes',
                                    0 => 'No',
                                ),
                            ),
                        ),
                    )
                );
            }

            //* Property Showcase
            //$saved_links = $this->idx_api->saved_links();
            shortcode_ui_register_for_shortcode(
                'property_showcase',
                array(
                    'label' => 'Property Showcase',
                    'listItemImage' => 'dashicons-admin-home',
                    'attrs' => array(
                        array(
                            'label' => 'Max Number of Listings',
                            'attr' => 'max',
                            'type' => 'number',
                            'value' => 8,
                        ),
                        array(
                            'label' => 'Use Rows',
                            'attr' => 'use_rows',
                            'type' => 'radio',
                            'value' => 1,
                            'options' => array(
                                1 => 'Yes',
                                0 => 'No',
                            ),
                        ),
                        array(
                            'label' => 'Number per row',
                            'attr' => 'num_per_row',
                            'type' => 'number',
                            'value' => 4,
                        ),
                        array(
                            'label' => 'Order',
                            'attr' => 'order',
                            'type' => 'select',
                            'value' => 'high-low',
                            'options' => array(
                                'high-low' => 'High to Low',
                                'low-high' => 'Low to High',
                            ),
                        ),
                        array(
                            'label' => 'Show Image',
                            'attr' => 'show_image',
                            'type' => 'radio',
                            'value' => 1,
                            'options' => array(
                                1 => 'Yes',
                                0 => 'No',
                            ),
                        ),
                        array(
                            'label' => 'Property Type',
                            'attr' => 'property_type',
                            'type' => 'select',
                            'value' => 'featured',
                            'options' => array(
                                'featured' => 'Featured',
                                'soldpending' => 'Sold/Pending',
                                'historical' => 'Historical',
                                'supplemental' => 'Supplemental',
                                'savedlinks' => 'Saved Link',
                            ),
                        ),
                        array(
                            'label' => 'Saved Link ID',
                            'attr' => 'saved_link_id',
                            'type' => 'text',
                            'value' => '',
                        ),
                    ),
                )
            );

            //* Property Carousel
            shortcode_ui_register_for_shortcode(
                'property_carousel',
                array(
                    'label' => 'Property Carousel',
                    'listItemImage' => 'dashicons-admin-home',
                    'attrs' => array(
                        array(
                            'label' => 'Max Number of Listings',
                            'attr' => 'max',
                            'type' => 'number',
                            'value' => 4,
                        ),
                        array(
                            'label' => 'Number to Display without scrolling',
                            'attr' => 'display',
                            'type' => 'number',
                            'value' => 3,
                        ),
                        array(
                            'label' => 'Order',
                            'attr' => 'order',
                            'type' => 'select',
                            'value' => 'high-low',
                            'options' => array(
                                'high-low' => 'High to Low',
                                'low-high' => 'Low to High',
                            ),
                        ),
                        array(
                            'label' => 'Autoplay',
                            'attr' => 'autoplay',
                            'type' => 'radio',
                            'value' => 1,
                            'options' => array(
                                1 => 'Yes',
                                0 => 'No',
                            ),
                        ),
                        array(
                            'label' => 'Property Type',
                            'attr' => 'property_type',
                            'type' => 'select',
                            'value' => 'featured',
                            'options' => array(
                                'featured' => 'Featured',
                                'soldpending' => 'Sold/Pending',
                                'historical' => 'Historical',
                                'supplemental' => 'Supplemental',
                                'savedlinks' => 'Saved Link',
                            ),
                        ),
                        array(
                            'label' => 'Saved Link ID',
                            'attr' => 'saved_link_id',
                            'type' => 'text',
                            'value' => '',
                        ),
                    ),
                )
            );

            //* City Links
            shortcode_ui_register_for_shortcode(
                'city_links',
                array(
                    'label' => 'City Links',
                    'listItemImage' => 'dashicons-editor-ul',
                    'attrs' => array(
                        array(
                            'label' => 'City List',
                            'attr' => 'city_list',
                            'type' => 'text',
                            'value' => 'combinedActiveMLS',
                        ),
                        array(
                            'label' => 'MLS ID',
                            'attr' => 'mls',
                            'type' => 'text',
                            'value' => 'a000',
                        ),
                        array(
                            'label' => 'Use Columns?',
                            'attr' => 'use_columns',
                            'type' => 'radio',
                            'value' => 1,
                            'options' => array(
                                1 => 'Yes',
                                0 => 'No',
                            ),
                        ),
                        array(
                            'label' => 'Number of Columns',
                            'attr' => 'number_columns',
                            'type' => 'select',
                            'value' => 4,
                            'options' => array(
                                2 => '2',
                                3 => '3',
                                4 => '4',
                            ),
                        ),
                    ),
                )
            );
        }
    }

}
