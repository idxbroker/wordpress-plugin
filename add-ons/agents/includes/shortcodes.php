<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Adds shortcode to display agent profiles
 */

add_shortcode( 'employee_profiles', 'impa_profile_shortcode' );

function impa_profile_shortcode($atts, $content = null) {
	extract(shortcode_atts(array(
		'id'   => '',
		'orderby' => 'menu_order',
		'order' => 'ASC'
	), $atts ) );

	if ($id == '' ) {
		$query_args = array(
			'post_type'       => 'employee',
			'posts_per_page'  => -1,
			'orderby' 		  => $orderby,
			'order' 		  => $order

		);
	} else {
		$id = esc_attr($id);
		$query_args = array(
			'post_type'       => 'employee',
			'post__in'        => explode( ',', $id),
			'posts_per_page'  => -1,
			'orderby' 		  => $orderby,
			'order' 		  => $order
		);
	}

	global $post;

	$profiles_array = get_posts( $query_args );

	$output = '';

	foreach ( $profiles_array as $post ) : setup_postdata( $post );

		$output .= '<div class="shortcode-agent-wrap">';
		$output .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post->ID, 'employee-thumbnail' ) . '</a>';
		$output .= '<div class="shortcode-agent-details"><a class="fn" href="' . get_permalink() . '">' . get_the_title() . '</a>';
		$output .= impa_employee_details();
		if ( function_exists( '_p2p_init' ) && function_exists( 'agentpress_listings_init' ) || function_exists( '_p2p_init' ) && function_exists( 'wp_listings_init' ) ) {
			$has_listings = impa_has_listings( $post->ID );
			if ( ! empty( $has_listings ) ) {
				echo '<p><a class="agent-listings-link" href="' . esc_url( get_permalink() ) . '#agent-listings">View My Listings</a></p>';
			}
		}

		$output .= '</div>';
		$output .= impa_employee_social();

		$output .= '</div><!-- .shortcode-agent-wrap -->';

	endforeach;
	wp_reset_postdata();

	return $output;

}
