<?php
namespace IDX;

/**
 * Register_Blocks class.
 */
class Register_Blocks {

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Lead_login_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $lead_login_shortcode;

	/**
	 * Lead_signup_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $lead_signup_shortcode;

	/**
	 * Omnibar_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $omnibar_shortcode;

	/**
	 * Impress_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $impress_shortcodes;

	/**
	 * Idx_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_shortcodes;

	/**
	 * Block_dependencies
	 *
	 * @var array
	 * @access public
	 */
	public $block_dependencies;

	/**
	 * __construct
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api            = new Idx_Api();
		$this->impress_shortcodes = new \IDX\Shortcodes\Register_Impress_Shortcodes();
		$this->idx_shortcodes     = new \IDX\Shortcodes\Register_Idx_Shortcodes();
		$this->omnibar_shortcode  = new \IDX\Widgets\Omnibar\IDX_Omnibar_Widget();

		// Register block category.
		global $wp_version;
		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			add_filter( 'block_categories_all', [ $this, 'register_idx_category' ], 10, 2 );
		} else {
			add_filter( 'block_categories', [ $this, 'register_idx_category' ], 10, 2 );
		}

		// Set dependencies used by blocks.
		add_action( 'enqueue_block_editor_assets', [ $this, 'set_block_dependencies' ] );

		// Editor CSS file shared across blocks.
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_shared_css' ] );

		// IDX Widgets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'idx_widgets_block_register_script' ] );
		add_action( 'init', [ $this, 'idx_widgets_block_init' ] );

		// IDX Wrapper Tags.
		add_action( 'enqueue_block_editor_assets', [ $this, 'idx_wrapper_tags_block_register_script' ] );
		add_action( 'init', [ $this, 'idx_wrapper_tags_block_init' ] );

		// IMPress Lead Signup Block.
		if ( $this->idx_api->platinum_account_type() ) {
			$this->lead_signup_shortcode = new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode();
			add_action( 'enqueue_block_editor_assets', [ $this, 'impress_lead_signup_block_register_script' ] );
			add_action( 'init', [ $this, 'impress_lead_signup_block_init' ] );
		}

		// IMPress Lead Login Block.
		add_action( 'enqueue_block_editor_assets', [ $this, 'impress_lead_login_block_register_script' ] );
		add_action( 'init', [ $this, 'impress_lead_login_block_init' ] );

		// IMPress Omnibar Block.
		add_action( 'enqueue_block_editor_assets', [ $this, 'impress_omnibar_block_register_script' ] );
		add_action( 'init', [ $this, 'impress_omnibar_block_init' ] );

		// IMPress Carousel.
		add_action( 'enqueue_block_editor_assets', [ $this, 'impress_carousel_block_register_script' ] );
		add_action( 'init', [ $this, 'impress_carousel_block_init' ] );

		// IMPress Showcase.
		add_action( 'enqueue_block_editor_assets', [ $this, 'impress_showcase_block_register_script' ] );
		add_action( 'init', [ $this, 'impress_showcase_block_init' ] );

		// IMPress City Links.
		add_action( 'enqueue_block_editor_assets', [ $this, 'impress_city_links_block_register_script' ] );
		add_action( 'init', [ $this, 'impress_city_links_block_init' ] );

	}

	/**
	 * Set_Block_Dependencies
	 *
	 * @access public
	 * @return mixed
	 */
	public function set_block_dependencies() {
		global $wp_version;
		$current_screen = get_current_screen();
		if ( version_compare( $wp_version, '5.8', '>=' ) && ( 'widgets' === $current_screen->id || 'customize' === $current_screen->id ) ) {
			$this->block_dependencies = [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-server-side-render', 'wp-edit-widgets' ];
		} else {
			$this->block_dependencies = [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ];
		}
	}

	/**
	 * Register_Idx_Category
	 *
	 * @access public
	 * @param array $categories - Array of existing categories.
	 * @return mixed
	 */
	public function register_idx_category( $categories ) {
		return array_merge(
			$categories,
			[
				[
					'slug'  => 'idx-category',
					'title' => __( 'IMPress for IDX Broker', 'idx-broker-platinum' ),
				],
			]
		);
	}

	/**
	 * Register_Idx_Category
	 *
	 * @access public
	 * @return mixed
	 */
	public function register_block_shared_css() {
		wp_enqueue_style( 'idx-shared-block-editor-css', plugins_url( '../assets/css/idx-block-edit.min.css', __FILE__ ), false, '1.0', 'all' );
		wp_enqueue_style( 'font-awesome-5.8.2' );
	}

	// Block Registration/Setup Functions.

	// IDX Widgets Block Setup.

	/**
	 * Idx_widgets_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function idx_widgets_block_register_script() {
		// Register block script.
		wp_register_script(
			'idx-widgets-block',
			plugins_url( '../assets/js/idx-widgets-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$placeholder_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/idx-widgets-placeholder.png';
		wp_localize_script( 'idx-widgets-block', 'idx_widget_block_image_url', [ $placeholder_image_url ] );

		$available_widgets = $this->get_widget_list_options();
		wp_localize_script( 'idx-widgets-block', 'idx_widgets_list', $available_widgets );

		wp_enqueue_script( 'idx-widgets-block' );
	}

	/**
	 * Impress_lead_signup_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function idx_widgets_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/idx-widgets-block',
			[
				'attributes' => [
					'id' => [
						'type' => 'string',
					],
				],
				'editor_script'   => 'idx-widgets-block',
				'render_callback' => [ $this, 'idx_widgets_block_render' ],
			]
		);
	}

	/**
	 * Idx_widgets_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function idx_widgets_block_render( $attributes ) {
		return $this->idx_shortcodes->show_widget( $attributes );
	}


	// IDX Wrapper Block Setup.

	/**
	 * Idx_wrapper_tags_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function idx_wrapper_tags_block_register_script() {
		// Hide from Customizer and Appearance > Widgets pages as this should only be used on a page/post.
		$current_screen = get_current_screen();
		if ( 'widgets' === $current_screen->id || 'customize' === $current_screen->id ) {
			return;
		}

		// Register block script.
		wp_register_script(
			'idx-wrapper-tags-block',
			plugins_url( '../assets/js/idx-wrapper-tags-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$idx_wrapper_tags_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/wrapper-tag-placeholder.png';
		wp_localize_script( 'idx-wrapper-tags-block', 'idx_wrapper_tags_image_url', [ $idx_wrapper_tags_image_url ] );
		wp_enqueue_script( 'idx-wrapper-tags-block' );
	}

	/**
	 * Idx_wrapper_tags_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function idx_wrapper_tags_block_init() {
		// Register block.
		register_block_type(
			'idx-broker-platinum/idx-wrapper-tags-block',
			[
				'editor_script' => 'idx-wrapper-tags-block',
			]
		);
	}


	// IMPress Lead Signup Block Setup.

	/**
	 * Impress_lead_signup_block_register_script.
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_signup_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-lead-signup-block',
			plugins_url( '../assets/js/impress-lead-signup-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$available_agents = $this->get_agents_select_list();
		wp_localize_script( 'impress-lead-signup-block', 'lead_signup_agent_list', $available_agents );

		$lead_signup_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/lead-signup-placeholder.png';
		wp_localize_script( 'impress-lead-signup-block', 'lead_signup_image_url', [ $lead_signup_image_url ] );

		wp_enqueue_script( 'impress-lead-signup-block' );
	}

	/**
	 * Impress_lead_signup_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_signup_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-lead-signup-block',
			[
				'attributes' => [
					'phone' => [
						'type' => 'integer',
					],
					'styles' => [
						'type' => 'integer',
					],
					'new_window' => [
						'type' => 'integer',
					],
					'agent_id' => [
						'type' => 'string',
					],
					'password_field' => [
						'type' => 'boolean',
					],
					'button_text' => [
						'type' => 'string',
					],
				],
				'editor_script'   => 'impress-lead-signup-block',
				'render_callback' => [ $this, 'impress_lead_signup_block_render' ],
			]
		);
	}

	/**
	 * Impress_lead_signup_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_lead_signup_block_render( $attributes ) {
		return $this->lead_signup_shortcode->shortcode_output( $attributes );
	}


	// IMPress Lead Login Block Setup.

	/**
	 * Impress_lead_login_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_login_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-lead-login-block',
			plugins_url( '../assets/js/impress-lead-login-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$lead_login_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/lead-login-placeholder.png';
		wp_localize_script( 'impress-lead-login-block', 'lead_login_image_url', [ $lead_login_image_url ] );
		wp_enqueue_script( 'impress-lead-login-block' );
	}

	/**
	 * Impress_lead_login_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_login_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-lead-login-block',
			[
				'attributes' => [
					'styles' => [
						'type' => 'integer',
					],
					'new_window' => [
						'type' => 'integer',
					],
					'password_field' => [
						'type' => 'boolean',
					],
				],
				'editor_script'   => 'impress-lead-login-block',
				'render_callback' => [ $this, 'impress_lead_login_block_render' ],
			]
		);
	}

	/**
	 * Impress_lead_login_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_lead_login_block_render( $attributes ) {
		return $this->impress_shortcodes->lead_login_shortcode( $attributes );
	}


	// IMPress Omnibar Block Setup.

	/**
	 * Impress_omnibar_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function impress_omnibar_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-omnibar-block',
			plugins_url( '../assets/js/impress-omnibar-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$impress_omnibar_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/omnibar-placeholder.png';
		wp_localize_script( 'impress-omnibar-block', 'impress_omnibar_image_url', [ $impress_omnibar_image_url ] );
		wp_enqueue_script( 'impress-omnibar-block' );
	}

	/**
	 * Impress_omnibar_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_omnibar_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-omnibar-block',
			[
				'attributes' => [
					'styles' => [
						'type' => 'integer',
					],
					'extra' => [
						'type' => 'integer',
					],
					'min_price' => [
						'type' => 'integer',
					],
				],
				'editor_script'   => 'impress-omnibar-block',
				'render_callback' => [ $this, 'impress_omnibar_block_render' ],
			]
		);
	}

	/**
	 * Impress_omnibar_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_omnibar_block_render( $attributes ) {
		return $this->omnibar_shortcode->create_omnibar->add_omnibar_shortcode( $attributes );
	}


	// Impress Carousel Block Setup.

	/**
	 * Impress_carousel_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function impress_carousel_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-carousel-block',
			plugins_url( '../assets/js/impress-carousel-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$available_agents = $this->get_agents_select_list();
		wp_localize_script( 'impress-carousel-block', 'impress_carousel_agent_list', $available_agents );

		$saved_links_list = $this->get_saved_links_list();
		wp_localize_script( 'impress-carousel-block', 'impress_carousel_saved_links', $saved_links_list );

		$impress_carousel_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/carousel-placeholder.png';
		wp_localize_script( 'impress-carousel-block', 'impress_carousel_image_url', [ $impress_carousel_image_url ] );

		wp_enqueue_script( 'impress-carousel-block' );
	}

	/**
	 * Impress_carousel_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_carousel_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-carousel-block',
			[
				'attributes' => [
					'max' => [
						'type' => 'integer',
					],
					'display' => [
						'type' => 'integer',
					],
					'autoplay' => [
						'type' => 'integer',
					],
					'order' => [
						'type' => 'string',
					],
					'property_type' => [
						'type' => 'string',
					],
					'styles' => [
						'type' => 'integer',
					],
					'new_window' => [
						'type' => 'integer',
					],
					'saved_link_id' => [
						'type' => 'string',
					],
					'agent_id' => [
						'type' => 'string',
					],
					'colistings' => [
						'type' => 'integer',
					],
				],
				'editor_script'   => 'impress-carousel-block',
				'render_callback' => [ $this, 'impress_carousel_block_render' ],
			]
		);
	}

	/**
	 * Impress_carousel_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_carousel_block_render( $attributes ) {
		return $this->impress_shortcodes->property_carousel_shortcode( $attributes );
	}


	// IMPress Showcase Block Setup.

	/**
	 * Impress_showcase_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function impress_showcase_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-showcase-block',
			plugins_url( '../assets/js/impress-showcase-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$available_agents = $this->get_agents_select_list();
		wp_localize_script( 'impress-showcase-block', 'impress_showcase_agent_list', $available_agents );

		$saved_links_list = $this->get_saved_links_list();
		wp_localize_script( 'impress-showcase-block', 'impress_showcase_saved_links', $saved_links_list );

		$impress_showcase_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/showcase-placeholder.png';
		wp_localize_script( 'impress-showcase-block', 'impress_showcase_image_url', [ $impress_showcase_image_url ] );

		wp_enqueue_script( 'impress-showcase-block' );
	}

	/**
	 * Impress_showcase_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_showcase_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-showcase-block',
			[
				'attributes' => [
					'max' => [
						'type' => 'integer',
					],
					'use_rows' => [
						'type' => 'integer',
					],
					'num_per_row' => [
						'type' => 'integer',
					],
					'show_image' => [
						'type' => 'integer',
					],
					'order' => [
						'type' => 'string',
					],
					'property_type' => [
						'type' => 'string',
					],
					'saved_link_id' => [
						'type' => 'string',
					],
					'agent_id' => [
						'type' => 'string',
					],
					'colistings' => [
						'type' => 'integer',
					],
					'styles' => [
						'type' => 'integer',
					],
					'new_window' => [
						'type' => 'integer',
					],
				],
				'editor_script'   => 'impress-showcase-block',
				'render_callback' => [ $this, 'impress_showcase_block_render' ],
			]
		);
	}

	/**
	 * Impress_showcase_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_showcase_block_render( $attributes ) {
		return $this->impress_shortcodes->property_showcase_shortcode( $attributes );
	}


	// IMPress City Links Block Setup.

	/**
	 * Impress_city_links_block_register_script
	 *
	 * @access public
	 * @return void
	 */
	public function impress_city_links_block_register_script() {
		// Register block script.
		wp_register_script(
			'impress-city-links-block',
			plugins_url( '../assets/js/impress-city-links-block.min.js', __FILE__ ),
			$this->block_dependencies,
			'1.0',
			false
		);

		$placeholder_image_url = IMPRESS_IDX_URL . 'assets/images/block-placeholder-images/impress-city-links.png';
		wp_localize_script( 'impress-city-links-block', 'impress_city_links_block_image_url', [ $placeholder_image_url ] );

		$mls_options = $this->get_mls_options();
		wp_localize_script( 'impress-city-links-block', 'impress_city_links_mls_options', $mls_options );

		$city_list_options = $this->get_city_list_options();
		wp_localize_script( 'impress-city-links-block', 'impress_city_links_city_options', $city_list_options );

		wp_enqueue_script( 'impress-city-links-block' );
	}

	/**
	 * Impress_city_links_block_init
	 *
	 * @access public
	 * @return void
	 */
	public function impress_city_links_block_init() {
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-city-links-block',
			[
				'attributes' => [
					'mls' => [
						'type' => 'string',
					],
					'city_list' => [
						'type' => 'string',
					],
					'use_columns' => [
						'type' => 'integer',
					],
					'number_columns' => [
						'type' => 'integer',
					],
					'styles' => [
						'type' => 'integer',
					],
					'show_count' => [
						'type' => 'integer',
					],
					'new_window' => [
						'type' => 'integer',
					],
				],
				'editor_script'   => 'impress-city-links-block',
				'render_callback' => [ $this, 'impress_city_links_block_render' ],
			]
		);
	}

