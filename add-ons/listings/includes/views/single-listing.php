<?php
/**
 * The Template for displaying all single listing posts
 *
 * @package WP Listings
 * @since 0.1.0
 */

add_action( 'wp_enqueue_scripts', 'enqueue_single_listing_scripts' );
/**
 * Enqueue_Single_Listing_Scripts function.
 *
 * @return void
 */
function enqueue_single_listing_scripts() {
	// Template Styles.
	wp_enqueue_style( 'impb-default-template', IMPRESS_IDX_URL . 'assets/css/listing-templates/single-listing-default.min.css', [], '1.0' );
	wp_enqueue_style( 'wp-listings-single' );
	wp_enqueue_style( 'font-awesome-5.8.2' );
	// Template Scripts.
	wp_enqueue_script( 'jquery-validate' );
	wp_enqueue_script( 'fitvids' );
	wp_enqueue_script( 'wp-listings-single' );
	wp_enqueue_script( 'impb-default-template-script', IMPRESS_IDX_URL . 'assets/js/listing-templates/single-listing-default.min.js', [], '1.0', false );
}

/**
 * Single_Listing_Post_Content function.
 *
 * @return void
 */
function single_listing_post_content() {

	global $post;
	$options = get_option( 'plugin_wp_listings_settings' );

	?>

	<div itemscope itemtype="http://schema.org/SingleFamilyResidence" class="entry-content wplistings-single-listing">

		<div class="listing-image-wrap">
			<?php
			echo '<div itemprop="image" itemscope itemtype="http://schema.org/ImageObject">' . get_the_post_thumbnail( $post->ID, 'listings-full', [ 'class' => 'single-listing-image', 'itemprop' => 'contentUrl' ] ) . '</div>';
			if ( ! empty( wp_listings_get_status() ) ) {
				printf( '<span class="listing-status %s">%s</span>', esc_attr( strtolower( str_replace( ' ', '-', wp_listings_get_status() ) ) ), esc_html( wp_listings_get_status() ) );
			}
			if ( ! empty( get_post_meta( $post->ID, '_listing_open_house', true ) ) ) {
				printf( '<span class="listing-open-house">Open House: %s</span>', esc_html( get_post_meta( $post->ID, '_listing_open_house', true ) ) );
			}
			?>
		</div><!-- .listing-image-wrap -->

		<?php
		echo '<ul class="listing-meta">';

		if ( get_post_meta( $post->ID, '_listing_hide_price', true ) == 1 ) {
			echo ( get_post_meta( $post->ID, '_listing_price_alt', true ) ) ? sprintf( '<li class="listing-price">%s</li>', esc_html( get_post_meta( $post->ID, '_listing_price_alt', true ) ) ) : '';
		} else {
			$currency_symbol = ( empty( $options['wp_listings_currency_symbol'] ) || 'none' === $options['wp_listings_currency_symbol'] ) ? '' : $options['wp_listings_currency_symbol'];
			$currency_code   = ( ! empty( $options['wp_listings_display_currency_code'] ) && ! empty( $options['wp_listings_display_currency_code'] ) && 'none' !== $options['wp_listings_currency_code'] ) ? $options['wp_listings_currency_code'] : '';
			printf( '<li class="listing-price">%s %s <span class="currency-code">%s</span></li>', '<span class="currency-symbol">' . esc_html( $currency_symbol ) . '</span>', esc_html( get_post_meta( $post->ID, '_listing_price', true ) ), esc_html( $currency_code ) );
		}

		if ( ! empty( wp_listings_get_property_types() ) ) {
			printf( '<li class="listing-property-type"><span class="label">Property Type: </span>%s</li>', get_the_term_list( get_the_ID(), 'property-types', '', ', ', '' ) );
		}

		if ( ! empty( wp_listings_get_locations() ) ) {
			printf( '<li class="listing-location"><span class="label">Location: </span>%s</li>', get_the_term_list( get_the_ID(), 'locations', '', ', ', '' ) );
		}

		if ( ! empty( get_post_meta( $post->ID, '_listing_bedrooms', true ) ) ) {
			printf( '<li class="listing-bedrooms"><span class="label">Beds: </span>%s</li>', esc_html( get_post_meta( $post->ID, '_listing_bedrooms', true ) ) );
		}

		if ( ! empty( get_post_meta( $post->ID, '_listing_bathrooms', true ) ) ) {
			printf( '<li class="listing-bathrooms"><span class="label">Baths: </span>%s</li>', esc_html( get_post_meta( $post->ID, '_listing_bathrooms', true ) ) );
		}

		if ( ! empty( get_post_meta( $post->ID, '_listing_sqft', true ) ) ) {
			printf( '<li class="listing-sqft"><span class="label">Sq Ft: </span>%s</li>', esc_html( get_post_meta( $post->ID, '_listing_sqft', true ) ) );
		}

		if ( ! empty( get_post_meta( $post->ID, '_listing_lot_sqft', true ) ) ) {
			printf( '<li class="listing-lot-sqft"><span class="label">Lot Sq Ft: </span>%s</li>', esc_html( get_post_meta( $post->ID, '_listing_lot_sqft', true ) ) );
		}

		if ( ! empty( get_post_meta( $post->ID, '_listing_acres', true ) ) ) {
			printf( '<li class="listing-acres"><span class="label">Acres: </span>%s</li>', esc_html( get_post_meta( $post->ID, '_listing_acres', true ) ) );
		}

		echo '</ul>';

		echo ( get_post_meta( $post->ID, '_listing_courtesy', true ) ) ? '<p class="wp-listings-courtesy">' . esc_html( get_post_meta( $post->ID, '_listing_courtesy', true ) ) . '</p>' : '';

		?>

		<div id="listing-tabs" class="listing-data">

			<ul>
				<li><a href="#listing-description">Description</a></li>

				<li><a href="#listing-details">Details</a></li>


				<?php if ( ! empty( get_post_meta( $post->ID, '_listing_gallery', true ) ) ) { ?>
					<li><a href="#listing-gallery">Photos</a></li>
				<?php } ?>

				<?php if ( ! empty( get_post_meta( $post->ID, '_listing_video', true ) ) ) { ?>
					<li><a href="#listing-video">Video / Virtual Tour</a></li>
				<?php } ?>

				<?php if ( ! empty( get_post_meta( $post->ID, '_listing_school_neighborhood', true ) ) ) { ?>
				<li><a href="#listing-school-neighborhood">Schools &amp; Neighborhood</a></li>
				<?php } ?>
			</ul>

			<div id="listing-description" itemprop="description">
				<?php
				the_content( esc_html__( 'View more <span class="meta-nav">&rarr;</span>', 'wp-listings' ) );

				echo ( get_post_meta( $post->ID, '_listing_featured_on', true ) ) ? '<p class="wp_listings_featured_on">' . esc_html( get_post_meta( $post->ID, '_listing_featured_on', true ) ) . '</p>' : '';

				if ( get_post_meta( $post->ID, '_listing_disclaimer', true ) ) {
					echo '<p class="wp-listings-disclaimer">' . get_post_meta( $post->ID, '_listing_disclaimer', true ) . '</p>';
				} elseif ( ! empty( $options['wp_listings_global_disclaimer'] ) ) {
					echo '<p class="wp-listings-disclaimer">' . esc_html( $options['wp_listings_global_disclaimer'] ) . '</p>';
				}

				if ( class_exists( 'Idx_Broker_Plugin' ) && ! empty( $options['wp_listings_display_idx_link'] ) && get_post_meta( $post->ID, '_listing_details_url', true ) ) {
					echo '<a href="' . esc_attr( get_post_meta( $post->ID, '_listing_details_url', true ) ) . '" title="' . esc_attr( get_post_meta( $post->ID, '_listing_mls', true ) ) . '" class="listing-full-details-link">View full listing details</a>';
				}
				?>
			</div><!-- #listing-description -->

			<div id="listing-details">
				<?php

				$details_instance = new WP_Listings();

				echo '<table class="listing-details core-fields">';

				echo '<tbody class="left">';
				if ( get_post_meta( $post->ID, '_listing_hide_price', true ) == 1 ) {
					echo ( get_post_meta( $post->ID, '_listing_price_alt', true ) ) ? '<tr class="wp_listings_listing_price"><td class="label">' . esc_html__( 'Price:', 'wp-listings' ) . '</td><td>' . esc_html( get_post_meta( $post->ID, '_listing_price_alt', true ) ) . '</td></tr>' : '';
				} elseif ( get_post_meta( $post->ID, '_listing_price', true ) ) {
					$currency_symbol = ( empty( $options['wp_listings_currency_symbol'] ) || 'none' === $options['wp_listings_currency_symbol'] ) ? '' : $options['wp_listings_currency_symbol'];
					$currency_code   = ( ! empty( $options['wp_listings_display_currency_code'] ) && ! empty( $options['wp_listings_display_currency_code'] ) && 'none' !== $options['wp_listings_currency_code'] ) ? $options['wp_listings_currency_code'] : '';
					echo '<tr class="wp_listings_listing_price"><td class="label">' . esc_html__( 'Price:', 'wp-listings' ) . '</td><td><span class="currency-symbol">' . esc_html( $currency_symbol ) . '</span>';
					echo esc_html( get_post_meta( $post->ID, '_listing_price', true ) ) . ' <span class="currency-code">' . esc_html( $currency_code ) . '</span>';
					echo '</td></tr>';
				}
				echo '<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
				echo ( get_post_meta( $post->ID, '_listing_address', true ) ) ? '<tr class="wp_listings_listing_address"><td class="label">' . esc_html__( 'Address:', 'wp-listings' ) . '</td><td itemprop="streetAddress">' . esc_html( get_post_meta( $post->ID, '_listing_address', true ) ) . '</td></tr>' : '';
				echo ( get_post_meta( $post->ID, '_listing_city', true ) ) ? '<tr class="wp_listings_listing_city"><td class="label">' . esc_html__( 'City:', 'wp-listings' ) . '</td><td itemprop="addressLocality">' . esc_html( get_post_meta( $post->ID, '_listing_city', true ) ) . '</td></tr>' : '';
				echo ( get_post_meta( $post->ID, '_listing_county', true ) ) ? '<tr class="wp_listings_listing_county"><td class="label">' . esc_html__( 'County:', 'wp-listings' ) . '</td><td>' . esc_html( get_post_meta( $post->ID, '_listing_county', true ) ) . '</td></tr>' : '';
				echo ( get_post_meta( $post->ID, '_listing_state', true ) ) ? '<tr class="wp_listings_listing_state"><td class="label">' . esc_html__( 'State:', 'wp-listings' ) . '</td><td itemprop="addressRegion">' . esc_html( get_post_meta( $post->ID, '_listing_state', true ) ) . '</td></tr>' : '';
				echo ( get_post_meta( $post->ID, '_listing_zip', true ) ) ? '<tr class="wp_listings_listing_zip"><td class="label">' . esc_html__( 'Zip Code:', 'wp-listings' ) . '</td><td itemprop="postalCode">' . esc_html( get_post_meta( $post->ID, '_listing_zip', true ) ) . '</td></tr>' : '';
				echo ( get_post_meta( $post->ID, '_listing_subdivision', true ) ) ? '<tr class="wp_listings_listing_subdivision"><td class="label">' . esc_html__( 'Subdivision:', 'wp-listings' ) . '</td><td>' . esc_html( get_post_meta( $post->ID, '_listing_subdivision', true ) ) . '</td></tr>' : '';
				echo '</div>';
				echo ( get_post_meta( $post->ID, '_listing_mls', true ) ) ? '<tr class="wp_listings_listing_mls"><td class="label">MLS:</td><td>' . esc_html( get_post_meta( $post->ID, '_listing_mls', true ) ) . '</td></tr>' : '';
				echo '</tbody>';

				echo '<tbody class="right">';
				foreach ( (array) $details_instance->property_details['col2'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta( $post->ID, $key, true ) );
					if ( ! empty( $detail_value ) ) {
						printf( '<tr class="wp_listings%s"><td class="label">%s</td><td>%s</td></tr>', esc_attr( $key ), esc_html( $label ), esc_html( $detail_value ) );
					}
				}
				echo '</tbody>';

				echo '</table>';

				echo '<table class="listing-details extended">';
				echo '<tbody class="left">';
				foreach ( (array) $details_instance->extended_property_details['col1'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta( $post->ID, $key, true ) );
					if ( ! empty( $detail_value ) ) {
						printf( '<tr class="wp_listings%s"><td class="label">%s</td><td>%s</td></tr>', esc_attr( $key ), esc_html( $label ), esc_html( $detail_value ) );
					}
				}
				echo '</tbody>';
				echo '<tbody class="right">';
				foreach ( (array) $details_instance->extended_property_details['col2'] as $label => $key ) {
					$detail_value = esc_html( get_post_meta( $post->ID, $key, true ) );
					if ( ! empty( $detail_value ) ) {
						printf( '<tr class="wp_listings%s"><td class="label">%s</td><td>%s</td></tr>', esc_attr( $key ), esc_html( $label ), esc_html( $detail_value ) );
					}
				}
				echo '</tbody>';
				echo '</table>';

				if ( isset( $options['wp_listings_display_advanced_fields'] ) && $options['wp_listings_display_advanced_fields'] ) {
					$adv_fields = generate_adv_field_list( $post );
					if ( ! empty( $adv_fields['col1'] ) || ! empty( $adv_fields['col2'] ) ) {
						echo '<table class="listing-details advanced">';
						echo '<tbody class="left">';
						foreach ( $adv_fields['col1'] as $key => $value ) {
							if ( ! empty( $value ) ) {
								printf( '<tr class="wp_listings%s"><td class="label">%s</td><td>%s</td></tr>', esc_attr( $key ), esc_html( get_adv_field_display_name( $key ) . ':' ), esc_html( $value ) );
							}
						}
						echo '</tbody>';

						echo '<tbody class="right">';
						foreach ( $adv_fields['col2'] as $key => $value ) {
							if ( ! empty( $value ) ) {
								printf( '<tr class="wp_listings%s"><td class="label">%s</td><td>%s</td></tr>', esc_attr( $key ), esc_html( get_adv_field_display_name( $key ) . ':' ), esc_html( $value ) );
							}
						}
						echo '</tbody>';
						echo '</table>';
					}
				}

				if ( get_the_term_list( get_the_ID(), 'features', '<li>', '</li><li>', '</li>' ) != null ) {
					echo '<h5>' . esc_html__( 'Tagged Features:', 'wp-listings' ) . '</h5><ul class="tagged-features">';
					echo get_the_term_list( get_the_ID(), 'features', '<li>', '</li><li>', '</li>' );
					echo '</ul><!-- .tagged-features -->';
				}

				if ( ! empty( get_post_meta( $post->ID, '_listing_home_sum', true ) ) || ! empty( get_post_meta( $post->ID, '_listing_kitchen_sum', true ) ) || ! empty( get_post_meta( $post->ID, '_listing_living_room', true ) ) || ! empty( get_post_meta( $post->ID, '_listing_master_suite', true ) ) ) {
					?>
					<div class="additional-features">
						<h4>Additional Features</h4>
						<h6 class="label"><?php esc_html_e( 'Home Summary', 'wp-listings' ); ?></h6>
						<p class="value"><?php echo do_shortcode( get_post_meta( $post->ID, '_listing_home_sum', true ) ); ?></p>
						<h6 class="label"><?php esc_html_e( 'Kitchen Summary', 'wp-listings' ); ?></h6>
						<p class="value"><?php echo do_shortcode( get_post_meta( $post->ID, '_listing_kitchen_sum', true ) ); ?></p>
						<h6 class="label"><?php esc_html_e( 'Living Room', 'wp-listings' ); ?></h6>
						<p class="value"><?php echo do_shortcode( get_post_meta( $post->ID, '_listing_living_room', true ) ); ?></p>
						<h6 class="label"><?php esc_html_e( 'Master Suite', 'wp-listings' ); ?></h6>
						<p class="value"><?php echo do_shortcode( get_post_meta( $post->ID, '_listing_master_suite', true ) ); ?></p>
					</div><!-- .additional-features -->
					<?php
				}
				?>

			</div><!-- #listing-details -->

			<?php if ( ! empty( get_post_meta( $post->ID, '_listing_gallery', true ) ) ) { ?>
			<div id="listing-gallery">
				<?php echo do_shortcode( get_post_meta( $post->ID, '_listing_gallery', true ) ); ?>
			</div><!-- #listing-gallery -->
			<?php } ?>

			<?php if ( ! empty( get_post_meta( $post->ID, '_listing_video', true ) ) ) { ?>
			<div id="listing-video">
				<div class="iframe-wrap">
				<?php echo do_shortcode( get_post_meta( $post->ID, '_listing_video', true ) ); ?>
				</div>
			</div><!-- #listing-video -->
			<?php } ?>

			<?php if ( ! empty( get_post_meta( $post->ID, '_listing_school_neighborhood', true ) ) ) { ?>
			<div id="listing-school-neighborhood">
				<p>
				<?php echo do_shortcode( get_post_meta( $post->ID, '_listing_school_neighborhood', true ) ); ?>
				</p>
			</div><!-- #listing-school-neighborhood -->
			<?php } ?>

		</div><!-- #listing-tabs.listing-data -->

		<?php
		if ( get_post_meta( $post->ID, '_listing_map', true ) != '' ) {
			echo '<div id="listing-map"><h3>Location Map</h3>';
			echo do_shortcode( get_post_meta( $post->ID, '_listing_map', true ) );
			echo '</div><!-- .listing-map -->';
		} elseif ( ! empty( $options['wp_listings_gmaps_api_key'] ) && get_post_meta( $post->ID, '_listing_latitude', true ) && get_post_meta( $post->ID, '_listing_longitude', true ) && get_post_meta( $post->ID, '_listing_automap', true ) === 'y' ) {

			$map_info_content = sprintf( '<p style="font-size: 14px; margin-bottom: 0;">%s<br />%s %s, %s</p>', get_post_meta( $post->ID, '_listing_address', true ), get_post_meta( $post->ID, '_listing_city', true ), get_post_meta( $post->ID, '_listing_state', true ), get_post_meta( $post->ID, '_listing_zip', true ) );

			( $options['wp_listings_gmaps_api_key'] ) ? $map_key = $options['wp_listings_gmaps_api_key'] : $map_key = '';
			wp_enqueue_script( 'idxb-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key, [], '1.0', false );
			echo '
			<script>
				function initialize() {
					var mapCanvas = document.getElementById(\'map-canvas\');
					var myLatLng = new google.maps.LatLng(' . esc_js( get_post_meta( $post->ID, '_listing_latitude', true ) ) . ', ' . esc_js( get_post_meta( $post->ID, '_listing_longitude', true ) ) . ')
					var mapOptions = {
						center: myLatLng,
						zoom: 14,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					}

					var marker = new google.maps.Marker({
						position: myLatLng,
						icon: \'//s3.amazonaws.com/ae-plugins/wp-listings/images/active.png\'
					});

					var infoContent = \' ' . esc_js( $map_info_content ) . ' \';

					var infowindow = new google.maps.InfoWindow({
						content: infoContent
					});

					var map = new google.maps.Map(mapCanvas, mapOptions);

					marker.setMap(map);

					infowindow.open(map, marker);
				}
				google.maps.event.addDomListener(window, \'load\', initialize);
			</script>
			';
			echo '<div id="listing-map"><h3>Location Map</h3><div id="map-canvas" style="width: 100%; height: 350px;"></div></div><!-- .listing-map -->';
		}
		?>

		<?php
		if ( function_exists( '_p2p_init' ) && function_exists( 'agent_profiles_init' ) ) {
			if ( impa_has_listings( $post->ID ) ) {
				echo '<div id="listing-agent">';
				echo '<div class="connected-agents">';
				aeprofiles_connected_agents_markup();
				echo '</div></div><!-- .listing-agent -->';
			}
		} elseif ( function_exists( '_p2p_init' ) && function_exists( 'impress_agents_init' ) ) {
			if ( impa_has_listings( $post->ID ) ) {
				echo '<div id="listing-agent">';
				echo '<div class="connected-agents">';
				impa_connected_agents_markup();
				echo '</div></div><!-- .listing-agent -->';
			}
		}
		?>

		<div id="listing-contact">

			<?php

			if ( get_post_meta( $post->ID, '_listing_contact_form', true ) != '' ) {
				echo do_shortcode( get_post_meta( $post->ID, '_listing_contact_form', true ) );
			} elseif ( ! empty( $options['wp_listings_default_form'] ) ) {
				echo do_shortcode( $options['wp_listings_default_form'] );
			} else {
				include_once IMPRESS_IDX_DIR . 'add-ons/listings/includes/listing-templates/listing-inquiry-form.php';
				listing_inquiry_form( $post );
			}

			?>
		</div><!-- .listing-contact -->

	</div><!-- .entry-content -->

	<?php
}

if ( function_exists( 'equity' ) ) {

	remove_action( 'equity_entry_header', 'equity_post_info', 12 );
	remove_action( 'equity_entry_footer', 'equity_post_meta' );

	remove_action( 'equity_entry_content', 'equity_do_post_content' );
	add_action( 'equity_entry_content', 'single_listing_post_content' );

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
	add_action( 'genesis_entry_content', 'single_listing_post_content' ); // HTML5.
	add_action( 'genesis_post_content', 'single_listing_post_content' ); // XHTML.

	genesis();

} else {

	$options = get_option( 'plugin_wp_listings_settings' );

	get_header();
	if ( isset( $options['wp_listings_custom_wrapper'] ) && ! empty( $options['wp_listings_start_wrapper'] ) ) {
		echo esc_html( $options['wp_listings_start_wrapper'] );
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
				<?php
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
					?>
				<span class="comments-link"><?php comments_popup_link( esc_html__( 'Leave a comment', 'wp-listings' ), esc_html__( '1 Comment', 'wp-listings' ), esc_html__( '% Comments', 'wp-listings' ) ); ?></span>
					<?php
					endif;

					edit_post_link( esc_html__( 'Edit', 'wp-listings' ), '<span class="edit-link">', '</span>' );
				?>
			</div><!-- .entry-meta -->
		</header><!-- .entry-header -->


		<?php single_listing_post_content(); ?>

		</article><!-- #post-ID -->

		<?php
		// Previous/next post navigation.
		wp_listings_post_nav();

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		endwhile;

	if ( isset( $options['wp_listings_custom_wrapper'] ) && ! empty( $options['wp_listings_end_wrapper'] ) ) {
		echo esc_html( $options['wp_listings_end_wrapper'] );
	} else {
		echo '</div><!-- #content -->
		</div><!-- #primary -->';
	}

	get_sidebar();
	get_footer();

}
