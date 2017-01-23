<?php
namespace IDX\Widgets;

class Impress_Showcase_Widget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {

        $this->idx_api = new \IDX\Idx_Api();

        parent::__construct(
            'impress_showcase', // Base ID
            'IMPress Property Showcase', // Name
            array(
                'description' => 'Displays a showcase of properties',
                'classname' => 'impress-showcase-widget',
                'customize_selective_refresh' => true,
            )
        );
    }

    public $idx_api;

    public $defaults = array(
        'title' => 'Properties',
        'properties' => 'featured',
        'saved_link_id' => '',
        'show_image' => '1',
        'use_rows' => '1',
        'listings_per_row' => 4,
        'max' => '',
        'order' => 'high-low',
        'geoip' => '',
        'geoip-location' => '',
        'styles' => 1,
        'new_window' => 0,
    );

    /**
     * Returns the markup for the featured properties
     *
     * @param array $instance Previously saved values from database.
     * @return string $output html markup for front end display
     */
    public function body($instance)
    {
        if (empty($instance)) {
            $instance = $this->defaults;
        }

        if ($instance['styles']) {
            wp_enqueue_style('impress-showcase', plugins_url('../assets/css/widgets/impress-showcase.css', dirname(__FILE__)));
        }

        if (($instance['properties']) == 'savedlinks') {
            $properties = $this->idx_api->saved_link_properties($instance['saved_link_id']);
        } else {
            $properties = $this->idx_api->client_properties($instance['properties']);
        }

        if (empty($properties) || (isset($properties) && $properties === 'No results returned') || gettype($properties) === 'object') {
            return 'No properties found';
        }

        //Force type as array.
        $properties = json_encode($properties);
        $properties = json_decode($properties, true);

        //Sort low to high.
        usort($properties, array($this, 'price_cmp'));

        if ('high-low' == $instance['order']) {
            $properties = array_reverse($properties);
        }

        $max = $instance['max'];
        $num_per_row = $instance['listings_per_row'];

        $total = count($properties);
        $count = 0;

        $output = '';

        $column_class = '';

        if (!isset($instance['new_window'])) {
            $instance['new_window'] = 0;
        }

        $target = $this->target($instance['new_window']);

        if (true == $instance['use_rows']) {

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

        foreach ($properties as $prop) {

            if (!empty($max) && $count == $max) {
                return $output;
            }

            $prop_image_url = (isset($prop['image']['0']['url'])) ? $prop['image']['0']['url'] : '//mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png';

            if (1 == $instance['use_rows'] && $count == 0 && $max != '1') {
                $output .= '<div class="impress-row">';
            }

            $prop = $this->set_missing_core_fields($prop);

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

            if (1 == $instance['show_image']) {
                $output .= sprintf(
                    '<div class="impress-showcase-property %12$s">
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
						</a>',
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
                $output .= '</div>';

                $output .= "</div>";
            } else {
                $output .= sprintf(
                    '<li class="impress-showcase-property-list %8$s">
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

                $output .= '<p class="impress-beds-baths-sqft">';
                $output .= $this->hide_empty_fields('beds', 'Beds', $prop['bedrooms']);
                $output .= $this->hide_empty_fields('baths', 'Baths', $prop['totalBaths']);
                $output .= $this->hide_empty_fields('sqft', 'SqFt', $prop['sqFt']);
                $output .= "</p>";
                $output .= "</a>";
                $output .= "</li>";

            }

            if (1 == $instance['use_rows'] && $count != 1) {

                // close a row if..
                // num_per_row is a factor of count OR
                // count is equal to the max number of listings to show OR
                // count is equal to the total number of listings available
                if ($count % $num_per_row == 0 || $count == $total || $count == $max) {
                    $output .= '</div> <!-- .row -->';
                }

                // open a new row if..
                // num per row is a factor of count AND
                // count is not equal to max AND
                // count is not equal to total
                if ($count % $num_per_row == 0 && $count != $max && $count != $total) {
                    $output .= '<div class="row">';
                }
            }
        }

        return $output;
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

    //Hide fields that have no data to avoid fields such as 0 Baths from displaying
    public function hide_empty_fields($field, $display_name, $value)
    {
        if ($value <= 0) {
            return '';
        } else {
            return "<span class=\"$field\">$value $display_name</span> ";
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

    /**
     * Converts the decimal to a percent
     *
     * @param mixed $num decimal to convert
     */
    public function calc_percent($num)
    {

        $num = round($num, 2);
        $num = preg_replace('/0\./', '', $num);

        if (strlen((string) $num) == 1) {
            $num *= 10;
        }

        $num = ($num == 100) ? 100 : $num -= 4;

        return $num;
    }

    /**
     * Compares the price fields of two arrays
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function price_cmp($a, $b)
    {

        $a = $this->clean_price($a['listingPrice']);
        $b = $this->clean_price($b['listingPrice']);

        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? -1 : 1;
    }

    /**
     * Removes the "$" and "," from the price field
     *
     * @param string $price
     * @return mixed $price the cleaned price
     */
    public function clean_price($price)
    {

        $patterns = array(
            '/\$/',
            '/,/',
        );

        $price = preg_replace($patterns, '', $price);

        return $price;
    }

    /**
     * Echos saved link names wrapped in option tags
     *
     * This is just a helper to keep the html clean
     *
     * @param var $instance
     */
    public function saved_link_options($instance)
    {

        $saved_links = $this->idx_api->idx_api_get_savedlinks();

        if (!is_array($saved_links)) {
            return;
        }

        foreach ($saved_links as $saved_link) {

            // display the link name if no link title has been assigned
            $link_text = empty($saved_link->linkTitle) ? $saved_link->linkName : $saved_link->linkTitle;

            echo '<option ', selected($instance['saved_link_id'], $saved_link->id, 0), ' value="', $saved_link->id, '">', $link_text, '</option>';

        }
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        $defaults = $this->defaults;

        $instance = wp_parse_args( (array) $instance, $defaults );

        extract($args);

        if (empty($instance)) {
            $instance = $this->defaults;
        }
        $title = $instance['title'];

        echo $before_widget;

        if ($instance['geoip'] && function_exists('turnkey_dashboard_setup')) {
            $geoip_before = '[geoip-content ' . $instance['geoip'] . '="' . $instance['geoip-location'] . '"]';
            $geoip_after = '[/geoip-content]';
            echo do_shortcode($geoip_before . $before_title . $title . $after_title . $this->body($instance) . $geoip_after);
        } else {
            if (!empty($title)) {
                echo $before_title . $title . $after_title;
            }

            echo $this->body($instance);
        }

        echo $after_widget;

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['properties'] = strip_tags($new_instance['properties']);
        $instance['saved_link_id'] = (int) ($new_instance['saved_link_id']);
        $instance['show_image'] = (bool) $new_instance['show_image'];
        $instance['listings_per_row'] = (int) $new_instance['listings_per_row'];
        $instance['max'] = strip_tags($new_instance['max']);
        $instance['order'] = strip_tags($new_instance['order']);
        $instance['use_rows'] = (bool) $new_instance['use_rows'];
        $instance['geoip'] = strip_tags($new_instance['geoip']);
        $instance['geoip-location'] = strip_tags($new_instance['geoip-location']);
        $instance['styles'] = strip_tags($new_instance['styles']);
        $instance['new_window'] = strip_tags($new_instance['new_window']);

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {

        $idx_api = $this->idx_api;

        $defaults = $this->defaults;

        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php echo 'Title:';?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php esc_attr_e($instance['title']);?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('properties');?>"><?php echo 'Properties to Display:';?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('properties');?>" name="<?php echo $this->get_field_name('properties')?>">
				<option <?php selected($instance['properties'], 'featured');?> value="featured"><?php echo 'Featured';?></option>
				<option <?php selected($instance['properties'], 'soldpending');?> value="soldpending"><?php echo 'Sold/Pending';?></option>
				<option <?php selected($instance['properties'], 'supplemental');?> value="supplemental"><?php echo 'Supplemental';?></option>
                <?php //Only allow Saved Links if Equity is active ?>
                <?php if (function_exists('equity')) {?>
				<option <?php selected($instance['properties'], 'savedlinks');?> value="savedlinks"><?php echo 'Use Saved Link';?></option>
                <?php }?>
			</select>
		</p>
        <?php //Only allow Saved Links if Equity is active ?>
        <?php if (function_exists('equity')) {?>
		<p>
			<label for="<?php echo $this->get_field_id('saved_link_id');?>">Choose a saved link (if selected above):</label>
			<select class="widefat" id="<?php echo $this->get_field_id('saved_link_id');?>" name="<?php echo $this->get_field_name('saved_link_id')?>">
				<?php $this->saved_link_options($instance);?>
			</select>
		</p>
        <?php }?>


		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_image'], 1);?> id="<?php echo $this->get_field_id('show_image');?>" name="<?php echo $this->get_field_name('show_image');?>" value="1" />
			<label for="<?php echo $this->get_field_id('show_image');?>"><?php echo 'Show image?';?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['use_rows'], 1);?> id="<?php echo $this->get_field_id('use_rows');?>" name="<?php echo $this->get_field_name('use_rows');?>" value="1" />
			<label for="<?php echo $this->get_field_id('use_rows');?>"><?php echo 'Use rows?';?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('listings_per_row');?>"><?php echo 'Listings per row:';?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('listings_per_row');?>" name="<?php echo $this->get_field_name('listings_per_row')?>">
				<option <?php selected($instance['listings_per_row'], '2');?> value="2">2</option>
				<option <?php selected($instance['listings_per_row'], '3');?> value="3">3</option>
				<option <?php selected($instance['listings_per_row'], '4');?> value="4">4</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('max');?>"><?php echo 'Max number of listings to show:';?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('max');?>" name="<?php echo $this->get_field_name('max');?>" type="number" value="<?php esc_attr_e($instance['max']);?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('order');?>"><?php echo 'Sort order:';?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('order');?>" name="<?php echo $this->get_field_name('order')?>">
				<option <?php selected($instance['order'], 'high-low');?> value="high-low"><?php echo 'Highest to Lowest Price';?></option>
				<option <?php selected($instance['order'], 'low-high');?> value="low-high"><?php echo 'Lowest to Highest Price';?></option>
			</select>
		</p>

		 <p>
            <label for="<?php echo $this->get_field_id('styles');?>"><?php _e('Default Styling?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('styles');?>" name="<?php echo $this->get_field_name('styles')?>" value="1" <?php checked($instance['styles'], true);?>>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('new_window');?>"><?php _e('Open Listings in a New Window?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('new_window');?>" name="<?php echo $this->get_field_name('new_window')?>" value="1" <?php checked($instance['new_window'], true);?>>
        </p>

		<?php if (function_exists('turnkey_dashboard_setup')) {?>
		<p>
			<label for="<?php echo $this->get_field_id('geoip');?>"><?php echo 'Only show content for (optional):';?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('geoip');?>" name="<?php echo $this->get_field_name('geoip')?>">
				<option <?php selected($instance['geoip'], '');?> value=""><?php echo 'All';?></option>
				<option <?php selected($instance['geoip'], 'region');?> value="region"><?php echo 'State';?></option>
				<option <?php selected($instance['geoip'], 'city');?> value="city"><?php echo 'City';?></option>
				<option <?php selected($instance['geoip'], 'postalcode');?> value="postalcode"><?php echo 'Postal Code';?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('geoip-location');?>"><?php echo 'Enter location to show for: <br /><em> Values can be comma separated.<br />For State, use 2 letter abbreviation.</em>';?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('geoip-location');?>" name="<?php echo $this->get_field_name('geoip-location');?>" type="text" value="<?php esc_attr_e($instance['geoip-location']);?>" />
		</p>

		<?php }
    }
}
