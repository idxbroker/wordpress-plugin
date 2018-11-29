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
		add_action( 'wp_ajax_create_dynamic_page', array( $this, 'idx_ajax_create_dynamic_page' ) );
		add_action( 'wp_ajax_delete_dynamic_page', array( $this, 'idx_ajax_delete_dynamic_page' ) );
		add_action( 'init', array( $this, 'register_wrapper_post_type' ) );
		add_action( 'admin_init', array( $this, 'manage_idx_wrapper_capabilities' ) );
		add_filter( 'default_content', array( $this, 'idx_wrapper_content' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wrapper_styles' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'set_wrapper_page' ) );
	}

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * register_wrapper_post_type function.
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
	 * wrapper_styles function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_styles() {
		// Add styles hiding the post title and previous/next links via the stylesheet
		global $post;
		if ( $post && $post->post_type === 'idx-wrapper' ) {
			wp_enqueue_style(
				'idx-wrappers',
				plugins_url(
					'../assets/css/idx-wrappers.css',
					__FILE__
				)
			);
		}
	}

	/**
	 * manage_idx_wrapper_capabilities function.
	 *
	 * @access public
	 * @return void
	 */
	public function manage_idx_wrapper_capabilities() {
		// gets the role to add capabilities to
		if ( current_user_can( 'edit_others_posts' ) ) {
			$current_user = wp_get_current_user();
			// replicate all the remapped capabilites from the custom post type
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
			// give all the capabilities to the administrator
			foreach ( $caps as $cap ) {
				$current_user->add_cap( $cap );
			}
		}
	}

	// check if theme includes idxstart and stop tags
	public function does_theme_include_idx_tag() {
		// default page content
		// the empty div is for any content they add to the visual editor so it displays
		$post_content = '<div></div><div id="idxStart" style="display: none;"></div><div id="idxStop" style="display: none;"></div>';
		// get theme to check start/stop tag
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
	 * idx_wrapper_content function.
	 *
	 * @access public
	 * @param mixed $content
	 * @param mixed $post
	 * @return void
	 */
	public function idx_wrapper_content( $content, $post ) {
		if ( $post->post_type === 'idx-wrapper' ) {
			$content = $this->does_theme_include_idx_tag();
			return $content;
		}
	}

	/**
	 * idx_ajax_create_dynamic_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_ajax_create_dynamic_page() {

		// default page content
		$post_content = $this->does_theme_include_idx_tag();

		$post_title = $_POST['post_title'] ? $_POST['post_title'] : 'Properties';
		$new_post   = array(
			'post_title'   => $post_title,
			'post_name'    => $post_title,
			'post_content' => $post_content,
			'post_type'    => 'idx-wrapper',
			'post_status'  => 'publish',
		);
		if ( $_POST['wrapper_page_id'] ) {
			$new_post['ID'] = $_POST['wrapper_page_id'];
		}
		$wrapper_page_id = wp_insert_post( $new_post );
		update_option( 'idx_broker_dynamic_wrapper_page_name', $post_title, false );
		update_option( 'idx_broker_dynamic_wrapper_page_id', $wrapper_page_id, false );
		$wrapper_page_url = get_permalink( $wrapper_page_id );
		$this->idx_api->set_wrapper( 'global', $wrapper_page_url );
		update_post_meta( $wrapper_page_id, 'idx-wrapper-page', 'global' );

		die(
			json_encode(
				array(
					'wrapper_page_id'   => $wrapper_page_id,
					'wrapper_page_name' => $post_title,
				)
			)
		);
	}

	/**
	 * idx_ajax_delete_dynamic_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function idx_ajax_delete_dynamic_page() {
		if ( $_POST['wrapper_page_id'] ) {
			wp_delete_post( $_POST['wrapper_page_id'], true );
			wp_trash_post( $_POST['wrapper_page_id'] );
		}
		die();
	}

	// dynamic wrapper requires the pageID, not the UID, so we must strip out the account number from the UID
	public function convert_uid_to_id( $uid ) {
		return substr( $uid, strpos( $uid, '-' ) + 1 );
	}

	/**
	 * is_selected function.
	 *
	 * @access public
	 * @param mixed $value
	 * @return void
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
	 * wrapper_page_dropdown function.
	 *
	 * @access public
	 * @param mixed $system_links
	 * @param mixed $saved_links
	 * @return void
	 */
	public function wrapper_page_dropdown( $system_links, $saved_links ) {
		echo '<select class="idx-wrapper-page" name="idx-wrapper-page" style="width: 100%;">';
		echo "<option value=\"none\" {$this->is_selected('none')}>None</option>";
		echo "<option value=\"global\" {$this->is_selected('global')}>Globally</option>";
		foreach ( $system_links as $system_link ) {
			$uid  = $system_link->uid;
			$name = $system_link->name;
			$id   = $this->convert_uid_to_id( $uid );
			echo "<option value=\"$id\" {$this->is_selected($id)}>$name</option>";
		}
		if ( $this->idx_api->platinum_account_type() ) {
			foreach ( $saved_links as $saved_link ) {
				$id   = $saved_link->id;
				$name = $saved_link->linkTitle;
				echo "<option value=\"$id\" {$this->is_selected($id)}>$name</option>";
			}
		}

		echo '</select>';
	}

	/**
	 * wrapper_page_ui function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_page_ui() {
		// add metabox interface when editing a wrapper page (with none and global options)
		// This UI should display the current page set
		// when saving a post, save the meta of which page is set
		$system_links = $this->idx_api->idx_api_get_systemlinks();
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		wp_nonce_field( 'idx-wrapper-page', 'idx-wrapper-page-nonce' );
		$this->wrapper_page_dropdown( $system_links, $saved_links );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), '4.0.5', 'all'  );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array( 'jquery' ), '4.0.5', true );
		wp_enqueue_script( 'idx-wrapper', plugins_url( '../assets/js/idx-wrappers.min.js', __FILE__ ) );

	}

	/**
	 * add_meta_box function.
	 *
	 * @access public
	 * @param mixed $post_type
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array( 'idx-wrapper' ); // limit meta box to certain post types
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
	 * set_wrapper_page function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	public function set_wrapper_page( $post_id ) {
		$post_id          = get_the_ID();
		$wrapper_page_url = get_permalink( $post_id );
		// saved idx page ID
		if ( empty( $_POST ) ) {
			return;
		}
		if ( empty( $_POST['idx-wrapper-page'] ) ) {
			return;
		}
		$meta_value = $_POST['idx-wrapper-page'];
		$meta_value = sanitize_text_field( $meta_value );
		if ( ! $this->verify_permissions() ) {
			return $post_id;
		}

		// logic for what type of idx page is in Idx_Api class
		$this->idx_api->set_wrapper( $meta_value, $wrapper_page_url );
		update_post_meta( $post_id, 'idx-wrapper-page', $meta_value );
	}

	/**
	 * verify_permissions function.
	 *
	 * @access public
	 * @return void
	 */
	public function verify_permissions() {
		// Check if our nonce is set.
		if ( ! isset( $_POST['idx-wrapper-page-nonce'] ) ) {
			return false;
		}
		$nonce = $_POST['idx-wrapper-page-nonce'];
		if ( ! wp_verify_nonce( $nonce, 'idx-wrapper-page' ) ) {
			return false;
		}
		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		return true;
	}
}
