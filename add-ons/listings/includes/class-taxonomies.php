<?php
/**
 * This file contains the WP_Listings_Taxonomies class.
 */

/**
 * This class handles all the aspects of displaying, creating, and editing the
 * user-created taxonomies for the "Listings" post-type.
 *
 */
class WP_Listings_Taxonomies {

	public $settings_field = 'wp_listings_taxonomies';
	public $menu_page      = 'register-taxonomies';
	public $reorder_page   = 'reorder-taxonomies';

	/**
	 * Construct Method.
	 */
	public function __construct() {

		add_action( 'admin_init', [ &$this, 'register_settings' ] );
		add_action( 'admin_menu', [ &$this, 'settings_init' ], 15 );
		add_action( 'admin_init', [ &$this, 'actions' ] );
		add_action( 'admin_notices', [ &$this, 'notices' ] );
		add_action( 'admin_enqueue_scripts', [ &$this, 'tax_reorder_enqueue' ] );

		add_action( 'init', [ &$this, 'register_taxonomies' ], 15 );
		add_action( 'init', [ $this, 'create_terms' ], 16 );
		add_action( 'init', [ $this, 'register_term_meta' ], 17 );

		if ( function_exists( 'get_term_meta' ) ) {
			add_action( 'init', [ $this, 'register_term_meta' ], 17 );
			foreach ( (array) $this->get_taxonomies() as $slug => $data ) {
				add_action( "{$slug}_add_form_fields", [ $this, 'wp_listings_new_term_image_field' ] );
				add_action( "{$slug}_edit_form_fields", [ $this, 'wp_listings_edit_term_image_field' ] );
				add_action( "create_{$slug}", [ $this, 'wp_listings_save_term_image' ] );
				add_action( "edit_{$slug}", [ $this, 'wp_listings_save_term_image' ] );
				add_filter( "manage_edit-{$slug}_columns", [ $this, 'wp_listings_edit_term_columns' ] );
				add_action( "manage_{$slug}_custom_column", [ $this, 'wp_listings_manage_term_custom_column' ], 10, 3 );
			}
		}

		add_action( 'restrict_manage_posts', [ $this, 'wp_listings_filter_post_type_by_taxonomy' ] );
		add_filter( 'parse_query', [ $this, 'wp_listings_convert_id_to_term_in_query' ] );

	}

	public function register_settings() {

		register_setting( $this->settings_field, $this->settings_field );
		add_option( $this->settings_field, __return_empty_array(), '', 'yes' );

	}

	public function settings_init() {

		add_submenu_page( 'edit.php?post_type=listing', __( 'Register Taxonomies', 'wp-listings' ), __( 'Register Taxonomies', 'wp-listings' ), 'manage_options', $this->menu_page, [ &$this, 'admin' ] );
		add_submenu_page( 'edit.php?post_type=listing', __( 'Reorder Taxonomies', 'wp-listings' ), __( 'Reorder Taxonomies', 'wp-listings' ), 'manage_options', $this->reorder_page, [ &$this, 'tax_reorder' ] );

	}

	public function actions() {

		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != $this->menu_page ) {
			return;
		}

		/** This section handles the data if a new taxonomy is created */
		if ( isset( $_REQUEST['action'] ) && 'create' == $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_listings-action_create-taxonomy' ) ) {
			$this->create_taxonomy( $_POST['wp_listings_taxonomy'] );
		}

		/** This section handles the data if a taxonomy is deleted */
		if ( isset( $_REQUEST['action'] ) && 'delete' == $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_listings-action_delete-taxonomy' ) ) {
			$this->delete_taxonomy( $_REQUEST['id'] );
		}

