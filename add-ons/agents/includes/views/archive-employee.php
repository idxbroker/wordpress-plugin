<?php
/**
 * The template for displaying Employee Archive pages
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package IMPress Agents
 * @since 0.9.0
 */

$options = get_option( 'plugin_impress_agents_settings' );

add_action( 'wp_enqueue_scripts', 'enqueue_single_employee_scripts' );
function enqueue_single_employee_scripts() {
	wp_enqueue_style( 'font-awesome-5.8.2' );
}
add_filter( 'body_class', 'add_body_class' );

function add_body_class( $classes) {
	$classes[] = 'archive-employee';
	return $classes;
}

function archive_employee_loop() {

	$class = '';
	$i = 4;

	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			// starting at 4.
			if ( $i == 4 ) {
				$class = 'first one-fourth agent-wrap';
				$i     = 0;
			} else {
				$class = 'one-fourth agent-wrap';
			}

			// increase count by 1.
			$i++;

			$post_id   = get_the_id();
			$thumb_id  = get_post_thumbnail_id();
			$thumb_url = wp_get_attachment_image_src( $thumb_id, 'employee-thumbnail', true );

			?>

	<div <?php post_class( $class ); ?> itemscope itemtype="http://schema.org/Person">
			<?php echo '<a href="' . esc_url( get_permalink() ) . '"><img src="' . esc_url( $thumb_url[0] ) . '" alt="' . esc_attr( get_the_title() ) . ' photo" class="attachment-employee-thumbnail wp-post-image" itemprop="image" /></a>'; ?>
		<div class="agent-details vcard">
			<?php

			printf( '<p><a class="fn" href="%s" itemprop="name">%s</a></p>', get_permalink(), get_the_title() );

			impa_employee_archive_details();
			if ( function_exists( '_p2p_init' ) && function_exists( 'agentpress_listings_init' ) || function_exists( '_p2p_init' ) && function_exists( 'wp_listings_init' ) ) {

				$has_listings = impa_has_listings( $post_id );
				if (!empty( $has_listings)) {
					echo '<p><a class="agent-listings-link" href="' . get_permalink() . '#agent-listings">View My Listings</a></p>';
				}
			}

			?>
		</div><!-- .agent-details -->
	</div> <!-- .agent-wrap -->

			<?php
	endwhile;
		if ( function_exists( 'equity' ) ) {
			equity_posts_nav();
		} elseif ( function_exists( 'genesis_init' ) ) {
			genesis_posts_nav();
		} else {
			impress_agents_paging_nav();
		}

	else :
		?>

	<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
	<?php endif;

}

if (function_exists( 'equity' )) {

	add_filter( 'equity_pre_get_option_site_layout', '__equity_return_full_width_content' );
	remove_action( 'equity_entry_header', 'equity_post_info', 12 );
	remove_action( 'equity_entry_footer', 'equity_post_meta' );

	remove_action( 'equity_loop', 'equity_do_loop' );
	add_action( 'equity_loop', 'archive_employee_loop' );

	equity();

} elseif (function_exists( 'genesis_init' )) {

	add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	remove_action( 'genesis_after_entry', 'genesis_do_author_box_single' );

	remove_action( 'genesis_loop', 'genesis_do_loop' );
	add_action( 'genesis_loop', 'archive_employee_loop' );

	genesis();

} else {

get_header();
if ( $options['impress_agents_custom_wrapper'] && $options['impress_agents_start_wrapper']) {
	echo $options['impress_agents_start_wrapper'];
} else {
	echo '<div id="primary" class="content-area container inner">
		<div id="content" class="site-content" role="main">';
}
	if ( have_posts() ) :
		?>

		<header class="archive-header">
			<?php
			$object = get_queried_object();

			if ( ! isset( $object->label ) ) {
				echo '<h1 class="archive-title">' . esc_html( $object->name ) . '</h1>';
			} else {
				echo '<h1 class="archive-title">' . esc_html( get_bloginfo( 'name' ) ) . ' Employees</h1>';
			}
			?>

			<small>
				<?php
				if ( function_exists( 'yoast_breadcrumb' ) ) {
					yoast_breadcrumb( '<p id="breadcrumbs">', '</p>' );
				}
				?>
			</small>
		</header><!-- .archive-header -->

	<?php

	archive_employee_loop();

	else :
		// If no content, include the "No posts found" template.
		get_template_part( 'content', 'none' );

	endif;

	if ( $options['impress_agents_custom_wrapper'] && $options['impress_agents_end_wrapper']) {
		echo $options['impress_agents_end_wrapper'];
	} else {
		echo '</div><!-- #content -->
		</div><!-- #primary -->';
	}
	get_sidebar();
	get_footer();

}
