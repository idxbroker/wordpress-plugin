<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * IMPress_Agents_Taxonomies
 *
 * This class handles all the aspects of displaying, creating, and editing the
 * user-created taxonomies for the "Employees" post-type.
 */
class IMPress_Agents_Taxonomies {

	/**
	 * Settings_Field
	 *
	 * @var string
	 * @access public
	 */
	public $settings_field = 'impress_agents_taxonomies';

	/**
	 * Menu_Page
	 *
	 * @var string
	 * @access public
	 */
	public $menu_page = 'impress-agents-taxonomies';

	/**
	 * Construct Method.
	 */
	public function __construct() {

		add_action( 'admin_init', [ &$this, 'register_settings' ] );
		add_action( 'admin_menu', [ &$this, 'settings_init' ], 15 );
		add_action( 'admin_init', [ &$this, 'actions' ] );
		add_action( 'admin_notices', [ &$this, 'notices' ] );

		add_action( 'init', [ &$this, 'register_taxonomies' ], 15 );

		if ( function_exists( 'get_term_meta' ) ) {
			add_action( 'init', [ $this, 'register_term_meta' ], 17 );

			foreach ( (array) $this->get_taxonomies() as $slug => $data ) {
				add_action( "{$slug}_add_form_fields", [ $this, 'impress_agents_new_term_image_field' ] );
				add_action( "{$slug}_edit_form_fields", [ $this, 'impress_agents_edit_term_image_field' ] );
				add_action( "create_{$slug}", [ $this, 'impress_agents_save_term_image' ] );
				add_action( "edit_{$slug}", [ $this, 'impress_agents_save_term_image' ] );
				add_filter( "manage_edit-{$slug}_columns", [ $this, 'impress_agents_edit_term_columns' ] );
				add_action( "manage_{$slug}_custom_column", [ $this, 'impress_agents_manage_term_custom_column' ], 10, 3 );
			}
		}

		add_action( 'restrict_manage_posts', [ $this, 'impress_agents_filter_post_type_by_taxonomy' ] );
		add_filter( 'parse_query', [ $this, 'impress_agents_convert_id_to_term_in_query' ] );

	}

	public function register_settings() {

		register_setting( $this->settings_field, $this->settings_field );
		add_option( $this->settings_field, __return_empty_array(), '', 'yes' );

	}

	public function settings_init() {

		add_submenu_page( 'edit.php?post_type=employee', __( 'Register Taxonomies', 'impress_agents' ), __( 'Register Taxonomies', 'impress_agents' ), 'manage_options', $this->menu_page, [ &$this, 'admin' ] );
	}

	public function actions() {

		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != $this->menu_page ) {
			return;
		}

