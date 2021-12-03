<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Holds miscellaneous functions for use in the IMPress Agents plugin
 *
 */
add_action( 'pre_get_posts', 'impa_change_sort_order' );
/**
 * Add pagination and sort by menu order for employee archives
 */
function impa_change_sort_order( $query ) {

	if ( $query->is_main_query() && ! is_admin() ) {
		if ( is_post_type_archive( 'employee' ) || is_tax( array( 'offices', 'job-types' ) ) ) {
			$query->set( 'meta_key', '_employee_last_name' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		}
	}
}

add_action( 'p2p_init', 'impa_employee_connection_types' );
/**
 * Connects employee post type to listing post type
 */
function impa_employee_connection_types() {

	if ( ! post_type_exists( 'listing' ) || ! post_type_exists( 'employee' ) ) {
		return;
	}

	p2p_register_connection_type(
		[
			'name'      => 'agents_to_listings',
			'from'      => 'employee',
			'to'        => 'listing',
			'sortable'  => 'any',
		]
	);
}

add_image_size( 'employee-thumbnail', 150, 200, true );
add_image_size( 'employee-full', 300, 400, true );

add_filter( 'template_include', 'impress_agents_template_include' );
function impress_agents_template_include( $template ) {

	global $wp_query;

	$post_type = 'employee';

	if ( $wp_query->is_search && get_post_type() == 'employee' ) {
		if ( file_exists( get_stylesheet_directory() . '/search-' . $post_type . '.php' ) ) {
			$template = get_stylesheet_directory() . '/search-' . $post_type . '.php';
			return $template;
		} else {
			return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
		}
	}
	if ( impress_agents_is_taxonomy_of( $post_type ) ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-' . $post_type . '.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-' . $post_type . '.php';
		} elseif ( file_exists( get_stylesheet_directory() . '/archive-' . $post_type . '.php' ) ) {
			return get_stylesheet_directory() . '/archive-' . $post_type . '.php';
		} else {
			return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
		}
	}

	if ( is_post_type_archive( $post_type ) ) {
		if ( file_exists( get_stylesheet_directory() . '/archive-' . $post_type . '.php' ) ) {
			$template = get_stylesheet_directory() . '/archive-' . $post_type . '.php';
			return $template;
		} else {
			return dirname( __FILE__ ) . '/views/archive-' . $post_type . '.php';
		}
	}

	if ( is_single() && get_post_type() === $post_type ) {
		if ( file_exists( get_stylesheet_directory() . '/single-' . $post_type . '.php' ) ) {
			return $template;
		} else {
			return dirname( __FILE__ ) . '/views/single-' . $post_type . '.php';
		}
	}

	return $template;
}

