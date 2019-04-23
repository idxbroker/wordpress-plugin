<?php
namespace IDX\Widgets;

/**
 * Impress_City_Links_Widget class.
 */
class Impress_City_Links_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		parent::__construct(
			'impress_city_links', // Base ID
			'IMPress City Links', // Name
			array(
				'description'                 => __( 'Outputs a list of city links', 'idxbroker' ),
				'classname'                   => 'impress-city-links-widget',
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * defaults
	 *
	 * @var mixed
	 * @access public
	 */
	public $defaults = array(
		'title'          => 'Explore Cities',
		'city_list'      => 'combinedActiveMLS',
		'mls'            => '',
		'use_columns'    => 0,
		'number_columns' => 4,
		'styles'         => 1,
		'show_count'     => 0,
		'new_window'     => 0,
	);

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $args );
		if ( empty( $instance ) ) {
			$instance = $this->defaults;
		}

		if ( $instance['styles'] ) {
			wp_enqueue_style( 'impress-city-links', plugins_url( '../assets/css/widgets/impress-city-links.css', dirname( __FILE__ ) ) );
		}

		if ( ! isset( $instance['new_window'] ) ) {
			$instance['new_window'] = 0;
		}

		$target = $this->target( $instance['new_window'] );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$idx_api = $this->idx_api;

		// For testing with demo data
		if ( empty( $instance['mls'] ) && defined( 'IDX_TESTING' ) ) {
			$instance['mls'] = 'a000';
		}

		if ( empty( $instance['mls'] ) ) {
			echo 'Invalid MLS IDX ID. Email help@idxbroker.com to get your MLS IDX ID';
		} else {
			echo '<div class="impress-city-links">';
			echo $this->city_list_links( $instance['city_list'], $instance['mls'], $instance['use_columns'], $instance['number_columns'], $target, $instance['show_count'], $this->idx_api );
			echo '</div>';
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
	public function update( $new_instance, $old_instance ) {
		$instance                   = array();
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['city_list']      = strip_tags( $new_instance['city_list'] );
		$instance['mls']            = strip_tags( $new_instance['mls'] );
		$instance['use_columns']    = (int) $new_instance['use_columns'];
		$instance['number_columns'] = (int) $new_instance['number_columns'];
		$instance['styles']         = (int) $new_instance['styles'];
		$instance['show_count']     = (bool) $new_instance['show_count'];
		$instance['new_window']     = (int) $new_instance['new_window'];

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @uses IMPress_City_Links_Widget::city_list_options()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$idx_api = $this->idx_api;

		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'mls' ); ?>">MLS to use for the city links: *required*</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'mls' ); ?>" name="<?php echo $this->get_field_name( 'mls' ); ?>">
				<?php echo $this->mls_options( $instance, $this->idx_api ); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'city_list' ); ?>">Select a city list:</label>
			<select class="city-list-options widefat" id="<?php echo $this->get_field_id( 'city_list' ); ?>" name="<?php echo $this->get_field_name( 'city_list' ); ?>">
				<?php echo $this->city_list_options( $instance, $this->idx_api ); ?>
			</select>
		</p>
		<p class="show-count">
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_count'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="1" />
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">Show number of listings in each city?</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['use_columns'], 1 ); ?> id="<?php echo $this->get_field_id( 'use_columns' ); ?>" name="<?php echo $this->get_field_name( 'use_columns' ); ?>" value="1" />
			<label for="<?php echo $this->get_field_id( 'use_columns' ); ?>">Split links into columns?</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_columns' ); ?>">Number of columns</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'number_columns' ); ?>" name="<?php echo $this->get_field_name( 'number_columns' ); ?>">
				<option <?php selected( $instance['number_columns'], 2 ); ?> value="2">2</option>
				<option <?php selected( $instance['number_columns'], 3 ); ?> value="3">3</option>
				<option <?php selected( $instance['number_columns'], 4 ); ?> value="4">4</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'styles' ); ?>"><?php _e( 'Default Styling?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'styles' ); ?>" name="<?php echo $this->get_field_name( 'styles' ); ?>" value="1" <?php checked( $instance['styles'], true ); ?>>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'new_window' ); ?>"><?php _e( 'Open Links in a New Window?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'new_window' ); ?>" name="<?php echo $this->get_field_name( 'new_window' ); ?>" value="1" <?php checked( $instance['new_window'], true ); ?>>
		</p>
		<p>Don't have any city lists? Go create some in your <a href="http://middleware.idxbroker.com/mgmt/citycountyziplists.php" target="_blank">IDX dashboard.</a></p>
		<?php
	}

	/**
	 * Echos city list ids wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public static function city_list_options( $instance, $idx_api ) {
		$lists  = $idx_api->city_list_names();
		$output = '';

		if ( ! is_array( $lists ) ) {
			return;
		}

		foreach ( $lists as $list ) {
			// display the list id if no list name has been assigned
			$list_text = empty( $list->name ) ? $list->id : $list->name;

			$output .= '<option class="city-list-option" ' . selected( $instance['city_list'], $list->id, 0 ) . ' value="' . $list->id . '">' . $list_text . '</option>';
		}
		return $output;
	}

	/**
	 * Echos the approved mls names wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public static function mls_options( $instance, $idx_api ) {
		$approved_mls = $idx_api->approved_mls();
		$output       = '';

		if ( ! is_array( $approved_mls ) ) {
			return;
		}
		foreach ( $approved_mls as $mls ) {
			$output .= '<option ' . selected( $instance['mls'], $mls->id . 0 ) . ' value="' . $mls->id . '">' . $mls->name . '</option>';
		}
		return $output;
	}

	/**
	 * target function.
	 *
	 * @access public
	 * @param mixed $new_window
	 * @return void
	 */
	public function target( $new_window ) {
		if ( ! empty( $new_window ) ) {
			// if enabled, open links in new tab/window
			return '_blank';
		} else {
			return '_self';
		}
	}

	/**
	 * Returns an unordered list of city links
	 *
	 * @param int|string the id of the city list to pull cities from
	 * @param bool                                                   $columns if true adds column classes to the ul tags
	 * @param int                                                    $number_of_columns optional total number of columns to split the links into
	 */
	public static function city_list_links( $list_id, $idx_id, $columns = 0, $number_columns = 4, $target, $show_count = false, $idx_api ) {
		$cities = $idx_api->city_list( $list_id );

		if ( ! $cities ) {
			return false;
		}

		$column_class = '';

		if ( true == $columns ) {

			// Max of four columns
			$number_columns = ( $number_columns > 4 ) ? 4 : (int) $number_columns;

			$number_links = count( $cities );

			$column_size = $number_links / $number_columns;

			// if more columns than links make one column for every link
			if ( $column_size < 1 ) {
				$number_columns = $number_links;
			}

			// round the column size up to a whole number
			$column_size = ceil( $column_size );

			// column class
			switch ( $number_columns ) {
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

		$output =
		'<div class="impress-city-list-links impress-city-list-links-' . $list_id . ' impress-row">' . "\n\t";

		$output .= ( true == $columns ) ? '<ul class="impress-' . $column_class . '">' : '<ul>';

		$count = 0;

		$cities_list = array();

		foreach ( $cities as $city ) {

			$count++;

			$href = $idx_api->subdomain_url() . 'city-' . $idx_id . '-' . rawurlencode( $city->name ) . '-' . $city->id;

			if ( has_filter( 'impress_city_links_url_suffix' ) ) {
				$href = apply_filters( 'impress_city_links_url_suffix', $href, $link = null );
			}

			// do not add empty city names, ids, duplicates, or 'Other' cities
			if ( ! empty( $city->name ) && ! empty( $city->id ) && ! in_array( $city->id, $cities_list ) && $city->name !== 'Other' && $city->name !== 'Out of State' && $city->name !== 'Out of Area' ) {
				// avoid duplicates by keeping track of cities already used
				array_push( $cities_list, $city->id );

				if ( $show_count ) {
					$listing_count = self::get_cumulative_property_count_from_mls( $city->id, $idx_id, $idx_api );
					$number        = ( ! is_wp_error( $listing_count ) && is_numeric( $listing_count ) ) ? $listing_count : 0;
					$output       .= "\n\t\t" .
					'<li>' .
					"\n\t\t\t" .
					'<a href="' .
					$href .
					'" target="' .
					$target .
					'">' .
					$city->name .
					' <span class="count">' .
					$number .
					' <span class="screen-reader-text">Listings</span></span>' .
					'</a>' .
					"\n\t\t" .
					'</li>';
				} else {
					$output .= "\n\t\t" .
					'<li>' .
					"\n\t\t\t" .
					'<a href="' .
					$href .
					'" target="' .
					$target .
					'">' .
					$city->name .
					'</a>' .
					"\n\t\t" .
					'</li>';
				}
			}

			if ( true == $columns && $count % $column_size == 0 && $count != 1 && $count != $number_links ) {
				$output .= "\n\t" . '</ul>' . "\n\t" . '<ul class="impress-' . $column_class . '">';
			}
		}

		$output .= "\n\t" . '</ul>' . "\n" . '</div><!-- .city-list-links -->';

		return $output;
	}

	/**
	 * Get cumulative number of occurances (properties) for a given city ID.
	 *
	 * @param  string   $city_id The city ID.
	 * @param  string   $idx_id  The IDX (MLS) ID
	 * @param  callable $idx_api The IDX API class.
	 * @return int               The total number of occurances.
	 */
	public static function get_cumulative_property_count_from_mls( $city_id, $idx_id, $idx_api ) {
		 $mls_cities = $idx_api->idx_api( 'cities/' . $idx_id, \IDX\Initiate_Plugin::IDX_API_DEFAULT_VERSION, 'mls', array(), 60 * 60 * 24, 'GET', true );

		// Loop through cities and add number to $occurances if $city_id matches.
		$occurances = 0;
		foreach ( $mls_cities as $city ) {
			if ( ! isset( $city['cityID'] ) ) {
				return;
			}
			if ( $city_id !== $city['cityID'] ) {
				continue;
			}
			$occurances += $city['occurances'];
		}

		return $occurances;
	}
}