	/**
	 * Impress_city_link_block_render
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_city_links_block_render( $attributes ) {
		return $this->impress_shortcodes->city_links_shortcode( $attributes );
	}


	// Block Setup Helper Functions.

	/**
	 * Get_saved_links_list
	 *
	 * @access public
	 * @return array
	 */
	public function get_saved_links_list() {
		$saved_links_list = [
			[
				'label' => '-',
				'value' => '0',
			],
		];

		// Check for API key before making call.
		if ( get_option( 'idx_broker_apikey' ) ) {
			$saved_links = $this->idx_api->idx_api_get_savedlinks();
			// Check for error in returned API response.
			if ( ! is_wp_error( $saved_links ) ) {
				foreach ( $saved_links as $saved_link ) {
					$link_label = empty( $saved_link->linkTitle ) ? $saved_link->linkName : $saved_link->linkTitle;
					array_push( $saved_links_list, ['label' => $link_label, 'value' => $saved_link->id] );
				}
			}
		}

		if ( ! is_array( $saved_links ) ) {
			return [];
		}

		return $saved_links_list;
	}

	/**
	 * Get_agents_select_list
	 *
	 * @access public
	 * @return array
	 */
	public function get_agents_select_list() {
		$agents_list = [
			[
				'label' => 'All',
				'value' => '',
			],
		];

		if ( get_option( 'idx_broker_apikey' ) ) {
			$agent_api_data = $this->idx_api->idx_api( 'agents', IDX_API_DEFAULT_VERSION, 'clients', array(), 7200, 'GET', true );
			if ( ! is_wp_error( $agent_api_data ) && ! empty( $agent_api_data['agent'] ) ) {
				foreach ( $agent_api_data['agent'] as $current_agent ) {
					array_push( $agents_list, [ 'label' => $current_agent['agentDisplayName'], 'value' => $current_agent['agentID'] ] );
				}
			}
		}

		if ( ! is_array( $agents_list ) ) {
			return [
				[
					'label' => 'All',
					'value' => '',
				],
			];
		}

		return $agents_list;
	}

