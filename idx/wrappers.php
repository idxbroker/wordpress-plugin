<?php
namespace IDX;

/**
 * Wrappers class.
 */
class Wrappers {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new Idx_Api();
		add_action( 'init', array( $this, 'register_wrapper_post_type' ) );
		add_action( 'admin_init', array( $this, 'manage_idx_wrapper_capabilities' ) );
		add_filter( 'default_content', array( $this, 'idx_wrapper_content' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wrapper_styles' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'set_wrapper_page' ) );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Register_wrapper_post_type function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_wrapper_post_type() {
		$labels       = array(
			'name'               => 'Wrappers',
			'singular_name'      => 'Wrapper',
			'add_new'            => 'Add Wrapper',
			'add_new_item'       => 'Add New Wrapper',
			'edit_item'          => 'Edit Wrapper',
			'new_item'           => 'New Wrapper',
			'view_item'          => 'View Wrapper',
			'search_items'       => 'Search Wrappers',
			'not_found'          => 'No Wrappers found',
			'not_found_in_trash' => 'No Wrappers found in Trash',
			'parent_item_colon'  => '',
			'parent'             => 'Parent Wrapper',
		);
		$capabilities = array(
			'publish_posts'       => 'publish_idx_wrappers',
			'edit_posts'          => 'edit_idx_wrappers',
			'edit_others_posts'   => 'edit_others_idx_wrappers',
			'delete_posts'        => 'delete_idx_wrappers',
			'delete_others_posts' => 'delete_others_idx_wrappers',
			'read_private_posts'  => 'read_private_idx_wrappers',
			'edit_post'           => 'edit_idx_wrapper',
			'delete_post'         => 'delete_idx_wrapper',
			'read_post'           => 'read_idx_wrapper',
		);

		$args = array(
			'public'              => true,
			'labels'              => $labels,
			'label'               => 'Wrappers',
			'description'         => 'Custom Posts Created To Match IDX Pages to the Website',
			'exclude_from_search' => true,
			'show_in_menu'        => 'idx-broker',
			'show_in_nav_menus'   => false,
			'capability_type'     => array( 'idx_wrapper', 'idx_wrappers' ),
			'capabilities'        => $capabilities,
			'has_archive'         => false,
			'hierarchical'        => false,
			'rewrite'             => array( 'pages' => false ),
			'supports'            => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'revisions',
				'equity-layouts',
				'equity-cpt-archives-settings',
				'genesis-seo',
				'genesis-layouts',
				'genesis-simple-sidebars',
				'genesis-cpt-archives-settings',
				'publicize',
				'wpcom-markdown',
			),
		);
		register_post_type( 'idx-wrapper', $args );
	}

	/**
	 * Wrapper_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_styles() {
		// Add styles hiding the post title and previous/next links via the stylesheet.
		global $post;
		if ( $post && 'idx-wrapper' === $post->post_type ) {
			wp_enqueue_style( 'idx-wrappers', IMPRESS_IDX_URL . 'assets/css/idx-wrappers.min.css', [], '1.0.0' );
		}
	}

	/**
	 * Manage_idx_wrapper_capabilities function.
	 *
	 * @access public
	 * @return void
	 */
	public function manage_idx_wrapper_capabilities() {
		// gets the role to add capabilities to.
		if ( current_user_can( 'edit_others_posts' ) ) {
			$current_user = wp_get_current_user();
			// replicate all the remapped capabilites from the custom post type.
			$caps = array(
				'publish_idx_wrappers',
				'edit_idx_wrappers',
				'edit_others_idx_wrappers',
				'delete_idx_wrappers',
				'delete_others_idx_wrappers',
				'read_private_idx_wrappers',
				'edit_idx_wrapper',
				'delete_idx_wrapper',
				'read_idx_wrapper',
			);
			// give all the capabilities to the administrator.
			foreach ( $caps as $cap ) {
				$current_user->add_cap( $cap );
			}
		}
	}

