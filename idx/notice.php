<?php

namespace IDX;

/* Allows creation of admin notices that are permanently dismissable.
 *
 * Currently supports detection and creation of specific plugins notices:
 *   Yoast,
 */
class Notice {
	function __construct( $handle, $message, $type, $url = '', $link_text = 'info' ) {
		$this->name      = $handle;
		$this->message   = $message;
		$this->type      = $type;
		$this->url       = $url;
		$this->link_text = $link_text;
	}

	// Returns array of all non-dismissed notices
	public static function get_all_notices() {
		$notices = [];

		// Always use the name returned from the notice function to avoid mistyping
		$name = Notice::yoast();
		if ( $name ) {
			$notices[] = new Notice(
				$name,
				__( 'Yoast is not allowing your pages to be indexed!', 'idxbroker' ),
				'error',
				'https://support.idxbroker.com/?/Knowledgebase',
				__( 'How can I fix this?', 'idxbroker' )
			);
		}

		return $notices;
	}

	// Checks if Yoast is noindexing our custom page types
	//
	// Returns the unique notice name if notice needs to be displayed, false otherwise
	private static function yoast() {
		$name = 'yoast';
		// If Yoast not active, return
		if ( is_plugin_inactive( 'wordpress-seo/wp-seo.php' ) ) {
			// Deletes the notice option is the notice now that the problem is resolved.
			// The user should be notified if they dismissed an option, fixed the problem,
			// then the problem arises later.
			self::delete_dismissed_option( $name );
			return false;
		}

		// Yoast stores the page type visibility option in their xml sitemap option table
		$data = get_option( 'wpseo_xml' );

		$wrapper_no_index   = (boolean) $data['post_types-idx-wrapper-not_in_sitemap'];
		$idx_pages_no_index = (boolean) $data['post_types-idx_page-not_in_sitemap'];

		if ( $wrapper_no_index || $idx_pages_no_index ) {
			if ( self::is_dismissed( $name ) ) {
				return false;
			}
			return $name;
		}
		self::delete_dismissed_option( $name );
		return false;
	}

	// $name should only be passed in from the returned notice functions
	private function delete_dismissed_option( $name ) {
		delete_option( "idx-notice-dismissed-$name" );
	}

	// Checks wp_options if a specific notice has been dismissed
	private static function is_dismissed( $name ) {
		return get_option( "idx-notice-dismissed-$name" );
	}

	// Creates notice if in the IMPress menu and is not dismissed
	public function create_notice() {
		$current_page = get_current_screen();
		if ( 'idx-broker' !== $current_page->parent_file ) {
			return;
		}
		if ( $this->dismissed ) {
			return;
		}
		\IDX\Views\Notice::create_notice( $this->name, $this->message, $this->type, $this->url, $this->link_text );
	}

	// Function called via ajax to dismiss the notice
	public static function dismissed() {
		check_ajax_referer( 'idx-notice-nonce' );
		$post = filter_input_array( INPUT_POST );
		if ( isset( $post['name'] ) && '' !== $post['name'] ) {
			$name = $post['name'];
			update_option( "idx-notice-dismissed-$name", true );
		}
		wp_die();
	}
}
