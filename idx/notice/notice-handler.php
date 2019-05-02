<?php

namespace IDX\Notice;

/** Has a bulk add notice method and holds all notice conditions.
 *
 * Currently supports detection and creation of specific plugins notices:
 *   Yoast,
 *
 * @since 2.5.10
 */
class Notice_Handler {
	/**
	 * Begin construct function
	 * We don't want anyone instantiating this class
	 *
	 * @since 2.5.10
	 */
	private function __construct() {}

	/**
	 * Begin get_all_notices function
	 * Returns array of all non-dismissed notices
	 *
	 * @since 2.5.10
	 * @return array of notices.
	 */
	public static function get_all_notices() {
		$notices = [];

		// Always use the name returned from the notice function to avoid mistyping.
		$name = self::yoast();
		if ( $name ) {
			$notices[] = new Notice(
				$name,
				__( 'Yoast is not allowing your pages to be indexed!', 'idxbroker' ),
				'error',
				'https://support.idxbroker.com/customer/portal/articles/2925410-fix-no-index',
				__( 'How can I fix this?', 'idxbroker' )
			);
		}

		if ( count( $notices ) > 0 ) {
			add_action( 'admin_enqueue_scripts', [ '\IDX\Notice\Notice_Handler', 'notice_script_styles' ] );
		}

		return $notices;
	}

	/**
	 * Begin dismissed function
	 * Function called via ajax to dismiss the notice
	 *
	 * @since 2.5.10
	 */
	public static function dismissed() {
		check_ajax_referer( 'idx-notice-nonce' );
		$post = filter_input_array( INPUT_POST );
		if ( isset( $post['name'] ) && '' !== $post['name'] ) {
			$name = $post['name'];
			update_option( "idx-notice-dismissed-$name", true );
		}
		wp_die();
	}

	/**
	 * Begin yoast function
	 * Checks if Yoast is noindexing our custom page types
	 * Returns the unique notice name if notice needs to be displayed, false otherwise
	 *
	 * @since 2.5.10
	 * @return Boolean value
	 */
	private static function yoast() {
		$name = 'yoast';
		// If Yoast not active, return.
		if ( is_plugin_inactive( 'wordpress-seo/wp-seo.php' ) ) {
			// Deletes the notice option is the notice now that the problem is resolved.
			// The user should be notified if they dismissed an option, fixed the problem,
			// then the problem arises later.
			self::delete_dismissed_option( $name );
			return false;
		}

		// Yoast stores their noindex flag for page types in wpseo_titles.
		$data = get_option( 'wpseo_titles' );

		$wrapper_no_index   = (boolean) $data['noindex-idx-wrapper'];
		$idx_pages_no_index = (boolean) $data['noindex-idx_page'];

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
	/**
	 * Begin delete_dismissed_option function
	 * $name should only be passed in from the returned notice functions
	 *
	 * @since 2.5.10
	 * @param mixed $name contains the name of the notice.
	 */
	private static function delete_dismissed_option( $name ) {
		delete_option( "idx-notice-dismissed-$name" );
	}

	/**
	 * Begin is_dismissed function
	 * Checks wp_options if a specific notice has been dismissed
	 *
	 * @since 2.5.10
	 * @param mixed $name is the name of the notice.
	 * @return option
	 */
	private static function is_dismissed( $name ) {
		return get_option( "idx-notice-dismissed-$name" );
	}

	/**
	 * Adds scripts and localizes AJAX variables
	 *
	 * @since 2.5.10
	 */
	public static function notice_script_styles() {
		$ajax_nonce = wp_create_nonce( 'idx-notice-nonce' );
		wp_register_script( 'idx-notice', IMPRESS_IDX_URL . '/assets/js/idx-notice.min.js', 'jquery', false, true );
		wp_localize_script( 'idx-notice', 'idxNoticeNonce', $ajax_nonce );
		wp_enqueue_script( 'idx-notice' );
	}
}
