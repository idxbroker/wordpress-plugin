<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Lists all the terms of a given taxonomy
 *
 * Adds the taxonomy title and a list of the terms associated with that taxonomy
 * used in custom post type templates.
 */
function impress_agents_list_terms( $taxonomy ) {
	$the_tax_object = get_taxonomy( $taxonomy );
	$terms          = get_terms( $taxonomy );
	$term_list      = '';

	$count = count( $terms );
	$i     = 0;
	if ( $count > 0 ) {
		echo '<div class="' . esc_attr( $taxonomy ) . ' term-list-container">';
		echo '<h3 class="taxonomy-name">' . esc_html( $the_tax_object->label ) . '</h3>';
		echo '<ul class="term-list">';
		foreach ( $terms as $term ) {
			$i++;
			echo '<li><a href="' . esc_url( site_url( $taxonomy . '/' . $term->slug ) ) . '" title="View all post filed under ' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . ' ( ' . esc_html( $term->count ) . ' )</a></li>';
		}
		echo '</ul>';
		echo '</div> <!-- .' . esc_html( $taxonomy ) . ' .term-list-container -->';
	}
}


/**
 * Returns true if the queried taxonomy is a taxonomy of the given post type
 */
function impress_agents_is_taxonomy_of( $post_type ) {
	$taxonomies  = get_object_taxonomies( $post_type );
	$queried_tax = get_query_var( 'taxonomy' );

	if ( in_array( $queried_tax, $taxonomies ) ) {
		return true;
	}

	return false;
}

/**
 * Display navigation to next/previous employee when applicable.
 *
 * @since 0.1.0
 */
function impress_agents_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}

	?>
	<nav class="navigation employee-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Agents navigation', 'impress_agents' ); ?></h1>
		<div class="nav-links">
			<?php
			if ( is_attachment() ) :
				previous_post_link( '%link', __( '<span class="meta-nav">Published In</span>%title', 'impress_agents' ) );
			else :
				previous_post_link( '%link', __( '<span class="meta-nav">Previous Agent</span>%title', 'impress_agents' ) );
				next_post_link( '%link', __( '<span class="meta-nav">Next Agent</span>%title', 'impress_agents' ) );
			endif;
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}


/**
 * Display navigation to next/previous set of employees when applicable.
 *
 * @since 0.1.0
 */
function impress_agents_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $GLOBALS['wp_query']->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => __( '&larr; Previous', 'impress_agents' ),
		'next_text' => __( 'Next &rarr;', 'impress_agents' ),
	) );

	if ( $links ) :

		?>
	<nav class="navigation archive-employee-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Agents navigation', 'impress_agents' ); ?></h1>
		<div class="pagination loop-pagination">
			<?php echo wp_kses_post( $links ); ?>
		</div><!-- .pagination -->
	</nav><!-- .navigation -->
		<?php
	endif;
}

/**
 * Return registered image sizes.
 *
 * Return a two-dimensional array of just the additionally registered image sizes, with width, height and crop sub-keys.
 *
 * @since 1.0.1
 *
 * @global array $_wp_additional_image_sizes Additionally registered image sizes.
 *
 * @return array Two-dimensional, with width, height and crop sub-keys.
 */
function impress_agents_get_additional_image_sizes() {

	global $_wp_additional_image_sizes;

	if ( $_wp_additional_image_sizes )
		return $_wp_additional_image_sizes;

	return array();

}


/**
 * Returns an array of posts of connected $type
 *
 * @param string $type the connected_type
 * @return array|bool array of posts if any else false
 */
function impa_get_connected_posts_of_type( $type) {

	$connected = get_posts( array(
		'connected_type'  => $type,
		'connected_items' => get_queried_object(),
		'nopaging'        => true
	) );

	if ( empty( $connected) ) {
		return false;
	}

	return $connected;
}

/**
 * Returns an array of posts of connected $type using the $post object
 * instead of get_queried_object()
 *
 * @param string $type the connected_type
 * @param  int $post the post id
 * @return array|bool array of posts if any else false
 */
