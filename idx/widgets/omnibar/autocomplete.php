<?php
namespace IDX\Widgets\Omnibar;

/**
 * Draws data from our autocomplete table (currently only addresses)
 */
class Autocomplete {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * get_autocomplete_data function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public function get_autocomplete_data( $data ) {

		check_ajax_referer( 'wp_rest', '_wpnonce' );

		$search_text = urldecode( $data['query'] );
		$like_query  = '%' . $search_text . '%';

		global $wpdb;

		$table_name = $wpdb->prefix . 'idx_broker_autocomplete_values';

		// Need to interpolate the table name, since wpdb::prepare will escape the value, adding quotes, and that table doesn't exist.
		// It is safe though, since we are deriving the table name from $wpdb->prefix.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT value, field, mls FROM $table_name WHERE value LIKE %s LIMIT 10", $like_query
			)
		);

		if ( ! is_array( $results ) ) {
			return [];
		}

		return $results;
	}

}
