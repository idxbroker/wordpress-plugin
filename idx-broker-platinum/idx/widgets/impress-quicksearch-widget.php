<?php
namespace IDX\Widgets;

class IMPress_Quicksearch_Widget extends \WP_Widget {

	/**
	 * Instance of the impress_Idx_Api class
	 */

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		parent::__construct(
	 		'impress_quicksearch', // Base ID
			'IMPress Quick Search', // Name
			array(
				'description' => __( 'IDX quick search widget', 'idxbroker' ),
				'classname'   => 'impress-idx-search-widget'
			)
		);
	}

		public $idx_api;
		public $defaults = array(
			'title'       => __( 'Property Search', 'idxbroker' ),
			'city_list'   => 'combinedActiveMLS',
			'button_text' => 'Search Now',
			'orientation' => 'vertical',
			'adv_search'  => 1,
			'map_search'  => 1
		);


	/**
	 * Outputs the html of the widget for front end display
	 */
	public function body( $instance ) {

		$idx_api = $this->idx_api;

		$idx_qs_city_label = apply_filters( 'idx_qs_city_label', $idx_qs_city_label_text = __( 'Select a City', 'idxbroker' ) );
		$idx_qs_min_price_label = apply_filters( 'idx_qs_min_price_label', $idx_qs_min_price_label_text = __( 'Min Price', 'idxbroker' ) );
		$idx_qs_max_price_label = apply_filters( 'idx_qs_max_price_label', $idx_qs_max_price_label_text = __( 'Max Price', 'idxbroker' ) );
		$idx_qs_beds_label = apply_filters( 'idx_qs_beds_label', $idx_qs_beds_label_text = __( 'Beds', 'idxbroker' ) );
		$idx_qs_baths_label = apply_filters( 'idx_qs_baths_label', $idx_qs_baths_label_text = __( 'Baths', 'idxbroker' ) );

		if ( $instance['orientation'] == 'vertical' ) {

		?>

		<form class="impress-quicksearch-form vertical" method="post" action="<?php echo get_template_directory_uri() . '/lib/idx/search.php'; ?>">

			<div class="impress-qs-city">
				<label for="impress-qs-city-select" class="impress-qs-city-label">City</label>

				<select id="bqf-city-select" class="bqf-city-select" name="city[]">
					<option value=""><?php echo $idx_qs_city_label ;?></option>
					<?php $this->selected_city_list_city_options($instance); ?>
				</select>
			</div><!-- .impress-qs-city -->

			<div class="impress-qs-price-min">
				<label for="impress-qs-price-min" class="impress-qs-price-min-label"><?php echo $idx_qs_min_price_label ;?></label>
				<input type="text" id="impress-qs-price-min" class="impress-qs-price-min-input input" name="lp" />
			</div><!-- .impress-qs-price-min -->

			<div class="impress-qs-price-max">
				<label for="impress-qs-price-max" class="impress-qs-price-max-label"><?php echo $idx_qs_max_price_label ;?></label>
				<input type="text" id="impress-qs-price-max" class="impress-qs-price-max-input input" name="hp" />
			</div><!-- .impress-qs-price-max -->

			<div class="impress-qs-beds">
				<label for="impress-qs-beds" class="impress-qs-beds-label"><?php echo $idx_qs_beds_label ;?></label>
				<input type="text" id="impress-qs-beds" class="impress-qs-beds-input input" name="bd" />
			</div><!-- .impress-qs-beds -->

			<div class="impress-qs-baths">
				<label for="impress-qs-baths" class="impress-qs-baths-label"><?php echo $idx_qs_baths_label ;?></label>
				<input type="text" id="impress-qs-baths" class="impress-qs-baths-input input" name="ba" />
			</div><!-- .impress-qs-baths -->

			<input type="hidden" name="results_url" value="<?php echo $idx_api->system_results_url(); ?>" />

			<div class="impress-qs-submit impress-qs-form-bottom">
				<div class="impress-qs-submit-btn">
					<button class="impress-qs-submit-button button" type="submit" name="submit"><i class="fa fa-search"></i><span class="button-text"> <?php echo esc_attr( $instance['button_text'] ); ?></span></button>
				</div>
				<div class="impress-qs-links">
					<?php if ( $instance['adv_search']) {
						echo '<a class="advanced-search" href="' . apply_filters( 'impress_qs_adv_link', $impress_qs_adv_link = $idx_api->subdomain_url() . 'search/advanced') . '">' . __( 'Advanced Search', 'idxbroker' ) .'</a>';
					} ?>
					<?php if ( $instance['map_search']) {
						echo '<a class="map-search" href="' . apply_filters( 'impress_qs_map_link', $impress_qs_map_link = $idx_api->subdomain_url() . 'map/mapsearch') . '">' . __( 'Map Search', 'idxbroker' ) .'</a>';
					} ?>
				</div><!-- .impress-qs-links -->
			</div><!-- .impress-qs-form-bottom -->
		</form>
		<?php

		} else {

		?>

		<form class="impress-quicksearch-form horizontal row" method="post" action="<?php echo get_template_directory_uri() . '/lib/idx/search.php'; ?>">

			<div class="columns small-12 large-9">
				<div class="impress-qs-city columns small-12 large-12">
					<select id="bqf-city-select" class="bqf-city-select" name="city[]">
						<option value=""><?php echo $idx_qs_city_label ;?></option>
						<?php $this->selected_city_list_city_options($instance); ?>
					</select>
				</div><!-- .impress-qs-city -->

				<div class="impress-qs-price columns small-12 large-6">
					<input id="search-min-price" class="impress-qs-price-min" name="lp" type="text" placeholder="<?php echo $idx_qs_min_price_label ;?>" onblur="if (this.value == '') {this.value = '<?php echo $idx_qs_min_price_label ;?>';}" onfocus="if (this.value == '<?php echo $idx_qs_min_price_label ;?>') {this.value = '';}">
					<span>to</span>
					<input id="search-max-price" class="impress-qs-price-max" name="hp" type="text" placeholder="<?php echo $idx_qs_max_price_label ;?>" onblur="if (this.value == '') {this.value = '<?php echo $idx_qs_max_price_label ;?>';}" onfocus="if (this.value == '<?php echo $idx_qs_max_price_label ;?>') {this.value = '';}">
				</div><!-- .impress-qs-price -->

				<div class="impress-qs-beds columns small-6 large-3">
					<input id="search-beds" class="impress-qs-beds" name="bd" type="text" placeholder="<?php echo $idx_qs_beds_label ;?>" onblur="if (this.value == '') {this.value = '<?php echo $idx_qs_beds_label ;?>';}" onfocus="if (this.value == '<?php echo $idx_qs_beds_label ;?>') {this.value = '';}">
				</div><!-- .impress-qs-beds -->
				<div class="impress-qs-baths columns small-6 large-3">
					<input id="search-baths" class="impress-qs-baths" name="tb" type="text" placeholder="<?php echo $idx_qs_baths_label ;?>" onblur="if (this.value == '') {this.value = '<?php echo $idx_qs_baths_label ;?>';}" onfocus="if (this.value == '<?php echo $idx_qs_baths_label ;?>') {this.value = '';}">
				</div><!-- .impress-qs-baths -->
			</div> <!-- .small-12 large-3 -->


			<div class="impress-qs-submit-btn columns small-12 large-3">
				<input type="hidden" name="results_url" value="<?php echo $idx_api->system_results_url(); ?>" />
				<button class="impress-qs-submit-button button expand" type="submit" name="submit"><i class="fa fa-search"></i><span class="button-text"> <?php echo esc_attr( $instance['button_text'] ); ?></span></button>
				<div class="impress-qs-links">
					<?php if ( $instance['adv_search']) {
						echo '<a class="advanced-search" href="' . apply_filters( 'impress_qs_adv_link', $impress_qs_adv_link = $idx_api->subdomain_url() . 'search') . '">' . __( 'Advanced Search', 'idxbroker' ) .'</a>';
					} ?>
					<?php if ( $instance['map_search']) {
						echo '<a class="map-search" href="' . apply_filters( 'impress_qs_map_link', $impress_qs_map_link = $idx_api->subdomain_url() . 'map/mapsearch') . '">' . __( 'Map Search', 'idxbroker' ) .'</a>';
					} ?>
				</div><!-- .impress-qs-links -->
			</div><!-- .impress-qs-submit -->

		</form>

	<?php }

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		if(empty($instance)){
			$instance = $this->defaults;
		}
		$title = $instance['title'];

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		echo $this->body( $instance );

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['city_list'] = strip_tags($new_instance['city_list']);
		$instance['button_text'] = strip_tags($new_instance['button_text']);
		$instance['orientation'] = ($new_instance['orientation']);
		$instance['adv_search'] = (int) ($new_instance['adv_search']);
		$instance['map_search'] = (int) ($new_instance['map_search']);

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
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
			<label for="<?php echo $this->get_field_id( 'city_list' ); ?>"><?php _e( 'Select a city list:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'city_list' ); ?>" name="<?php echo $this->get_field_name( 'city_list') ?>">
				<option value="combinedActiveMLS">Combined active MLS</option>
				<?php $this->city_list_options($instance); ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Search Button Text:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php esc_attr_e( $instance['button_text'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'orientation' ); ?>"><?php _e( 'Orientation:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'orientation' ); ?>" name="<?php echo $this->get_field_name( 'orientation') ?>">
				<option value="vertical" <?php selected( 'vertical', $instance['orientation'] ); ?>><?php _e( 'Vertical', 'idxbroker' ); ?></option>
				<option value="horizontal" <?php selected( 'horizontal', $instance['orientation'] ); ?>><?php _e( 'Horizontal', 'idxbroker' ); ?></option>
			</select>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'adv_search' ); ?>" name="<?php echo $this->get_field_name( 'adv_search' ); ?>" value="1" <?php checked( $instance['adv_search'], true ); ?> />
			<label for="<?php echo $this->get_field_id( 'adv_search' ); ?>"><?php _e( 'Include Advanced Search link?', 'idxbroker' ); ?></label>

		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'map_search' ); ?>" name="<?php echo $this->get_field_name( 'map_search' ); ?>" value="1" <?php checked( $instance['map_search'], true ); ?> />
			<label for="<?php echo $this->get_field_id( 'map_search' ); ?>"><?php _e( 'Include Map Search link?', 'idxbroker' ); ?></label>

		</p>

		<?php
	}

	/**
	 * Echos city list ids wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public function city_list_options($instance) {

		$lists = $this->idx_api->city_list_names();

		if ( !is_array($lists) ) {
			return;
		}

		foreach($lists as $list) {
			echo '<option ', selected($instance['city_list'], $list->id, 0), ' value="', $list->id, '">', $list->name, '</option>';
		}
	}

	/**
	 * Echos the city names of the selected city list wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 */
	public function selected_city_list_city_options( $instance ) {

		if ( !isset($instance['city_list'] ) ) {
			$instance['city_list'] = 'combinedActiveMLS';
		}

		$cities = $this->idx_api->city_list($instance['city_list']);

		if ( !$cities ) {
			return;
		}

		foreach ($cities as $city_object) {
			echo '<option value="', $city_object->id, '">', $city_object->name, '</option>';
		}
	}

	/**
	 * Returns an array of the cities in the combined active MLS for autocomplete
	 */
	public function city_list_data_source( $instance ) {

		if ( !isset($instance['city_list'] ) ) {
			$instance['city_list'] = 'combinedActiveMLS';
		}

		$cities = $this->idx_api->city_list($instance['city_list']);

		if ( ! $cities ) {
			return;
		}

		$count = '';
		$output = '';

		foreach ($cities as $city) {

			$count++;

			if ( '' == $city->name ) {
				continue;

			}

			// Clean city names of single quotes which break the form
			$clean_city_name = str_replace("'", "", $city->name);
			$output .= '"' . $clean_city_name . ' (' . $city->id . ')"';

			if ( $count != count($cities) ) {
				$output .= ',';
			}

		}

		return '[' . $output . ']';
	}
}
