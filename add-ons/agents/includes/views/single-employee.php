<?php
/**
 * The Template for displaying all single employee posts
 *
 * @package IMPress Agents
 * @since 0.9.0
 */

add_action( 'wp_enqueue_scripts', 'enqueue_single_employee_scripts' );
function enqueue_single_employee_scripts() {
	wp_enqueue_style( 'font-awesome-5.8.2' );
}

function single_employee_post_content() {
	global $post;
	?>

	<div itemscope class="entry-content impa-single-employee">

	<div class="agent-wrap" itemscope itemtype="http://schema.org/Person">
		<?php
			$thumb_id  = get_post_thumbnail_id();
			$thumb_url = wp_get_attachment_image_src( $thumb_id, 'employee-full', true );
			echo '<img src="' . esc_url( $thumb_url[0] ) . '" alt="' . esc_attr( get_the_title() ) . ' photo" class="attachment-employee-full wp-post-image" itemprop="image" />';
		?>
		<div class="agent-details vcard">
			<span class="fn" style="display:none;" itemprop="name"><?php the_title(); ?></span>
			<?php echo wp_kses_post( impa_employee_details() ); ?>
			<?php echo wp_kses_post( impa_employee_social() ); ?>
		</div> <!-- .agent-details -->
	</div> <!-- .agent-wrap -->

	<div class="agent-bio">
		<?php the_content(); ?>
	</div><!-- .agent-bio -->

	<?php
	if ( function_exists( '_p2p_init' ) && function_exists( 'agentpress_listings_init' ) || function_exists( '_p2p_init' ) && function_exists( 'wp_listings_init' ) ) {
		echo '<div class="connected-agent-listings">';
		impa_connected_listings_markup();
		echo '</div>';
	}
	?>

	</div><!-- .entry-content -->

	<?php
}

$options = get_option( 'plugin_impress_agents_settings' );

if ( function_exists( 'equity' ) ) {

	remove_action( 'equity_entry_header', 'equity_post_info', 12 );
	remove_action( 'equity_entry_footer', 'equity_post_meta' );

	remove_action( 'equity_entry_content', 'equity_do_post_content' );
	add_action( 'equity_entry_content', 'single_employee_post_content' );

	equity();

} elseif ( function_exists( 'genesis_init' ) ) {

	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 ); // HTML5.
	remove_action( 'genesis_before_post_content', 'genesis_post_info' ); // XHTML.
	remove_action( 'genesis_entry_footer', 'genesis_post_meta' ); // HTML5.
	remove_action( 'genesis_after_post_content', 'genesis_post_meta' ); // XHTML.
	remove_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 ); // HTML5.
	remove_action( 'genesis_after_post', 'genesis_do_author_box_single' ); // XHTML.

	remove_action( 'genesis_entry_content', 'genesis_do_post_content' ); // HTML5.
	remove_action( 'genesis_post_content', 'genesis_do_post_content' ); // XHTML.
	add_action( 'genesis_entry_content', 'single_employee_post_content' ); // HTML5.
	add_action( 'genesis_post_content', 'single_employee_post_content' ); // XHTML.

	genesis();

} else {

	get_header();

	if ( ! empty( $options['impress_agents_custom_wrapper'] ) && ! empty( $options['impress_agents_start_wrapper'] ) ) {
		echo wp_kses_post( $options['impress_agents_start_wrapper'] );
	} else {
		echo '<div id="primary" class="content-area container inner">
			<div id="content" class="site-content" role="main">';
	}
	// Start the Loop.
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title" itemprop="name">', '</h1>' ); ?>
				<small>
					<?php
					if ( function_exists( 'yoast_breadcrumb' ) ) {
						yoast_breadcrumb( '<p id="breadcrumbs">', '</p>' );
					}
					?>
				</small>
				<div class="entry-meta">

					<?php edit_post_link( __( 'Edit', 'impress_agents' ), '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .entry-meta -->
			</header><!-- .entry-header -->


		<?php single_employee_post_content(); ?>

		</article><!-- #post-ID -->

		<?php
		// Previous/next post navigation.
		impress_agents_post_nav();

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;

	if ( ! empty( $options['impress_agents_custom_wrapper'] ) && ! empty( $options['impress_agents_end_wrapper'] ) ) {
		echo wp_kses_post( $options['impress_agents_end_wrapper'] );
	} else {
		echo '</div><!-- #content -->
		</div><!-- #primary -->';
	}
	get_sidebar();
	get_footer();

}