function impa_employee_details() {
	global $post;

	$output = '';

	if ( get_post_meta( $post->ID, '_employee_title', true ) != '' ) {
		$output .= sprintf( '<p class="title" itemprop="jobTitle">%s</p>', get_post_meta( $post->ID, '_employee_title', true ) );
	}
	if ( get_post_meta( $post->ID, '_employee_license', true ) != '' ) {
		$output .= sprintf( '<p class="license">%s</p>', get_post_meta( $post->ID, '_employee_license', true ) );
	}
	if ( get_post_meta( $post->ID, '_employee_designations', true ) != '' ) {
		$output .= sprintf( '<p class="designations" itemprop="awards">%s</p>', get_post_meta( $post->ID, '_employee_designations', true ) );
	}
	if ( get_post_meta( $post->ID, '_employee_phone', true ) != '' ) {
		$output .= sprintf( '<p class="tel" itemprop="telephone"><span class="type">Office</span>: <span class="value">%s</span></p>', get_post_meta( $post->ID, '_employee_phone', true ) );
	}
	if ( get_post_meta( $post->ID, '_employee_mobile', true ) != '' ) {
		$output .= sprintf( '<p class="tel" itemprop="telephone"><span class="type">Cell</span>: <span class="value">%s</span></p>', get_post_meta( $post->ID, '_employee_mobile', true ) );
	}
	if ( get_post_meta( $post->ID, '_employee_email', true ) != '' ) {
		$email = get_post_meta( $post->ID, '_employee_email', true );
		$output .= sprintf( '<p><a class="email" itemprop="email" href="mailto:%s">%s</a></p>', antispambot( $email ), antispambot( $email ) );
	}

	if ( get_post_meta( $post->ID, '_employee_website', true ) != '' ) {
		$website = get_post_meta( $post->ID, '_employee_website', true );
		$website_no_http = preg_replace( '#^https?://#', '', rtrim( $website, '/' ) );
		$output .= sprintf( '<p><a class="website" itemprop="url" href="%s">%s</a></p>', $website, $website_no_http );
	}

	if ( get_post_meta( $post->ID, '_employee_city', true ) != '' || get_post_meta( $post->ID, '_employee_address', true ) != '' || get_post_meta( $post->ID, '_employee_state', true ) != '' || get_post_meta( $post->ID, '_employee_zip', true ) != '' ) {

		$address = '<p class="adr" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';

		if ( get_post_meta( $post->ID, '_employee_address', true ) != '' ) {
			$address .= '<span class="street-address" itemprop="streetAddress">' . get_post_meta( $post->ID, '_employee_address', true ) . '</span><br />';
		}

		if ( get_post_meta( $post->ID, '_employee_city', true ) != '' ) {
			$address .= '<span class="locality" itemprop="addressLocality">' . get_post_meta( $post->ID, '_employee_city', true ) . '</span>, ';
		}

		if ( get_post_meta( $post->ID, '_employee_state', true ) != '' ) {
			$address .= '<abbr class="region" itemprop="addressRegion">' . get_post_meta( $post->ID, '_employee_state', true ) . '</abbr> ';
		}

		if ( get_post_meta( $post->ID, '_employee_zip', true ) != '' ) {
			$address .= '<span class="postal-code" itemprop="postalCode">' . get_post_meta( $post->ID, '_employee_zip', true ) . '</span>';
		}

		$address .= '</p>';

		if ( get_post_meta( $post->ID, '_employee_address', true ) != '' || get_post_meta( $post->ID, '_employee_city', true ) != '' || get_post_meta( $post->ID, '_employee_state', true ) != '' || get_post_meta( $post->ID, '_employee_zip', true ) != '' ) {
			$output .= $address;
		}
	}

	return $output;
}

function impa_employee_archive_details() {
	global $post;

	if ( get_post_meta( $post->ID, '_employee_title', true ) != '' ) {
		printf( '<p class="title" itemprop="jobTitle">%s</p>', esc_html( get_post_meta( $post->ID, '_employee_title', true ) ) );
	}

	if ( get_post_meta( $post->ID, '_employee_phone', true ) != '' ) {
		printf( '<p class="tel" itemprop="telephone"><span class="type">Office</span>: <span class="value">%s</span></p>', esc_html( get_post_meta( $post->ID, '_employee_phone', true ) ) );
	}

	if ( get_post_meta( $post->ID, '_employee_email', true ) != '' ) {
		$email = get_post_meta( $post->ID, '_employee_email', true );
		printf( '<p><a class="email" itemprop="email" href="mailto:%s">%s</a></p>', esc_attr( antispambot( $email ) ), esc_html( antispambot( $email ) ) );
	}

	if ( function_exists( '_p2p_init' ) && function_exists( 'agentpress_listings_init' ) || function_exists( '_p2p_init' ) && function_exists( 'wp_listings_init' ) ) {
		$listings = impa_get_connected_posts_of_type( 'agents_to_listings' );
		if ( ! empty( $listings ) ) {
			echo '<p><a class="agent-listings-link" href="' . esc_url( get_permalink() ) . '#agent-listings">View My Listings</a></p>';
		}
	}
}

