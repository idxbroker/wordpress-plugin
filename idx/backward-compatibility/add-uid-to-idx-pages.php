<?php
/**
 * UID to IDX Pages.
 *
 * @package idxbroker-platinum
 */
namespace IDX\Backward_Compatibility;

defined( 'ABSPATH' ) || exit;


/**
 * Add_Uid_To_Idx_Pages class.
 */
class Add_Uid_To_Idx_Pages {

	/**
	 * IDX API.
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		// If Migrate_Old_Table has not run, wait for it.
		$migrated = get_option( 'idx_migrated_old_table' );
		if ( empty( $migrated ) ) {
			return;
		}

		// Run Scheduled Addng of UID.
		add_action( 'idx_add_uid_to_idx_pages', array( $this, 'add_uid' ) );

	}

	/**
	 * Add UID to all IDX pages as the unique identifier.
	 *
	 * @access public
	 * @return Updated Option idx_added_uid_to_idx_pages.
	 */
	public function add_uid() {
		$wp_idx_pages = get_posts(
			array(
				'post_type'   => 'idx_page',
				'numberposts' => -1,
			)
		);
		// If no IDX Pages, do not cause an err.
		if ( empty( $wp_idx_pages ) ) {
			return update_option( 'idx_added_uid_to_idx_pages', true, false );
		}

		foreach ( $wp_idx_pages as $wp_idx_page ) {
			$id = $wp_idx_page->ID;
			// Get IDX Page data by match to  URL.
			$uid = $this->matched_idx_page( $wp_idx_page->post_name );
			// No matching IDX page.
			if ( empty( $uid ) ) {
				continue;
			}
			// Add UID to post meta.
			update_post_meta( $id, 'idx_uid', $uid );

		}
		return update_option( 'idx_added_uid_to_idx_pages', true, false );
	}

	/**
	 * Find the IDX page with matching URL.
	 *
	 * @access public
	 * @param mixed $url URL.
	 * @return bool Matched IDX Page.
	 */
	public function matched_idx_page( $url ) {
		$saved_links  = $this->idx_api->idx_api_get_savedlinks();
		$system_links = $this->idx_api->idx_api_get_systemlinks();

		// If no links or error, end search.
		if ( ! is_array( $system_links ) || ! is_array( $saved_links ) ) {
			return false;
		}

		$idx_pages = array_merge( $saved_links, $system_links );

		foreach ( $idx_pages as $idx_page ) {
			if ( $idx_page->url === $url ) {
				return $idx_page->uid;
			}
		}
		return false;
	}
}