	/**
	 * Check if theme includes idxstart and stop tags
	 */
	public function does_theme_include_idx_tag() {
		// default page content
		// the empty div is for any content they add to the visual editor so it displays.
		$post_content = '<div></div><div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div>';
		// get theme to check start/stop tag.
		$does_theme_include_idx_tag = false;
		$template_root              = get_theme_root() . DIRECTORY_SEPARATOR . get_stylesheet();
		$files                      = scandir( $template_root );
		foreach ( $files as $file ) {
			$path = $template_root . DIRECTORY_SEPARATOR . $file;
			if ( is_file( $path ) && preg_match( '/.*\.php/', $file ) ) {
				$content = file_get_contents( $template_root . DIRECTORY_SEPARATOR . $file );
				if ( preg_match( '/<div[^>\n]+?id=[\'"]idxstart[\'"].*?(\/>|><\/div>)/i', $content ) ) {
					if ( preg_match( '/<div[^>\n]+?id=[\'"]idxstop[\'"].*?(\/>|><\/div>)/i', $content ) ) {
						$does_theme_include_idx_tag = true;
						break;
					}
				}
			}
		}
		if ( $does_theme_include_idx_tag || function_exists( 'equity' ) ) {
			$post_content = '';
		}

		return $post_content;
	}

	/**
	 * Idx_wrapper_content function.
	 *
	 * @access public
	 * @param mixed $content - Content.
	 * @param mixed $post - Post.
	 * @return mixed
	 */
	public function idx_wrapper_content( $content, $post ) {
		if ( 'idx-wrapper' === $post->post_type ) {
			$content = $this->does_theme_include_idx_tag();
			return $content;
		}
	}

	/**
	 * Idx_create_dynamic_page function.
	 *
	 * @param string $title Wrapper page title.
	 * @access public
	 * @return WP_Error|null
	 */
	public function idx_create_dynamic_page( $title ) {
		// Default page content.
		$post_content    = $this->does_theme_include_idx_tag();
		$submitted_title = sanitize_text_field( wp_unslash( $title ) );
		$post_title      = $submitted_title ? $submitted_title : 'Properties';

		// Check if already exists.
		$existing_post = get_page_by_title( $post_title, OBJECT, 'idx-wrapper' );
		if ( $existing_post ) {
			return null;
		}

		$new_post = [
			'post_title'   => $post_title,
			'post_name'    => $post_title,
			'post_content' => $post_content,
			'post_type'    => 'idx-wrapper',
			'post_status'  => 'publish',
		];

		$wrapper_page_id = wp_insert_post( $new_post );
		update_option( 'idx_broker_dynamic_wrapper_page_name', $post_title, false );
		update_option( 'idx_broker_dynamic_wrapper_page_id', $wrapper_page_id, false );
		$wrapper_page_url = get_permalink( $wrapper_page_id );
		$error            = $this->idx_api->set_wrapper( 'global', $wrapper_page_url );
		update_post_meta( $wrapper_page_id, 'idx-wrapper-page', 'global' );
		if ( is_wp_error( $error ) ) {
			return $error;
		}
		return null;
	}

	/**
	 * Idx_ajax_delete_dynamic_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_ajax_delete_dynamic_page() {
		// User capability check.
		if ( ! current_user_can( 'delete_others_posts' ) ) {
			wp_die();
		}
		// Validate and process request.
		if ( isset( $_POST['wrapper_page_id'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'idx-settings-wrapper-delete-nonce' ) ) {
			$wrapper_page_id = sanitize_text_field( wp_unslash( $_POST['wrapper_page_id'] ) );
			// Verify retrieved post type is idx-wrapper before deleting.
			if ( get_post_type( $wrapper_page_id ) === 'idx-wrapper' ) {
				wp_delete_post( $wrapper_page_id, true );
				wp_trash_post( $wrapper_page_id );
			}
		}
		wp_die();
	}

	/**
	 * Dynamic wrapper requires the pageID, not the UID, so we must strip out the account number from the UID.
	 *
	 * @param string $uid - UID.
	 * @return string
	 */
	public function convert_uid_to_id( $uid ) {
		return substr( $uid, strpos( $uid, '-' ) + 1 );
	}

	/**
	 * Is_selected function.
	 *
	 * @access public
	 * @param mixed $value - Selected value.
	 * @return mixed
	 */
	public function is_selected( $value ) {
		$post_id            = get_the_ID();
		$saved_wrapper_page = get_post_meta( $post_id, 'idx-wrapper-page', true );
		if ( ! empty( $saved_wrapper_page ) ) {
			$saved_wrapper_page = get_post_meta( $post_id, 'idx-wrapper-page', true );
		}
		if ( ! empty( $value ) && $value === $saved_wrapper_page ) {
			return 'selected';
		}
	}