function impa_employee_social() {
	global $post;

	if ( get_post_meta( $post->ID, '_employee_facebook', true ) != '' || get_post_meta( $post->ID, '_employee_twitter', true ) != '' || get_post_meta( $post->ID, '_employee_linkedin', true ) != '' || get_post_meta( $post->ID, '_employee_googleplus', true ) != '' || get_post_meta( $post->ID, '_employee_pinterest', true ) != '' || get_post_meta( $post->ID, '_employee_youtube', true ) != '' || get_post_meta( $post->ID, '_employee_instagram', true ) != '' ) {

		$output = '<div class="agent-social-profiles">';

		if ( get_post_meta( $post->ID, '_employee_facebook', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-facebook" rel="me" itemprop="sameAs" href="%s" title="Facebook Profile"></a>', get_post_meta( $post->ID, '_employee_facebook', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_twitter', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-twitter" rel="me" itemprop="sameAs" href="%s" title="Twitter Profile"></a>', get_post_meta( $post->ID, '_employee_twitter', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_linkedin', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-linkedin" rel="me" itemprop="sameAs" href="%s" title="LinkedIn Profile"></a>', get_post_meta( $post->ID, '_employee_linkedin', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_googleplus', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-google-plus" rel="me" itemprop="sameAs" href="%s" title="Google+ Profile"></a>', get_post_meta( $post->ID, '_employee_googleplus', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_pinterest', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-pinterest" rel="me" itemprop="sameAs" href="%s" title="Pinterest Profile"></a>', get_post_meta( $post->ID, '_employee_pinterest', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_youtube', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-youtube" rel="me" itemprop="sameAs" href="%s" title="YouTube Profile"></a>', get_post_meta( $post->ID, '_employee_youtube', true ) );
		}

		if ( get_post_meta( $post->ID, '_employee_instagram', true ) != '' ) {
			$output .= sprintf( '<a class="fab fa-instagram" rel="me" itemprop="sameAs" href="%s" title="Instagram Profile"></a>', get_post_meta( $post->ID, '_employee_instagram', true ) );
		}

		$output .= '</div><!-- .employee-social-profiles -->';

		return $output;
	}
}

/**
 * Displays the job type of a employee
 */
function impress_agents_get_job_types( $post_id = null ) {

	if ( null == $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	$employee_job_types = wp_get_object_terms( $post_id, 'job-types' );

	if ( empty( $employee_job_types ) || is_wp_error( $employee_job_types ) ) {
		return;
	}

	foreach ( $employee_job_types as $type ) {
		return $type->name;
	}
}

/**
 * Displays the office of a employee
 */
function impress_agents_get_offices( $post_id = null ) {

	if ( null == $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	$employee_occifcs = wp_get_object_terms( $post_id, 'occifcs' );

	if ( empty( $employee_occifcs ) || is_wp_error( $employee_occifcs ) ) {
		return;
	}

	foreach ( $employee_occifcs as $office ) {
		return $office->name;
	}
}

function impress_agents_post_number( $query ) {

	if ( ! $query->is_main_query() || is_admin() || ! is_post_type_archive( 'employee' ) ) {
		return;
	}

	$options = get_option( 'plugin_impress_agents_settings' );

	$archive_posts_num = $options['impress_agents_archive_posts_num'];

	if ( empty( $archive_posts_num ) ) {
		$archive_posts_num = '9';
	}

	$query->query_vars['posts_per_page'] = $archive_posts_num;

}
add_action( 'pre_get_posts', 'impress_agents_post_number' );

/**
 * Add Employees to "At a glance" Dashboard widget
 */
add_filter( 'dashboard_glance_items', 'impress_agents_glance_items', 10, 1 );
function impress_agents_glance_items( $items = array() ) {

	$post_types = array( 'employee' );

	foreach ( $post_types as $type ) {

		if ( ! post_type_exists( $type ) ) continue;

		$num_posts = wp_count_posts( $type );

		if ( $num_posts ) {

			$published = intval( $num_posts->publish );
			$post_type = get_post_type_object( $type );

			$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'impress_agents' );
			$text = sprintf( $text, number_format_i18n( $published ) );

			if ( current_user_can( $post_type->cap->edit_posts ) ) {
				$items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', $type, $text ) . "\n";
			} else {
				$items[] = sprintf( '<span class="%1$s-count">%2$s</span>', $type, $text ) . "\n";
			}
		}
	}

	return $items;
}

/**
 * Add Employees to Jetpack Omnisearch
 */
if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
	new Jetpack_Omnisearch_Posts( 'employee' );
}

/**
 * Add Employees to Jetpack sitemap
 */
add_filter( 'jetpack_sitemap_post_types', 'impress_agents_jetpack_sitemap' );
function impress_agents_jetpack_sitemap() {
	$post_types[] = 'employee';
	return $post_types;
}

/**
 * Function to return term image for use on front end
 * @param  num  $term_id the id of the term
 * @param  boolean $html    use html wrapper with wp_get_attachment_image
 * @return mixed  the image with html markup or the image id
 */
function impress_agents_term_image( $term_id, $html = true, $size = 'full' ) {
	$image_id = get_term_meta( $term_id, 'impa_term_image', true );
	return $image_id && $html ? wp_get_attachment_image( $image_id, $size, false, array( 'class' => 'impress-agents-term-image' ) ) : $image_id;
}