		/** This section handles the data if a new taxonomy is created */
		if ( isset( $_REQUEST['action'] ) && 'create' === $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'impress_agents-action_create-taxonomy' ) ) {
			$this->create_taxonomy( $_REQUEST['impress_agents_taxonomy'] );
		}

		/** This section handles the data if a taxonomy is deleted */
		if ( isset( $_REQUEST['action'] ) && 'delete' == $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'impress_agents-action_delete-taxonomy' ) ) {
			$this->delete_taxonomy( $_REQUEST['id'] );
		}

		/** This section handles the data if a taxonomy is being edited */
		if ( isset( $_REQUEST['action'] ) && 'edit' == $_REQUEST['action'] && wp_verify_nonce( $_REQUEST['_wpnonce'], 'impress_agents-action_edit-taxonomy' ) ) {
			$this->edit_taxonomy( $_REQUEST['impress_agents_taxonomy'] );
		}

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
			wp_die( esc_html_e( 'Manage options capabilities required to create a taxonomy.', 'impress_agents' ) );
		}
		/** No empty fields */
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}
		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}
		if ( ! isset( $args['singular_name'] ) || empty( $args['singular_name'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}

		extract( $args );

		$labels = [
			'name'                  => strip_tags( $name ),
			'singular_name'         => strip_tags( $singular_name ),
			'menu_name'             => strip_tags( $name ),
			'search_items'          => sprintf( __( 'Search %s', 'impress_agents' ), strip_tags( $name ) ),
			'popular_items'         => sprintf( __( 'Popular %s', 'impress_agents' ), strip_tags( $name ) ),
			'all_items'             => sprintf( __( 'All %s', 'impress_agents' ), strip_tags( $name ) ),
			'edit_item'             => sprintf( __( 'Edit %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'update_item'           => sprintf( __( 'Update %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'new_item_name'         => sprintf( __( 'New %s Name', 'impress_agents' ), strip_tags( $singular_name ) ),
			'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'impress_agents' ), strip_tags( $name ) ),
			'choose_from_most_used'	=> sprintf( __( 'Choose from the most used %s', 'impress_agents' ), strip_tags( $name ) )
		];

		$args = [
			'labels'       => $labels,
			'hierarchical' => true,
			'rewrite'      => [
				'slug' => $id,
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
			wp_die( esc_html_e( 'Manage options capabilities required to delete a taxonomy.', 'impress_agents' ) );
		}
		/** No empty ID */
		if ( ! isset( $id ) || empty( $id ) ) {
			wp_die( esc_html_e( "Nice try, partner. But that taxonomy doesn't exist. Click back and try again.", 'impress_agents' ) );
		}

		$options = get_option( $this->settings_field );

		/** Look for the ID, delete if it exists */
		if ( array_key_exists( $id, (array) $options ) ) {
			unset( $options[$id] );
		} else {
			wp_die( esc_html_e( "Nice try, partner. But that taxonomy doesn't exist. Click back and try again.", 'impress_agents' ) );
		}

		/** Update the DB */
		update_option( $this->settings_field, $options );

	}

	public function edit_taxonomy( $args = [] ) {

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html_e( 'Manage options capabilities required to edit a taxonomy.', 'impress_agents' ) );
		}
		/** No empty fields */
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}
		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}
		if ( ! isset( $args['singular_name'] ) || empty( $args['singular_name'] ) ) {
			wp_die( esc_html_e( 'Please complete all required fields.', 'impress_agents' ) );
		}

		extract( $args );

		$labels = [
			'name'					=> strip_tags( $name ),
			'singular_name' 		=> strip_tags( $singular_name ),
			'menu_name'				=> strip_tags( $name ),

			'search_items'          => sprintf( __( 'Search %s', 'impress_agents' ), strip_tags( $name ) ),
			'popular_items'         => sprintf( __( 'Popular %s', 'impress_agents' ), strip_tags( $name ) ),
			'all_items'             => sprintf( __( 'All %s', 'impress_agents' ), strip_tags( $name ) ),
			'edit_item'             => sprintf( __( 'Edit %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'update_item'           => sprintf( __( 'Update %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'impress_agents' ), strip_tags( $singular_name ) ),
			'new_item_name'         => sprintf( __( 'New %s Name', 'impress_agents' ), strip_tags( $singular_name ) ),
			'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'impress_agents' ), strip_tags( $name ) ),
			'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'impress_agents' ), strip_tags( $name ) ),
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

		$format = '<div id="message" class="updated"><p><strong>%s</strong></p></div>';

		if ( isset( $_REQUEST['created'] ) && 'true' == $_REQUEST['created'] ) {
			printf( $format, __('New taxonomy successfully created!', 'impress_agents') );
			return;
		}

		if ( isset( $_REQUEST['edited'] ) && 'true' == $_REQUEST['edited'] ) {
			printf( $format, __('Taxonomy successfully edited!', 'impress_agents') );
			return;
		}

		if ( isset( $_REQUEST['deleted'] ) && 'true' == $_REQUEST['deleted'] ) {
			printf( $format, __('Taxonomy successfully deleted.', 'impress_agents') );
			return;
		}

		return;

	}

	/**
	 * Register the job-types taxonomy, manually.
	 */
	public function employee_job_type_taxonomy() {

		$name          = __( 'Job Types', 'impress_agents' );
		$singular_name = __( 'Job Type', 'impress_agents' );

		return [
			'job-types' => [
				'labels'                => [
					'name'                  => strip_tags( $name ),
					'singular_name'         => strip_tags( $singular_name ),
					'menu_name'             => strip_tags( $name ),
					'search_items'          => sprintf( __( 'Search %s', 'impress_agents' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'impress_agents' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'impress_agents' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'impress_agents' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'impress_agents' ), strip_tags( $name ) ),
					'choose_from_most_used'	=> sprintf( __( 'Choose from the most used %s', 'impress_agents' ), strip_tags( $name ) ),
				],
				'hierarchical'          => true,
				'rewrite'               => [
					__( 'job-types', 'impress_agents' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'job-types',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			]
		];

	}

	/**
	 * Register the offices taxonomy, manually.
	 */
	public function employee_offices_taxonomy() {

		$name          = __( 'Offices', 'impress_agents' );
		$singular_name = __( 'Office', 'impress_agents' );

		return [
			'offices' => [
				'labels'                => [
					'name'                  => strip_tags( $name ),
					'singular_name'         => strip_tags( $singular_name ),
					'menu_name'             => strip_tags( $name ),

					'search_items'          => sprintf( __( 'Search %s', 'impress_agents' ), strip_tags( $name ) ),
					'popular_items'         => sprintf( __( 'Popular %s', 'impress_agents' ), strip_tags( $name ) ),
					'all_items'             => sprintf( __( 'All %s', 'impress_agents' ), strip_tags( $name ) ),
					'edit_item'             => sprintf( __( 'Edit %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'update_item'           => sprintf( __( 'Update %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'add_new_item'          => sprintf( __( 'Add New %s', 'impress_agents' ), strip_tags( $singular_name ) ),
					'new_item_name'         => sprintf( __( 'New %s Name', 'impress_agents' ), strip_tags( $singular_name ) ),
					'add_or_remove_items'   => sprintf( __( 'Add or Remove %s', 'impress_agents' ), strip_tags( $name ) ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'impress_agents' ), strip_tags( $name ) ),
				],
				'hierarchical'          => true,
				'rewrite'               => [
					__( 'offices', 'impress_agents' ),
					'with_front' => false,
				],
				'editable'              => 0,
				'show_in_rest'          => true,
				'rest_base'             => 'offices',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
			],
		];

	}

	/**
	 * Create the taxonomies.
	 */
	public function register_taxonomies() {
		foreach ( (array) $this->get_taxonomies() as $id => $data ) {
			register_taxonomy( $id, [ 'employee' ], $data );
		}
	}

	/**
	 * Get the taxonomies.
	 */
	public function get_taxonomies() {
		return array_merge( $this->employee_offices_taxonomy(), $this->employee_job_type_taxonomy(), (array) get_option( $this->settings_field ) );
	}

	/**
	 * Register term meta for a featured image
	 */
	public function register_term_meta() {
		register_meta( 'term', 'impa_term_image', 'impress_agents_sanitize_term_image' );
	}

	/**
	 * Callback to retrieve the term image
	 * @return [type] [description]
	 */
	public function impress_agents_sanitize_term_image( $impa_term_image ) {
		return $impa_term_image;
	}

	/**
	 * Get the term featured image id
	 * @param  $html bool whether to use html wrapper
	 * @uses  wp_get_attachment_image to return image id wrapped in markup
	 */
	public function impress_agents_get_term_image( $term_id, $html = true ) {
		$image_id = get_term_meta( $term_id, 'impa_term_image', true );
		return $image_id && $html ? wp_get_attachment_image( $image_id, 'thumbnail' ) : $image_id;
	}

	/**
	 * Save the image uploaded
	 * @param  string $term_id term slug
	 */
	public function impress_agents_save_term_image( $term_id ) {
		if ( ! isset( $_POST['impa_term_image_nonce'] ) || ! wp_verify_nonce( $_POST['impa_term_image_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		$old_image = $this->impress_agents_get_term_image( $term_id );
		$new_image = isset( $_POST['impa-term-image'] ) ? $_POST['impa-term-image'] : '';

		if ( $old_image && '' === $new_image ) {
			delete_term_meta( $term_id, 'impa_term_image' );
		} elseif ( $old_image !== $new_image ) {
			update_term_meta( $term_id, 'impa_term_image', $new_image );
		}
		return $term_id;
	}

	/**
	 * Filter the edit term columns
	 */
	public function impress_agents_edit_term_columns( $columns ) {
		$columns['impa_term_image'] = __( 'Image', 'impress_agents' );
		return $columns;
	}

	/**
	 * Display the new column
	 */
	public function impress_agents_manage_term_custom_column( $out, $column, $term_id ) {
		if ( 'impa_term_image' === $column ) {
			$image_id = $this->impress_agents_get_term_image( $term_id, false );
			if ( ! $image_id ) {
				return $out;
			}
			$image_markup = wp_get_attachment_image( $image_id, 'thumbnail', true, [ 'class' => 'impa-term-image' ] );
			$out          = $image_markup;
		}
		return $out;
	}

	/**
	 * Display a custom taxonomy dropdown in admin
	 */
	public function impress_agents_filter_post_type_by_taxonomy() {
		global $typenow;
		$post_type   = 'employee';
		$taxonomies  = [ 'job-types', 'offices' ];
		foreach ( $taxonomies as $taxonomy ) {
			if ( $typenow == $post_type ) {
				$selected      = isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : '';
				$info_taxonomy = get_taxonomy( $taxonomy );
				wp_dropdown_categories(
					[
						'show_option_all' => __("Show All {$info_taxonomy->label}"),
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
	public function impress_agents_convert_id_to_term_in_query( $query ) {
		global $pagenow;
		$post_type  = 'employee';
		$taxonomies = [ 'job-types', 'offices' ];
		$q_vars     = &$query->query_vars;
		foreach ( $taxonomies as $taxonomy ) {
			if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[$taxonomy] ) && is_numeric( $q_vars[$taxonomy] ) && $q_vars[$taxonomy] != 0 ) {
				$term              = get_term_by( 'id', $q_vars[$taxonomy], $taxonomy );
				$q_vars[ $taxonomy ] = $term->slug;
			}
		}
	}

	/**
	 * Field for adding a new image on a term
	 */
	public function impress_agents_new_term_image_field( $term ) {

		$image_id = '';

		wp_nonce_field( basename( __FILE__ ), 'impa_term_image_nonce' ); ?>

		<div class="form-field impa-term-image-wrap">
			<label for="impa-term-image"><?php esc_html_e( 'Image', 'impress_agents' ); ?></label>
			<!-- Begin term image -->
			<p>
				<input type="hidden" name="impa-term-image" id="impa-term-image" value="<?php echo esc_attr( $image_id ); ?>" />
				<a href="#" class="impa-add-media impa-add-media-img"><img class="impa-term-image-url" src="" style="max-width: 100%; max-height: 200px; height: auto; display: block;" /></a>
				<a href="#" class="impa-add-media impa-add-media-text"><?php esc_html_e( 'Set term image', 'impress_agents' ); ?></a>
				<a href="#" class="impa-remove-media"><?php esc_html_e( 'Remove term image', 'impress_agents' ); ?></a>
			</p>
			<!-- End term image -->
		</div>
		<?php
	}

	/**
	 * Field for editing an image on a term
	 */
	public function impress_agents_edit_term_image_field( $term ) {

		$image_id  = $this->impress_agents_get_term_image( $term->term_id, false );
		$image_url = wp_get_attachment_url( $image_id );

		if ( ! $image_url ) {
			$image_url = '';
		}
		?>

		<tr class="form-field impa-term-image-wrap">
			<th scope="row"><label for="impa-term-image"><?php esc_html_e( 'Image', 'impress_agents' ); ?></label></th>
			<td>
				<?php wp_nonce_field( basename( __FILE__ ), 'impa_term_image_nonce' ); ?>
				<!-- Begin term image -->
				<p>
					<input type="hidden" name="impa-term-image" id="impa-term-image" value="<?php echo esc_attr( $image_id ); ?>" />
					<a href="#" class="impa-add-media impa-add-media-img"><img class="impa-term-image-url" src="<?php echo esc_url( $image_url ); ?>" style="max-width: 100%; max-height: 200px; height: auto; display: block;" /></a>
					<a href="#" class="impa-add-media impa-add-media-text"><?php esc_html_e( 'Set term image', 'impress_agents' ); ?></a>
					<a href="#" class="impa-remove-media"><?php esc_html_e( 'Remove term image', 'impress_agents' ); ?></a>
				</p>
				<!-- End term image -->
			</td>
		</tr>
		<?php
	}

}