	/**
	 * Wrapper_page_dropdown function.
	 *
	 * @access public
	 * @param mixed $system_links - System Links.
	 * @param mixed $saved_links - Saved Links.
	 * @return void
	 */
	public function wrapper_page_dropdown( $system_links, $saved_links ) {
		echo '<select class="idx-wrapper-page" name="idx-wrapper-page" style="width: 100%;">';
		echo '<option value="none"' . esc_attr( $this->is_selected( 'none' ) ) . '>None</option>';
		echo '<option value="global"' . esc_attr( $this->is_selected( 'global' ) ) . '>Globally</option>';
		foreach ( $system_links as $system_link ) {
			$uid  = $system_link->uid;
			$name = $system_link->name;
			$id   = $this->convert_uid_to_id( $uid );
			echo '<option value="' . esc_attr( $id ) . '" ' . esc_attr( $this->is_selected( $id ) ) . '>' . esc_html( $name ) . '</option>';
		}
		if ( $this->idx_api->platinum_account_type() ) {
			foreach ( $saved_links as $saved_link ) {
				$id   = $saved_link->id;
				$name = $saved_link->linkTitle;
				echo '<option value="' . esc_attr( $id ) . '" ' . esc_attr( $this->is_selected( $id ) ) . '>' . esc_html( $name ) . '</option>';
			}
		}

		echo '</select>';
	}

	/**
	 * Wrapper_page_ui function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_page_ui() {
		// add metabox interface when editing a wrapper page (with none and global options)
		// This UI should display the current page set
		// when saving a post, save the meta of which page is set.
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		wp_nonce_field( 'idx-wrapper-page', 'idx-wrapper-page-nonce' );
		$this->wrapper_page_dropdown( $system_links, $saved_links );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'idx-wrapper', plugins_url( '../assets/js/idx-wrappers.min.js', __FILE__ ), [], '1.0', false );

	}

	/**
	 * Add_meta_box function.
	 *
	 * @access public
	 * @param mixed $post_type - Post type.
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array( 'idx-wrapper' ); // limit meta box to certain post types.
		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'set_wrapper_page',
				'Apply Wrapper to IDX Pages',
				array( $this, 'wrapper_page_ui' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * Set_wrapper_page function.
	 *
	 * @access public
	 * @param mixed $post_id - Post ID.
	 * @return mixed
	 */
	public function set_wrapper_page( $post_id ) {
		// saved idx page ID.
		if ( empty( $_POST ) || ! current_user_can( 'publish_posts' ) || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST['idx-wrapper-page-nonce'] ) ) {
			return $post_id;
		}

		// Validate and process request.
		if ( isset( $_POST['idx-wrapper-page-nonce'], $_POST['idx-wrapper-page'] ) || wp_verify_nonce( sanitize_key( $_POST['idx-wrapper-page-nonce'] ), 'idx-wrapper-page' ) ) {
			// meta_value is the IDX Broker page ID (or 'none' or 'global') that should have its wrapper updated
			$meta_value = sanitize_text_field( wp_unslash( $_POST['idx-wrapper-page'] ) );
			// If 'none' was selected on the post, don't update anything
			if ( 'none' === $meta_value ) {
				return;
			} elseif ( 'global' === $meta_value ) {
				$this->idx_api->set_wrapper( $idx_page_id, '' );
			}
			// Get the edited wrapper's URL to set the wrapper with
			$wrapper_page_url = get_permalink();
			$this->idx_api->set_wrapper( $meta_value, $wrapper_page_url );
			update_post_meta( $post_id, 'idx-wrapper-page', $meta_value );
		}
	}

	/**
	 * Find_idx_url function.
	 *
	 * @access public
	 * @param mixed $post_id - Post ID.
	 * @return mixed
	 */
	public function find_idx_url( $post_id ) {
		$post         = get_post( $post_id );
		$url          = $post->post_name;
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		foreach ( $system_links as $link ) {
			if ( $link->url === $url ) {
				$uid  = $link->uid;
				$name = $link->name;
				$id   = $this->convert_uid_to_id( $uid );
				return $id;
			}
		}
		foreach ( $saved_links as $link ) {
			if ( $link->url === $url ) {
				$id   = $link->id;
				$name = $link->linkTitle;
				return $id;
			}
		}
	}
}
