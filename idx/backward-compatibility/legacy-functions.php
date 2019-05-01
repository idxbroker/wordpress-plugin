<?php
/**
 * Legacy IDX Tags.
 *
 * @package idxbroker-platinum
 * @since 2.5.10
 */

namespace {
	/*
	* global namespace for legacy start and stop functions from IDX Broker Original
	* These functions were sometimes added directly into themes.
	*/
	legacy_idx_tags();

	/**
	 * Legacy IDX Tags.
	 *
	 * @access public
	 * @since 2.5.10
	 * @return Legacy HTML Start/Stop Tags.
	 */
	function legacy_idx_tags() {
		if ( ! function_exists( 'idx_start' ) ) {

			/**
			 * IDX Start.
			 *
			 * @access public
			 * @since 2.5.10
			 * @return HTML for Start Tag.
			 */
			function idx_start() {
				return '<div id="idxStart" style="display: none;"></div>';
			}

			/**
			 * IDX Stop.
			 *
			 * @access public
			 * @since 2.5.10
			 * @return HTML for Stop Tag.
			 */
			function idx_stop() {
				return '<div id="idxStop" style="display: none;"></div>';
			}
		}
	}
}
