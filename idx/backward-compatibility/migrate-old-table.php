<?php
/**
 * Migrate Old Tables.
 *
 * @package idxbroker-platinum
 */

/* Exit if accessed directly. */
namespace IDX\Backward_Compatibility;

defined( 'ABSPATH' ) || exit;

use \IDX\Idx_Api;

/**
 * Migrate Legacy Plugin Pages from version <1.3
 */
class Migrate_Old_Table {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		$post_info = $this->grab_post_ids();
		if ( empty( $post_info ) ) {
			return;
		}
		$this->migrate_old_pages( $post_info );
		$this->drop_old_table();
		$this->migrate_old_wrapper();

	}

	/**
	 * Grab Post IDs.
	 *
	 * @access public
	 * @return void
	 */
	public function grab_post_ids() {
		global $wpdb;
		$post_ids = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'" );
		$links    = $wpdb->get_col( 'SELECT meta_value FROM ' . $wpdb->prefix . "postmeta WHERE meta_key = '_links_to'" );
		return array(
			'post_ids' => $post_ids,
			'links'    => $links,
		);
	}

	/**
	 * Update Post Type.
	 *
	 * @access public
	 * @param mixed $post_id Post ID.
	 * @param mixed $link Link.
	 * @param mixed $post_type Post Type.
	 * @return void
	 */
	public function update_post_type( $post_id, $link, $post_type ) {
		if ( 'idx_page' === $post_type ) {
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'posts',
				array(
					'post_name' => $link,
					'post_type' => 'idx_page',
				),
				array(
					'ID' => $post_id,
				)
			);
		} else {
			$post = get_post( $post_id );
			$link = $post->post_name;
			set_post_type( $post_id, $post_type );
		}
	}

	/**
	 * Migrate Old Pages.
	 *
	 * @access public
	 * @param mixed $post_info Post Info.
	 * @return void
	 */
	public function migrate_old_pages( $post_info ) {
		$post_ids           = $post_info['post_ids'];
		$links              = $post_info['links'];
		$args               = array(
			'post_type'      => 'idx_page',
			'posts_per_page' => -1,
		);
		$custom_posts_array = get_posts( $args );
		// Delete duplicates first.
		foreach ( $links as $link ) {
			$this->find_and_remove_duplicate_posts( $link, $custom_posts_array );
		}
		// Update existing idx pages to custom post type.
		for ( $i = 0; $i < count( $post_ids ); $i++ ) {
			$this->update_post_type( $post_ids[ $i ], $links[ $i ], 'idx_page' );
		}
	}

	/**
	 * Drop Old Table.
	 *
	 * @access public
	 * @return void
	 */
	public function drop_old_table() {
		global $wpdb;
		$posts_idx = $wpdb->prefix . 'posts_idx';
		$postmeta  = $wpdb->prefix . 'postmeta';
		$wpdb->query( "DROP TABLE IF EXISTS $posts_idx" );
		$wpdb->delete(
			$postmeta,
			array(
				'meta_key' => '_links_to',
			)
		);
		return update_option( 'idx_migrated_old_table', true, false );
	}

	/**
	 * Remove Duplicate Posts.
	 *
	 * @access public
	 * @param mixed $page_id Page ID.
	 * @return void
	 */
	public function remove_duplicate_posts( $page_id ) {
		wp_delete_post( $page_id, true );
		wp_trash_post( $page_id );
	}

	/**
	 * Find and Remove Duplicate Posts.
	 *
	 * @access public
	 * @param mixed $link Link.
	 * @param mixed $custom_posts_array Custom Posts Array.
	 * @return void
	 */
	public function find_and_remove_duplicate_posts( $link, $custom_posts_array ) {
		foreach ( $custom_posts_array as $post ) {
			if ( $post->post_name === $link ) {
				$page_id = $post->ID;
				$this->remove_duplicate_posts( $page_id );
			}
		}
	}

	/**
	 * Migrate Old Wrappers..
	 *
	 * @access public
	 * @return void
	 */
	public function migrate_old_wrapper() {
		$page_id = get_option( 'idx_broker_dynamic_wrapper_page_id' );
		if ( ! empty( $page_id ) ) {
			// Update post type to wrappers.
			$this->update_post_type( $page_id, null, 'idx-wrapper' );

			// Update global wrapper.
			$wrapper_page_url = get_permalink( $page_id );
			$this->idx_api->set_wrapper( 'global', $wrapper_page_url );
		}
		flush_rewrite_rules();
	}
}