		/** This section handles the data if a taxonomy is being edited */
		if ( isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_listings-action_edit-taxonomy' ) ) {
			$this->edit_taxonomy( $_POST['wp_listings_taxonomy'] );
		}

	}

	public function tax_reorder_enqueue() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	public function admin() {

		echo '<div class="wrap">';

			if ( isset( $_REQUEST['view'] ) && 'edit' == $_REQUEST['view'] ) {
				require( dirname( __FILE__ ) . '/views/edit-tax.php' );
			} else {
				require( dirname( __FILE__ ) . '/views/create-tax.php' );
			}

		echo '</div>';

	}

	public function create_taxonomy( $args = [] ) {

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html_e( 'Manage options capabilities required to create a taxonomy.', 'wp_listings' ) );
		}
		/** No empty fields */
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			wp_die( __( 'Please complete all required fields.', 'wp-listings' ) );
		}
		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			wp_die( __( 'Please complete all required fields.', 'wp-listings' ) );
		}
		if ( ! isset( $args['singular_name'] ) || empty( $args['singular_name'] ) ) {
			wp_die( __( 'Please complete all required fields.', 'wp-listings' ) );
		}

		extract( $args );

		$labels = [
			'name'                  => wp_strip_all_tags( $name ),
			'singular_name'         => wp_strip_all_tags( $singular_name ),
			'menu_name'             => wp_strip_all_tags( $name ),
			'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
			'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
			'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
			'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
			'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
			'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
		];

		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => [
				'slug'       => $id,
				'with_front' => false,
			],
			'editable'     => 1,
		];

		$tax = [ $id => $args ];

		$options = get_option( $this->settings_field );

		/** Update the options */
		update_option( $this->settings_field, wp_parse_args( $tax, $options ) );

		/** Flush rewrite rules */
		$this->register_taxonomies();
		flush_rewrite_rules();

	}

	public function delete_taxonomy( $id = '' ) {

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html_e( 'Manage options capabilities required to delete a taxonomy.', 'wp_listings' ) );
		}
		/** No empty ID */
		if ( ! isset( $id ) || empty( $id ) ) {
			wp_die( esc_html__( "Nice try, partner. But that taxonomy doesn't exist. Click back and try again.", 'wp-listings' ) );
		}

		$options = get_option( $this->settings_field );

		/** Look for the ID, delete if it exists */
		if ( array_key_exists( $id, (array) $options ) ) {
			unset( $options[ $id ] );
		} else {
			wp_die( esc_html__( "Nice try, partner. But that taxonomy doesn't exist. Click back and try again.", 'wp-listings' ) );
		}

		/** Update the DB */
		update_option( $this->settings_field, $options );

	}

	public function edit_taxonomy( $args = [] ) {

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html_e( 'Manage options capabilities required to edit a taxonomy.', 'wp_listings' ) );
		}
		/** No empty fields */
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			wp_die( esc_html__( 'Please complete all required fields.', 'wp-listings' ) );
		}
		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			wp_die( esc_html__( 'Please complete all required fields.', 'wp-listings' ) );
		}
		if ( ! isset( $args['singular_name'] ) || empty( $args['singular_name'] ) ) {
			wp_die( esc_html__( 'Please complete all required fields.', 'wp-listings' ) );
		}

		extract( $args );

		$labels = [
			'name'                  => strip_tags( $name ),
			'singular_name'         => strip_tags( $singular_name ),
			'menu_name'             => strip_tags( $name ),
			'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
			'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
			'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
			'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
			'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
			'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
			'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
		];

		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => [
				'slug'       => $id,
				'with_front' => false,
			],
			'editable'     => 1,
		];

		$tax = [ $id => $args ];

		$options = get_option( $this->settings_field );

		update_option( $this->settings_field, wp_parse_args( $tax, $options ) );

	}

	public function notices() {

		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != $this->menu_page ) {
			return;
		}

		if ( isset( $_REQUEST['created'] ) && 'true' == $_REQUEST['created'] ) {
			echo '<div id="message" class="updated"><p><strong>' . esc_html__( 'New taxonomy successfully created!', 'wp-listings' ) . '</strong></p></div>';
		}

		if ( isset( $_REQUEST['edited'] ) && 'true' == $_REQUEST['edited'] ) {
			echo '<div id="message" class="updated"><p><strong>' . esc_html__( 'Taxonomy successfully edited!', 'wp-listings' ) . '</strong></p></div>';
		}

		if ( isset( $_REQUEST['deleted'] ) && 'true' == $_REQUEST['deleted'] ) {
			echo '<div id="message" class="updated"><p><strong>' . esc_html__( 'Taxonomy successfully deleted.', 'wp-listings' ) . '</strong></p></div>';
		}

	}

	/**
	 * Register the status taxonomy, manually.
	 */
	public function listing_status_taxonomy() {

		$name          = __( 'Status', 'wp-listings' );
		$singular_name = __( 'Status', 'wp-listings' );

		return [
			'status' => [
				'labels'                => [
					'name'                  => strip_tags( $name ),
					'singular_name'         => strip_tags( $singular_name ),
					'menu_name'             => strip_tags( $name ),
					'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
				],
				'hierarchical'          => true,
				'rewrite'               => [
					__( 'status', 'wp-listings' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'listing-status',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			],
		];

	}

	/**
	 * Register the property-types taxonomy, manually.
	 */
	public function property_type_taxonomy() {

		$name          = __( 'Property Types', 'wp-listings' );
		$singular_name = __( 'Property Type', 'wp-listings' );

		return [
			'property-types' => [
				'labels'                => [
					'name'                  => strip_tags( $name ),
					'singular_name'         => strip_tags( $singular_name ),
					'menu_name'             => strip_tags( $name ),
					'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
				],
				'hierarchical'          => true,
				'rewrite'               => [
					__( 'property-types', 'wp-listings' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'property-types',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			],
		];

	}

	/**
	 * Register the location taxonomy, manually.
	 */
	public function listing_location_taxonomy() {

		$name          = __( 'Locations', 'wp-listings' );
		$singular_name = __( 'Location', 'wp-listings' );

		return [
			'locations' => [
				'labels' => [
					'name'                  => strip_tags( $name ),
					'singular_name'         => strip_tags( $singular_name ),
					'menu_name'             => strip_tags( $name ),
					'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
				],
				'hierarchical'          => true,
				'rewrite'               => [
					__( 'locations', 'wp-listings' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'locations',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			],
		];

	}

	/**
	 * Register the property features taxonomy, manually.
	 */
	public function property_features_taxonomy() {

		$name          = __( 'Features', 'wp-listings' );
		$singular_name = __( 'Feature', 'wp-listings' );

		return [
			'features' => [
				'labels' => [
					'name'                  => wp_strip_all_tags( $name ),
					'singular_name'         => wp_strip_all_tags( $singular_name ),
					'menu_name'             => wp_strip_all_tags( $name ),
					'search_items'          => sprintf( __( 'Search %s', 'wp-listings' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'wp-listings' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'wp-listings' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'wp-listings' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'wp-listings' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'wp-listings' ), strip_tags( $name ) ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'wp-listings' ), strip_tags( $name ) ),
				],
				'hierarchical'          => 0,
				'rewrite'               => [
					__( 'features', 'wp-listings' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'features',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			],
		];

	}

	/**
	 * Create the taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( (array) $this->get_taxonomies() as $id => $data ) {
			register_taxonomy( $id, [ 'listing' ], $data );
		}
	}

	/**
	 * Get the taxonomies.
	 */
	public function get_taxonomies() {
		return array_merge( $this->listing_status_taxonomy(), $this->listing_location_taxonomy(), $this->property_type_taxonomy(), $this->property_features_taxonomy(), (array) get_option( $this->settings_field ) );
	}

	/**
	 * Create the default terms
	 * @uses  wp_listings_default_status_terms filter to add or remove default taxonomy terms
	 * @uses  wp_listings_default_property_type_terms filter to add or remove default taxonomy terms
	 */
	public function create_terms() {

		/** Default terms for status */
		$status_terms = apply_filters( 'wp_listings_default_status_terms', [ 'Active' => 'active', 'Pending' => 'pending', 'For Rent' => 'for-rent', 'Sold' => 'sold', 'Featured' => 'featured', 'New' => 'new', 'Reduced' => 'reduced' ] );
		foreach ( $status_terms as $term => $slug ) {
			if ( term_exists( $term, 'status' ) ) {
				continue;
			}
			wp_insert_term( $term, 'status', [ 'slug' => $slug ] );
		}

		/** Default terms for property-type */
		$property_type_terms = apply_filters( 'wp_listings_default_property_type_terms', [ 'Residential' => 'residential', 'Condo' => 'condo', 'Townhome' => 'townhome', 'Commercial' => 'commercial' ] );
		foreach ( $property_type_terms as $term => $slug ) {
			if ( term_exists( $term, 'property-types' ) ) {
				continue;
			}
			wp_insert_term( $term, 'property-types', [ 'slug' => $slug ] );
		}

	}

	/**
	 * Register term meta for a featured image
	 * @return [type] [description]
	 */
	public function register_term_meta() {
		register_meta( 'term', 'wpl_term_image', 'wp_listings_sanitize_term_image' );
	}

	/**
	 * Callback to retrieve the term image
	 * @return [type] [description]
	 */
	public function wp_listings_sanitize_term_image( $wpl_term_image ) {
		return $wpl_term_image;
	}

	/**
	 * Get the term featured image id
	 * @param  $html bool whether to use html wrapper
	 * @uses  wp_get_attachment_image to return image id wrapped in markup
	 */
	public function wp_listings_get_term_image( $term_id, $html = true ) {
		$image_id = get_term_meta( $term_id, 'wpl_term_image', true );
		return $image_id && $html ? wp_get_attachment_image( $image_id, 'thumbnail' ) : $image_id;
	}

	/**
	 * Save the image uploaded
	 * @param  string $term_id term slug
	 */
	public function wp_listings_save_term_image( $term_id ) {

		if ( ! isset( $_POST['wpl_term_image_nonce'] ) || ! wp_verify_nonce( $_POST['wpl_term_image_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		$old_image = $this->wp_listings_get_term_image( $term_id );
		$new_image = isset( $_POST['wpl-term-image'] ) ? $_POST['wpl-term-image'] : '';

		if ( $old_image && '' === $new_image ) {
			delete_term_meta( $term_id, 'wpl_term_image' );
		} elseif ( $old_image !== $new_image ) {
			update_term_meta( $term_id, 'wpl_term_image', $new_image );
		}

		return $term_id;
	}

	/**
	 * Filter the edit term columns
	 */
	public function wp_listings_edit_term_columns( $columns ) {

		$columns['wpl_term_image'] = __( 'Image', 'wp-listings' );

		return $columns;
	}

	/**
	 * Display the new column
	 */
	public function wp_listings_manage_term_custom_column( $out, $column, $term_id ) {

		if ( 'wpl_term_image' === $column ) {

			$image_id = $this->wp_listings_get_term_image( $term_id, false );

			if ( ! $image_id ) {
				return $out;
			}

			$image_markup = wp_get_attachment_image( $image_id, 'thumbnail', true, [ 'class' => 'wpl-term-image' ] );

			$out = $image_markup;
		}

		return $out;
	}

	/**
	 * Display a custom taxonomy dropdown in admin
	 */
	public function wp_listings_filter_post_type_by_taxonomy() {
		global $typenow;
		$post_type  = 'listing';
		$taxonomies = [ 'property-types', 'status', 'locations' ];
		foreach ( $taxonomies as $taxonomy ) {
			if ( $typenow == $post_type ) {
				$selected      = isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : '';
				$info_taxonomy = get_taxonomy( $taxonomy );
				wp_dropdown_categories(
					[
						'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
						'taxonomy'        => $taxonomy,
						'name'            => $taxonomy,
						'orderby'         => 'name',
						'selected'        => $selected,
						'show_count'      => true,
						'hide_empty'      => true,
					]
				);
			};
		}
	}

	/**
	 * Filter posts by taxonomy in admin
	 */
	public function wp_listings_convert_id_to_term_in_query( $query ) {
		global $pagenow;
		$post_type  = 'listing';
		$taxonomies = [ 'property-types', 'status', 'locations' ];
		$q_vars     = &$query->query_vars;
		foreach ( $taxonomies as $taxonomy ) {
			if ( $pagenow == 'edit.php' && isset( $q_vars['post_type']  ) && $q_vars['post_type'] == $post_type && isset( $q_vars[$taxonomy] ) && is_numeric( $q_vars[$taxonomy] ) && $q_vars[$taxonomy] != 0 ) {
				$term              = get_term_by( 'id', $q_vars[$taxonomy], $taxonomy );
				$q_vars[ $taxonomy ] = $term->slug;
			}
		}
	}

	/**
	 * Field for adding a new image on a term
	 */
	public function wp_listings_new_term_image_field( $term ) {

		$image_id = '';

		wp_nonce_field( basename( __FILE__ ), 'wpl_term_image_nonce' ); ?>

		<div class="form-field wpl-term-image-wrap">
			<label for="wpl-term-image"><?php esc_html_e( 'Image', 'wp-listings' ); ?></label>
			<!-- Begin term image -->
			<p>
				<input type="hidden" name="wpl-term-image" id="wpl-term-image" value="<?php echo esc_attr( $image_id ); ?>" />
				<a href="#" class="wpl-add-media wpl-add-media-img"><img class="wpl-term-image-url" src="" style="max-width: 100%; max-height: 200px; height: auto; display: block;" /></a>
				<a href="#" class="wpl-add-media wpl-add-media-text"><?php esc_html_e( 'Set term image', 'wp-listings' ); ?></a>
				<a href="#" class="wpl-remove-media"><?php esc_html_e( 'Remove term image', 'wp-listings' ); ?></a>
			</p>
			<!-- End term image -->
		</div>
		<?php
	}

	/**
	 * Field for editing an image on a term.
	 */
	public function wp_listings_edit_term_image_field( $term ) {

		$image_id = $this->wp_listings_get_term_image( $term->term_id, false );
		$image_url = wp_get_attachment_url( $image_id );

		if ( ! $image_url ) {
			$image_url = '';
		}
		?>

		<tr class="form-field wpl-term-image-wrap">
			<th scope="row"><label for="wpl-term-image"><?php esc_html_e( 'Image', 'wp-listings' ); ?></label></th>
			<td>
				<?php wp_nonce_field( basename( __FILE__ ), 'wpl_term_image_nonce' ); ?>
				<!-- Begin term image -->
				<p>
					<input type="hidden" name="wpl-term-image" id="wpl-term-image" value="<?php echo esc_attr( $image_id ); ?>" />
					<a href="#" class="wpl-add-media wpl-add-media-img"><img class="wpl-term-image-url" src="<?php echo esc_url( $image_url ); ?>" style="max-width: 100%; max-height: 200px; height: auto; display: block;" /></a>
					<a href="#" class="wpl-add-media wpl-add-media-text"><?php esc_html_e( 'Set term image', 'wp-listings' ); ?></a>
					<a href="#" class="wpl-remove-media"><?php esc_html_e( 'Remove term image', 'wp-listings' ); ?></a>
				</p>
				<!-- End term image -->
			</td>
		</tr>
	<?php }

	/**
	 * Reorder taxonomies
	 */
	public function tax_reorder() {
		$wp_listings_taxes = get_option( 'wp_listings_taxonomies' );

		if ( $_POST ) {
			$new_order                   = sanitize_text_field( wp_unslash( $_POST['wp_listings_taxonomy'] ) );
			$wp_listings_taxes_reordered = [];
			foreach ( $new_order as $tax ) {
				if ( $wp_listings_taxes[ $tax ] ) {
					$wp_listings_taxes_reordered[ $tax ] = $wp_listings_taxes[ $tax ];
				}
			}
			$wp_listings_taxes = $wp_listings_taxes_reordered;
			update_option( 'wp_listings_taxonomies', $wp_listings_taxes_reordered );

		}?>
	<h2><?php esc_html_e( 'Reorder Taxonomies', 'wp-listings' ); ?></h2>
	<div id="col-container">
		<div class="updated"><?php esc_html_e( 'Note: This will only allow you to reorder user-created taxonomies. Default taxonomies cannot be reordered (Status, Locations, Property Types, Features).', 'wp-listings' ); ?> </div>
		<style>
#sortable{list-style-type:none;margin:10px 0;padding:0}#sortable li .item{-moz-border-radius:6px 6px 6px 6px;border:1px solid #e6e6e6;font-weight:bold;height:auto;line-height:35px;overflow:hidden;padding-left:10px;position:relative;text-shadow:0 1px 0 white;width:auto;word-wrap:break-word;cursor:move;background:none repeat-x scroll left top #dfdfdf;-moz-box-shadow:2px 2px 3px #888;-webkit-box-shadow:2px 2px 3px #888;box-shadow:2px 2px 3px #888}#sortable li span{position:absolute;margin-left:-1.3em}.ui-state-highlight{background:#e6e6e6;border:1px #666 dashed}.wplistings-submit{padding:5px 10px}.wplistings-submit:hover{background:#eaf2fa;font-weight:bold}
		</style>
		<script>
		jQuery(function($) {
			jQuery( "#sortable" ).sortable({ placeholder: 'ui-state-highlight', forcePlaceholderSize: true});
			jQuery( "#sortable" ).disableSelection();
		});
		</script>
		<div id="col-left">
		<div class="col-wrap">
		<p><?php esc_html_e( 'Drag and Drop to reorder', 'wp-listings' ); ?></p>
		<form method="post">
		<ul id="sortable">
			<?php
			foreach ( $wp_listings_taxes as $wp_listings_tax_key => $wp_listings_tax_value ) {
				?>
				<li class="ui-state-default">
					<div class="item">
						<?php echo esc_html( $wp_listings_tax_value['labels']['name'] ); ?><input type="hidden" id="wp_listings_taxonomy[]" name="wp_listings_taxonomy[]" value="<?php echo esc_attr( $wp_listings_tax_key ); ?>" />
					</div>
				</li>
			<?php } ?>
		</ul>
		<input class="wplistings-submit" type="submit" value="Save" />
		</form>
		</div>
		</div><!-- /col-left -->

	</div><!-- /col-container -->
		<?php
	}

}
