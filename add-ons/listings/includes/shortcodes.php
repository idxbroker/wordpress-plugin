<?php 
/**
 * Adds shortcode to display listings
 * Adds shortcode to display post meta
 */

add_shortcode( 'listings', 'wp_listings_shortcode' );

function wp_listings_shortcode($atts, $content = null) {
	extract(shortcode_atts(array(
		'id'       => '',
		'taxonomy' => '',
		'term'     => '',
		'limit'    => '',
		'columns'  => ''
	), $atts ) );

	$limit    = (int) $limit;
	$columns  = (int) $columns;
	$taxonomy = sanitize_key($taxonomy);
	$term     = sanitize_text_field($term);

	/**
	 * if limit is empty set to all
	 */
	if(!$limit) {
		$limit = -1;
	}

	/*
	 * query args based on parameters
	 */
	$query_args = array(
		'post_type'       => 'listing',
		'posts_per_page'  => $limit
	);

	if($id) {
		$query_args = array(
			'post_type'       => 'listing',
			'post__in'        => array_map('intval', explode(',', $id))
		);
	}

	if($term && $taxonomy) {
		$query_args = array(
			'post_type'       => 'listing',
			'posts_per_page'  => $limit,
			'tax_query'       => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'     => $term
				)
			)
		);
	}

	/*
	 * start loop
	 */
	global $post;

	$listings_array = get_posts( $query_args );

	$count = 0;

	$output = '<div class="wp-listings-shortcode">';

	foreach ( $listings_array as $post ) : setup_postdata( $post );

		$count = ( $count == $columns ) ? 1 : $count + 1;

		$first_class = ( 1 == $count ) ? 'first' : '';

		$output .= '<div class="listing-wrap ' . esc_attr( get_column_class($columns) ) . ' ' . esc_attr( $first_class ) . '"><div class="listing-widget-thumb"><a href="' . esc_url( get_permalink() ) . '" class="listing-image-link">' . get_the_post_thumbnail( $post->ID, 'listings' ) . '</a>';

		if ( '' != wp_listings_get_status() ) {
			$output .= '<span class="listing-status ' . esc_attr( strtolower(str_replace(' ', '-', wp_listings_get_status())) ) . '">' . esc_html( wp_listings_get_status() ) . '</span>';
		}

		$output .= '<div class="listing-thumb-meta">';

		if ( '' != get_post_meta( $post->ID, '_listing_text', true ) ) {
			$output .= '<span class="listing-text">' . esc_html( get_post_meta( $post->ID, '_listing_text', true ) ) . '</span>';
		} elseif ( '' != wp_listings_get_property_types() ) {
			$output .= '<span class="listing-property-type">' . esc_html( wp_listings_get_property_types() ) . '</span>';
		}

		if ( '' != get_post_meta( $post->ID, '_listing_price', true ) ) {
			$output .= '<span class="listing-price">' . esc_html( get_post_meta( $post->ID, '_listing_price', true ) ) . '</span>';
		}

		$output .= '</div><!-- .listing-thumb-meta --></div><!-- .listing-widget-thumb -->';

		if ( '' != get_post_meta( $post->ID, '_listing_open_house', true ) ) {
			$output .= '<span class="listing-open-house">' . __( "Open House", 'wp-listings' ) . ': ' . esc_html( get_post_meta( $post->ID, '_listing_open_house', true ) ) . '</span>';
		}

		$output .= '<div class="listing-widget-details"><h3 class="listing-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		$output .= '<p class="listing-address"><span class="listing-address">' . esc_html( wp_listings_get_address() ) . '</span><br />';
		$output .= '<span class="listing-city-state-zip">' . esc_html( wp_listings_get_city() ) . ', ' . esc_html( wp_listings_get_state() ) . ' ' . esc_html( get_post_meta( $post->ID, '_listing_zip', true ) ) . '</span></p>';

		if ( '' != get_post_meta( $post->ID, '_listing_bedrooms', true ) || '' != get_post_meta( $post->ID, '_listing_bathrooms', true ) || '' != get_post_meta( $post->ID, '_listing_sqft', true )) {
			$output .= '<ul class="listing-beds-baths-sqft"><li class="beds">' . esc_html( get_post_meta( $post->ID, '_listing_bedrooms', true ) ) . '<span>' . __( "Beds", 'wp-listings' ) . '</span></li> <li class="baths">' . esc_html( get_post_meta( $post->ID, '_listing_bathrooms', true ) ) . '<span>' . __( "Baths", 'wp-listings' ) . '</span></li> <li class="sqft">' . esc_html( get_post_meta( $post->ID, '_listing_sqft', true ) ) . '<span>' . __( "Square Feet", 'wp-listings' ) . '</span></li></ul>';
		}

		$output .= '</div><!-- .listing-widget-details --></div><!-- .listing-wrap -->';

	endforeach;

	$output .= '</div><!-- .wp-listings-shortcode -->';

	wp_reset_postdata();

	return $output;
	
}

add_shortcode('wp_listings_meta', 'wp_listings_meta_shortcode');
/**
 * Returns meta data for listings
 * @param  array $atts meta key
 * @return string meta value wrapped in span
 */
function wp_listings_meta_shortcode($atts) {
	extract(shortcode_atts(array(
		'key' => ''
	), $atts ) );
	$key = sanitize_key($key);
	$postid = get_the_id();

	return '<span class="' . esc_attr( $key ) . '">' . wp_kses_post( get_post_meta( $postid, '_listing_' . $key, true ) ) . '</span>';
}
