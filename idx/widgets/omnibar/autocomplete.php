<?php
namespace IDX\Widgets\Omnibar;

class Autocomplete {
	public function __construct() {

	}

	public function get_autocomplete_data( $data ) {

		check_ajax_referer('wp_rest', '_wpnonce');

		$search_text = urldecode($data['query']);

		global $wpdb;

		$table_name = $wpdb->prefix . 'idxbroker_autocomplete_values';

		// Direct db call is fine, since we can't cache every search variant
		$results = $wpdb->get_results(
			"SELECT value FROM $table_name WHERE value LIKE '%$search_text%' LIMIT 10"
		);

		// MAKE SURE ASSOC ARRAY IS ASSOC ARRAY HERE

		$output_array = array_map(function($x) {
			return $x->value;
		}, $results);

		return $output_array;
	}

}