	/**
	 * Get_city_list_options
	 *
	 * @access public
	 * @return array
	 */
	public function get_city_list_options( ) {
		$lists  = $this->idx_api->city_list_names();
		$impress_city_lists = [
			[
				'label' => '-',
				'value' => 'a000',
			],
		];

		if ( ! is_array( $lists ) ) {
			return;
		}

		foreach ( $lists as $list ) {
			// display the list id if no list name has been assigned.
			$list_text = empty( $list->name ) ? $list->id : $list->name;
			array_push( $impress_city_lists, [ 'label' => $list_text, 'value' => $list->id ] );
		}
		return $impress_city_lists;
	}

	/**
	 * Get_mls_options
	 *
	 * @access public
	 * @return array
	 */
	public function get_mls_options() {
		$approved_mls = $this->idx_api->approved_mls();
		$mls_list     = [
			[
				'label' => '-',
				'value' => 'a000',
			],
		];

		if ( ! is_array( $approved_mls ) ) {
			return;
		}
		foreach ( $approved_mls as $mls ) {
			array_push( $mls_list, [ 'label' => $mls->name, 'value' => $mls->id ] );
		}
		return $mls_list;
	}

	/**
	 * Get_widget_list_options
	 *
	 * @access public
	 * @return array
	 */
	public function get_widget_list_options() {
		$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
		$widget_list = [
			[
				'label' => '-',
				'value' => '0',
			],
		];

		if ( $idx_widgets && ! is_wp_error( $idx_widgets ) ) {
			foreach ( $idx_widgets as $widget ) {
				array_push(
					$widget_list,
					[
						'label' => $widget->name,
						'value' => $widget->uid,
					]
				);
			}
		}
		return $widget_list;
	}
}
