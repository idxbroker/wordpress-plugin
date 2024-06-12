<?php
namespace IDX\Widgets;

/**
 * Impress_Carousel_Widget class.
 */
class Impress_Carousel_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		parent::__construct(
			'impress_carousel', // Base ID.
			'IMPress Property Carousel', // Name.
			array(
				'description'                 => 'Displays a carousel of properties',
				'classname'                   => 'impress-carousel-widget',
				'customize_selective_refresh' => true,
			)
		);
		// Include helper functions file.
		include_once IMPRESS_IDX_DIR . 'idx/widgets/impress-widget-helper.php';
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Defaults
	 *
	 * @var mixed
	 * @access public
	 */
	public $defaults = array(
		'title'         => 'Properties',
		'properties'    => 'featured',
		'saved_link_id' => '',
		'agentID'       => '',
		'colistings'    => 1,
		'display'       => 3,
		'max'           => 15,
		'order'         => 'default',
		'autoplay'      => 1,
		'styles'        => 1,
		'new_window'    => 0,
	);

	/**
	 * Returns the markup for the listings
	 *
	 * @param array $instance Previously saved values from database.
	 * @return string $output html markup for front end display
	 */
	public function body( $instance ) {
		wp_enqueue_style( 'owl2-css' );
		wp_enqueue_script( 'owl2' );

		if ( empty( $instance ) ) {
			$instance = $this->defaults;
		}

		$prev_link = apply_filters( 'idx_listing_carousel_prev_link', $idx_listing_carousel_prev_link_text = __( '<i class=\"fas fa-caret-left\" aria-label=\"Previous Listing\"></i><span>Prev</span>', 'idxbroker' ) );
		$next_link = apply_filters( 'idx_listing_carousel_next_link', $idx_listing_carousel_next_link_text = __( '<i class=\"fas fa-caret-right\" aria-label=\"Next Listing\"></i><span>Next</span>', 'idxbroker' ) );

		if ( $instance['styles'] ) {
			wp_enqueue_style( 'impress-carousel' );
			wp_enqueue_style( 'font-awesome-5.8.2' );
		}

		$output = '';
		$properties = [];
		$comingSoon = coming_soon_listing_restriction();
		if ( ! $comingSoon ) {
			if ( ( $instance['properties'] ) === 'savedlinks' ) {
				$properties = $this->idx_api->saved_link_properties( $instance['saved_link_id'] );
				$output    .= '<!-- Saved Link ID: ' . $instance['saved_link_id'] . ' -->';
			} else {
				$properties = $this->idx_api->client_properties( $instance['properties'] );
				$output    .= '<!-- Property Type: ' . $instance['properties'] . ' -->';
			}
			// Force type of array.
			$properties = json_encode( $properties );
			$properties = json_decode( $properties, true );
		}

		// If no properties or an error, load message.
		if ( empty( $properties ) || ( isset( $properties[0] ) && $properties[0] === 'No results returned' ) || isset( $properties['errors']['idx_api_error'] ) ) {
			if ( $comingSoon ) {
				return $output .= '<p>Coming Soon</p>';
			} elseif ( isset( $properties['errors']['idx_api_error'] ) ) {
				return $output .= '<p>' . $properties['errors']['idx_api_error'][0] . '</p>';
			} else {
				return $output .= '<p>No properties found</p>';
			}
		}

		if ( $instance['autoplay'] ) {
			$autoplay = 'autoplay: true,';
		} else {
			$autoplay = '';
		}

		$display = $instance['display'];

		if ( ! isset( $instance['new_window'] ) ) {
			$instance['new_window'] = 0;
		}

		$target = $this->target( $instance['new_window'] );

		if ( 'low-high' == $instance['order'] ) {
			// sort low to high
			usort( $properties, array( $this, 'price_cmp' ) );
		}

		if ( 'high-low' == $instance['order'] ) {
			usort( $properties, array( $this, 'price_cmp' ) );
			$properties = array_reverse( $properties );
		}

		$max = $instance['max'];

		$total = count( $properties );
		$count = 0;

		// The id set on the container and used by the output script to insert the listings into the page for this particular carousel
		$carousel_id = uniqid('impress-carousel-');
		$output .= sprintf( '<div id="%s" class="impress-carousel impress-listing-carousel-%s owl-carousel owl-theme">', $carousel_id, $instance['display'] );

		// Used to hold agent data when matching for co-listings.
		$agent_data;

		foreach ( $properties as $prop ) {

			if ( ! empty( $instance['agentID'] ) ) {
				if ( empty( $prop['userAgentID'] ) || (int) $instance['agentID'] !== (int) $prop['userAgentID'] ) {
					if ( $instance['colistings'] ) {
						// Check if coListingAgentID exists since the initial agent ID match failed.
						if ( array_key_exists( 'coListingAgentID', $prop ) ) {
							// Check if $agent_data is already set, if not grab a new copy to get MLS-provided agent ID.
							if ( empty( $agent_data ) ) {
								$agent_data = $this->idx_api->idx_api( 'agents?filterField=agentID&filterValue=' . $instance['agentID'], IDX_API_DEFAULT_VERSION, 'clients', [], 7200, 'GET', true );
							}
							// Check the listing's coListingAgentID against the agent's raw MLS-provided ID, continues if no match.
							if ( empty( $agent_data['agent'][0]['listingAgentID'] ) || $agent_data['agent'][0]['listingAgentID'] !== $prop['coListingAgentID'] ) {
								continue;
							}
						} else {
							// Listing does not have coListingAgentID field data to match against.
							continue;
						}
					} else {
						// Colistings setting is not enabled.
						continue;
					}
				}
			}

			if ( ! empty( $max ) && $count == $max ) {
				break;
			}

			$prop_image_url = $prop['image']['0']['url'] ?? $prop['image']['1']['url'] ?? plugins_url( '/idx-broker-platinum/assets/images/noPhotoFull.png' );
			$image_alt_tag  = apply_filters( 'impress_carousel_image_alt_tag', esc_html( $prop['address'] ), $prop );

			$count++;

			$prop = $this->set_missing_core_fields( $prop );

			// Get URL and add suffix if one exists.
			if ( isset( $prop['fullDetailsURL'] ) ) {
				$url = $prop['fullDetailsURL'];
			} else {
				$url = $this->idx_api->details_url() . '/' . $prop['detailsURL'];
			}

			if ( has_filter( 'impress_carousel_property_url_suffix' ) ) {
				$url = $url . apply_filters( 'impress_carousel_property_url_suffix', $suffix = http_build_query( array() ), $prop, $this->idx_api );
			}

			$output .= apply_filters(
				'impress_carousel_property_html',
				sprintf(
					'<div class="impress-carousel-property">
					<a href="%2$s" class="impress-carousel-photo" target="%16$s">
						<img class="lazyOwl owl-lazy" data-src="%3$s" alt="%4$s" title="%5$s %6$s %7$s %8$s %9$s, %10$s" />
						<span class="impress-price">%1$s</span>
					</a>
					<a href="%2$s" target="%16$s">
						<p class="impress-address">
							<span class="impress-street">%5$s %6$s %7$s %8$s</span>
							<span class="impress-cityname">%9$s</span>,
							<span class="impress-state"> %10$s</span>
						</p>
					</a>
					<p class="impress-beds-baths-sqft">
						%11$s
						%12$s
						%13$s
						%14$s
					</p>
					%15$s
					</div><!-- end .impress-carousel-property -->',
					esc_html( price_selector( $prop ) ),
					esc_url( $url ),
					esc_url( $prop_image_url ),
					esc_attr( $image_alt_tag ),
					esc_html( $prop['streetNumber'] ),
					esc_html( $prop['streetDirection'] ),
					esc_html( $prop['streetName'] ),
					esc_html( $prop['unitNumber'] ),
					esc_html( $prop['cityName'] ),
					esc_html( $prop['state'] ),
					$this->hide_empty_fields( 'beds', 'Beds', $prop['bedrooms'] ),
					$this->hide_empty_fields( 'baths', 'Baths', $prop['totalBaths'] ),
					$this->hide_empty_fields( 'sqft', 'SqFt', $prop['sqFt'] ),
					$this->hide_empty_fields( 'acres', 'Acres', $prop['acres'] ),
					wp_kses_post( $this->maybe_add_disclaimer_and_courtesy( $prop ) ),
					esc_attr( $target )
				),
				$prop,
				$instance,
				$url,
				$this->maybe_add_disclaimer_and_courtesy( $prop )
			);
		}

		$output = '
			<script>
				window.addEventListener("DOMContentLoaded", function(event) {
					jQuery("#' . $carousel_id . '").owlCarousel({
						items: ' . $display . ',
						' . $autoplay . '
						nav: true,
						navText: ["' . $prev_link . '", "' . $next_link . '"],
						loop: true,
						lazyLoad: true,
						addClassActive: true,
						itemsScaleUp: true,
						navContainerClass: "owl-controls owl-nav",
						responsiveClass:true,
						responsive:{
							0:{
								items: 1,
								nav: true,
								margin: 0
							},
							450:{
								items: ' . round( $display / 2 ) . ',
								loop: ' . ( round( $display / 2 ) < $count ? 'true' : 'false' ) . '
							},
							800:{
								items: ' . $display . ',
								loop: ' . ( $display < $count ? 'true' : 'false' ) . '
							}
						}
					});
				});
			</script>
		' . $output;

		$output .= '</div><!-- end .impress-carousel -->';

		return $output;
	}

	// Hide fields that have no data to avoid fields such as 0 Baths from displaying.
	public function hide_empty_fields( $field, $display_name, $value ) {
		if ( $value <= 0 ) {
			return '';
		} else {
			return '<span class="impress-' . esc_attr( $field ) . '">' . esc_html( $value ) . ' ' . esc_html( $display_name ) . '</span> ';
		}
	}

	/**
	 * Target
	 *
	 * @access public
	 * @param mixed $new_window - New Window settings value.
	 * @return string
	 */
	public function target( $new_window ) {
		if ( ! empty( $new_window ) ) {
			// if enabled, open links in new tab/window.
			return '_blank';
		} else {
			return '_self';
		}
	}

	/**
	 * Set_missing_core_fields
	 *
	 * @access public
	 * @param mixed $prop - Listing data.
	 * @return array
	 */
	public function set_missing_core_fields( $prop ) {
		$name_values   = array(
			'image',
			'remarksConcat',
			'detailsURL',
			'streetNumber',
			'streetName',
			'streetDirection',
			'unitNumber',
			'cityName',
			'state',
		);
		$number_values = array(
			'listingPrice',
			'bedrooms',
			'totalBaths',
			'sqFt',
			'acres',
		);
		foreach ( $name_values as $field ) {
			if ( empty( $prop[ $field ] ) ) {
				$prop[ $field ] = '';
			}
		}
		foreach ( $number_values as $field ) {
			if ( empty( $prop[ $field ] ) ) {
				$prop[ $field ] = 0;
			}
		}
		return $prop;

	}

	/**
	 * Compares the price fields of two arrays
	 *
	 * @param array $a - Listing 1.
	 * @param array $b - Listing 2.
	 * @return int
	 */
	public function price_cmp( $a, $b ) {

		$a = $this->clean_price( $a['listingPrice'] );
		$b = $this->clean_price( $b['listingPrice'] );

		return $a <=> $b;
	}

	/**
	 * Removes the "$" and "," from the price field
	 *
	 * @param string $price - Price string.
	 * @return string $price - Cleaned price string
	 */
	public function clean_price( $price ) {

		$patterns = array(
			'/\$/',
			'/,/',
		);

		$price = preg_replace( $patterns, '', $price );

		return $price;
	}

	/**
	 * Echos saved link names wrapped in option tags
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public static function saved_link_options( $instance, $idx_api ) {
		$saved_links = $idx_api->idx_api_get_savedlinks();

		if ( ! is_array( $saved_links ) ) {
			return;
		}

		foreach ( $saved_links as $saved_link ) {

			// Display the link name if no link title has been assigned.
			$link_text = $saved_link->linkTitle ?? $saved_link->linkName;

			echo '<option ' . selected( $instance['saved_link_id'], $saved_link->id, 0 ) . ' value="' . esc_attr( $saved_link->id ) . '">' . esc_html( $link_text ) . '</option>';

		}
	}

	/**
	 * Front-end display of widget
	 *
	 * @see WP_Widget::widget()
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $args );

		if ( empty( $instance ) ) {
			$instance = $this->defaults;
		}

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		echo $this->body( $instance );

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		// Merge defaults and new_instance to avoid any missing index warnings when used with the legacy block widget.
		$new_instance              = array_merge( $this->defaults, $new_instance );
		$instance                  = array();
		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['properties']    = strip_tags( $new_instance['properties'] );
		$instance['saved_link_id'] = (int) $new_instance['saved_link_id'];
		$instance['agentID']       = (int) $new_instance['agentID'];
		$instance['colistings']    = (bool) $new_instance['colistings'];
		$instance['display']       = (int) $new_instance['display'];
		$instance['max']           = (int) $new_instance['max'];
		$instance['order']         = strip_tags( $new_instance['order'] );
		$instance['autoplay']      = strip_tags( $new_instance['autoplay'] );
		$instance['styles']        = strip_tags( $new_instance['styles'] );
		$instance['new_window']    = strip_tags( $new_instance['new_window'] );

		return $instance;
	}

	/**
	 * Back-end widget form
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$idx_api = $this->idx_api;

		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'properties' ) ); ?>"><?php esc_html_e( 'Properties to Display:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'properties' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'properties' ) ); ?>">
				<option <?php selected( $instance['properties'], 'featured' ); ?> value="featured"><?php esc_html_e( 'Featured', 'idxbroker' ); ?></option>
				<option <?php selected( $instance['properties'], 'soldpending' ); ?> value="soldpending"><?php esc_html_e( 'Sold/Pending', 'idxbroker' ); ?></option>
				<option <?php selected( $instance['properties'], 'supplementalactive' ); ?> value="supplementalactive"><?php echo 'Active Supplemental'; ?></option>
				<option <?php selected( $instance['properties'], 'supplementalsoldpending' ); ?> value="supplementalsoldpending"><?php echo 'Sold/Pending Supplemental'; ?></option>
				<option <?php selected( $instance['properties'], 'supplementalall' ); ?> value="supplementalall"><?php echo 'All Supplemental'; ?></option>
				<option <?php selected( $instance['properties'], 'savedlinks' ); ?> value="savedlinks"><?php esc_html_e( 'Use Saved Link', 'idxbroker' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'saved_link_id' ) ); ?>">Choose a saved link (if selected above):</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'saved_link_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'saved_link_id' ) ); ?>">
				<?php $this->saved_link_options( $instance, $this->idx_api ); ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'agentID' ) ); ?>"><?php esc_html_e( 'Limit by Agent:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'agentID' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'agentID' ) ); ?>">
				<?php $this->idx_api->get_agents_select_list( $instance['agentID'] ); ?>
			</select>
		</p>

		<p>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'colistings' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'colistings' ) ); ?>" value="1" <?php checked( $instance['colistings'], true ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'colistings' ) ); ?>"><?php esc_html_e( 'Include colistings for selected agent?', 'idxbroker' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>"><?php esc_html_e( 'Listings to show without scrolling:', 'idxbroker' ); ?></label>
			<input class="widefat" type="number" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>" value="<?php echo esc_attr( $instance['display'] ); ?>" size="3">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>"><?php esc_html_e( 'Max number of listings to show:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'max' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max' ) ); ?>" type="number" value="<?php echo esc_attr( $instance['max'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Sort order:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<option <?php selected( $instance['order'], 'default' ); ?> value="default"><?php esc_html_e( 'Default', 'idxbroker' ); ?></option>
				<option <?php selected( $instance['order'], 'high-low' ); ?> value="high-low"><?php esc_html_e( 'Highest to Lowest Price', 'idxbroker' ); ?></option>
				<option <?php selected( $instance['order'], 'low-high' ); ?> value="low-high"><?php esc_html_e( 'Lowest to Highest Price', 'idxbroker' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'autoplay' ) ); ?>"><?php esc_html_e( 'Autoplay?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'autoplay' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'autoplay' ) ); ?>" value="1" <?php checked( $instance['autoplay'], true ); ?>>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>"><?php esc_html_e( 'Default Styling?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'styles' ) ); ?>" value="1" <?php checked( $instance['styles'], true ); ?>>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>"><?php esc_html_e( 'Open Listings in a New Window?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_window' ) ); ?>" value="1" <?php checked( $instance['new_window'], true ); ?>>
		</p>

		<?php
	}

	/**
	 * Output disclaimer and courtesy if applicable
	 *
	 * @param  array $prop The current property in the loop.
	 * @return string HTML of disclaimer, logo, and courtesy.
	 */
	public function maybe_add_disclaimer_and_courtesy( $prop ) {
		// Add Disclaimer when applicable.
		if ( isset( $prop['disclaimer'] ) && ! empty( $prop['disclaimer'] ) ) {
			foreach ( $prop['disclaimer'] as $disclaimer ) {
				if ( in_array( 'widget', $disclaimer ) ) {
					$disclaimer_text = $disclaimer['text'];
					$disclaimer_logo = $disclaimer['logoURL'];
				}
			}
		}
		// Add Courtesy when applicable.
		if ( isset( $prop['courtesy'] ) && ! empty( $prop['courtesy'] ) ) {
			foreach ( $prop['courtesy'] as $courtesy ) {
				if ( in_array( 'widget', $courtesy ) ) {
					$courtesy_text = $courtesy['text'];
				}
			}
		}

		$output = '';

		if ( isset( $disclaimer_text ) ) {
			$output .= '<p style="display: block !important; visibility: visible !important; opacity: 1 !important; position: static !important;">' . $disclaimer_text . '</p>';
		}
		if ( isset( $disclaimer_logo ) ) {
			$output .= '<img class="logo" src="' . $disclaimer_logo . '" style="opacity: 1 !important; position: static !important;" />';
		}
		if ( isset( $courtesy_text ) ) {
			$output .= '<p class="courtesy" style="display: block !important; visibility: visible !important;">' . $courtesy_text . '</p>';
		}

		if ( empty( $output ) ) {
			return;
		} else {
			return '<div class="disclaimer">' . $output . '</div>';
		}
	}
}