function impa_get_connected_posts_of_type_archive( $type, $post) {

	$connected = get_posts( array(
		'connected_type'  => $type,
		'connected_items' => $post,
		'nopaging'        => true
	) );

	if ( empty( $connected) ) {
		return false;
	}

	return $connected;
}

/**
 * Outputs markup for the connected listings on single agents
 */
function impa_connected_listings_markup() {

	$count = 0;

	$listings = impa_get_connected_posts_of_type( 'agents_to_listings' );

	if ( empty( $listings ) ) {
		return;
	}

	echo wp_kses_post( apply_filters( 'impa_connected_listing_heading', $heading = '<h3><a name="agent-listings">My Listings</a></h3>' ) );

	global $post;

	foreach ( $listings as $listing ) {

		setup_postdata( $listing );

		$post = $listing;

		$thumb_id  = get_post_thumbnail_id();
		$thumb_url = wp_get_attachment_image_src( $thumb_id, 'medium', true );

		$count++;

		if ( 4 == $count ) {
			$count = 1;
		}

		$class = ( 1 === $count ) ? ' first' : '';

		echo '
		<div class="one-third ', esc_attr( $class ), ' connected-listings" itemscope itemtype="http://schema.org/Offer">
			<a href="', esc_url( get_permalink( $listing->ID ) ), '"><img src="', esc_url( $thumb_url[0] ), '" alt="', esc_attr( get_the_title() ), ' photo" class="attachment-agent-profile-photo wp-post-image" itemprop="image" /></a>
			<h4 itemprop="itemOffered"><a class="listing-title" href="', esc_url( get_permalink( $listing->ID ) ), '" itemprop="url">', esc_html( get_the_title( $listing->ID ) ), '</a></h4>
			<p class="listing-price"><span class="label-price">Price: </span><span itemprop="price">', esc_html( get_post_meta( $listing->ID, '_listing_price', true ) ), '</span></p>
			<p class="listing-beds"><span class="label-beds">Beds: </span>', esc_html( get_post_meta( $listing->ID, '_listing_bedrooms', true ) ), '</p><p class="listing-baths"><span class="label-baths">Baths: </span>', esc_html( get_post_meta( $listing->ID, '_listing_bathrooms', true ) ),'</p>
		</div><!-- .connected-listings -->';
	}

	echo '<div class="clearfix"></div>';

	wp_reset_postdata();

}

/**
 * Check if the agent post id has connected listings
 */
function impa_has_listings( $post) {

	$listings = impa_get_connected_posts_of_type_archive( 'agents_to_listings', $post );

	if ( empty( $listings ) ) {
		return false;
	}
	return true;
}

/**
 * Outputs markup for the connected agents on single listings
 */
function impa_connected_agents_markup() {

	$profiles = impa_get_connected_posts_of_type( 'agents_to_listings' );

	if ( empty( $profiles ) ) {
		return;
	}

	echo wp_kses_post( apply_filters( 'impa_connected_agent_heading', $heading = '<h4>Listing Presented by:</h4>' ) );

	global $post;

	foreach ( $profiles as $profile ) {

		setup_postdata( $profile );

		$post      = $profile;
		$thumb_id  = get_post_thumbnail_id();
		$thumb_url = wp_get_attachment_image_src( $thumb_id, 'agent-profile-photo', true );

		echo '
		<div ', post_class( 'connected-agents vcard' ), ' itemscope itemtype="http://schema.org/Person">
			<div class="agent-thumb"><a href="', esc_url( get_permalink( $profile->ID ) ), '"><img src="', esc_url( $thumb_url[0] ), '" alt="', esc_attr( get_the_title() ), ' photo" class="attachment-agent-profile-photo wp-post-image alignleft" itemprop="image" /></a></div><!-- .agent-thumb -->
			<div class="agent-details"><h5><a class="fn agent-name" itemprop="name" href="', esc_url( get_permalink( $profile->ID ) ), '">', esc_html( get_the_title( $profile->ID ) ), '</a></h5>';
			echo wp_kses_post( impa_employee_details() );
			echo wp_kses_post( impa_employee_social() );
		echo '</div><!-- .agent-details --></div><!-- .connected-agents .vcard -->';
	}

	echo '<div class="clearfix"></div>';

	wp_reset_postdata();
}
