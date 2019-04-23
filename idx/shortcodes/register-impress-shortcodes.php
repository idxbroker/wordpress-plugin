<?php
namespace IDX\Shortcodes;

/**
 * Register_Impress_Shortcodes class.
 */
class Register_Impress_Shortcodes {

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		add_shortcode( 'impress_lead_login', array( $this, 'lead_login_shortcode' ) );
		if ( $this->idx_api->platinum_account_type() ) {
			add_action( 'wp_loaded', array( $this, 'lead_signup_shortcode' ) );
		}
		add_shortcode( 'impress_property_showcase', array( $this, 'property_showcase_shortcode' ) );
		add_shortcode( 'impress_property_carousel', array( $this, 'property_carousel_shortcode' ) );
		add_shortcode( 'impress_city_links', array( $this, 'city_links_shortcode' ) );

	}


	/**
	 * lead_login_shortcode function.
	 *
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	public function lead_login_shortcode( $atts ) {
		extract(
			shortcode_atts(
				array(
					'styles'         => 1,
					'new_window'     => 0,
					'password_field' => false,
				),
				$atts
			)
		);

		if ( ! empty( $styles ) ) {
			wp_enqueue_style( 'impress-lead-login', plugins_url( '../assets/css/widgets/impress-lead-login.css', dirname( __FILE__ ) ) );
		}

		if ( ! isset( $new_window ) ) {
			$new_window = 0;
		}

		$target = $this->target( $new_window );

		// Returns hidden if false or not set
		$password_field_type = $password_field ? 'password' : 'hidden';
		$password_label      = $password_field ? '<label for="impress-widgetPassword">Password:</label>' : '';

		$widget = sprintf(
			'
            <form action="%1$sajax/userlogin.php" class="impress-lead-login" method="post" target="%2$s" name="leadLoginForm">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="loginWidget" value="true">
                <label for="impress-widgetEmail">Email Address:</label>
                <input id="impress-widgetEmail" type="text" name="email" placeholder="Enter your email address">
                %3$s
                <input id="impress-widgetPassword" type="%4$s" name="password" placeholder="Password">
                <input id="impress-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
            </form>',
			$this->idx_api->subdomain_url(),
			$target,
			$password_label,
			$password_field_type
		);

		return $widget;
	}

	/**
	 * lead_signup_shortcode function.
	 *
	 * @access public
	 * @return void
	 */
	public function lead_signup_shortcode() {
		new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode();

	}

	/**
	 * property_showcase_shortcode function.
	 *
	 * @access public
	 * @param array $atts (default: array())
	 * @return void
	 */
	public function property_showcase_shortcode( $atts = array() ) {
		extract(
			shortcode_atts(
				array(
					'max'           => 4,
					'use_rows'      => 1,
					'num_per_row'   => 4,
					'show_image'    => 1,
					'order'         => 'default',
					'property_type' => 'featured',
					'saved_link_id' => '',
					'agent_id'      => '',
					'styles'        => 1,
					'new_window'    => 0,
				),
				$atts
			)
		);

		if ( ! empty( $styles ) ) {
			wp_enqueue_style( 'impress-showcase', plugins_url( '../assets/css/widgets/impress-showcase.css', dirname( __FILE__ ) ) );
		}

		$output = '';
		if ( ( $property_type ) === 'savedlinks' ) {
			$properties = $this->idx_api->saved_link_properties( $saved_link_id );
			$output    .= '<!-- Saved Link ID: ' . $saved_link_id . ' -->';
		} else {
			$properties = $this->idx_api->client_properties( $property_type );
			$output    .= '<!-- Property Type: ' . $property_type . ' -->';
		}

		// Force type as Array.
		$properties = json_encode( $properties );
		$properties = json_decode( $properties, true );

		// If no properties or an error, load message
		if ( empty( $properties ) || ( isset( $properties[0] ) && $properties[0] === 'No results returned' ) || isset( $properties['errors']['idx_api_error'] ) ) {
			if ( isset( $properties['errors']['idx_api_error'] ) ) {
				return $output .= '<p>' . $properties['errors']['idx_api_error'][0] . '</p>';
			} else {
				return $output .= '<p>No properties found</p>';
			}
		}

		$total = count( $properties );
		$count = 0;

		$column_class = '';

		if ( 1 == $use_rows ) {
			// Max of four columns
			$number_columns = ( $num_per_row > 4 ) ? 4 : (int) $num_per_row;

			// column class
			switch ( $number_columns ) {
				case 0:
					$column_class = 'columns small-12 large-12';
					break;
				case 1:
					$column_class = 'columns small-12 large-12';
					break;
				case 2:
					$column_class = 'columns small-12 medium-6 large-6';
					break;
				case 3:
					$column_class = 'columns small-12 medium-4 large-4';
					break;
				case 4:
					$column_class = 'columns small-12 medium-3 large-3';
					break;
			}
		}

		if ( ! isset( $new_window ) ) {
			$new_window = 0;
		}

		$target = $this->target( $new_window );

		if ( 'low-high' == $order ) {
			// sort low to high
			usort( $properties, array( $this->idx_api, 'price_cmp' ) );
		}

		if ( 'high-low' == $order ) {
			usort( $properties, array( $this->idx_api, 'price_cmp' ) );
			$properties = array_reverse( $properties );
		}

		foreach ( $properties as $prop ) {

			if ( isset( $agent_id, $prop['userAgentID'] ) && ! empty( $agent_id ) ) {
				if ( (int) $agent_id !== (int) $prop['userAgentID'] ) {
					continue;
				}
			}

			if ( ! empty( $max ) && $count == $max ) {
				return $output;
			}

			$prop_image_url = ( isset( $prop['image']['0']['url'] ) ) ? $prop['image']['0']['url'] : 'https://s3.amazonaws.com/mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png';

			if ( 1 == $use_rows && $count == 0 && $max != '1' ) {
				$output .= '<div class="shortcode impress-property-showcase impress-row">';
			}

			if ( empty( $prop['propStatus'] ) ) {
				$prop['propStatus'] = 'none';
			}

			$count++;

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

			$prop = $this->set_missing_core_fields( $prop );

			// Get URL and add suffix if one exists
			if ( isset( $prop['fullDetailsURL'] ) ) {
				$url = $prop['fullDetailsURL'];
			} else {
				$url = $this->idx_api->details_url() . '/' . $prop['detailsURL'];
			}

			if ( has_filter( 'impress_showcase_property_url_suffix' ) ) {
				$url = $url . apply_filters( 'impress_showcase_property_url_suffix', $suffix = http_build_query( array() ), $prop, $this->idx_api );
			}

			if ( 1 == $show_image ) {
				$output .= apply_filters(
					'impress_showcase_property_html',
					sprintf(
						'<div class="impress-showcase-property %17$s">
                        <a href="%3$s" class="impress-showcase-photo" target="%18$s">
                            <img src="%4$s" alt="%5$s" title="%6$s %7$s %8$s %9$s %10$s, %11$s" />
                            <span class="impress-price">%1$s</span>
                            <span class="impress-status">%2$s</span>
                            <p class="impress-address">
                                <span class="impress-street">%6$s %7$s %8$s %9$s</span>
                                <span class="impress-cityname">%10$s</span>,
                                <span class="impress-state"> %11$s</span>
                            </p>
                        </a>
                        <p class="impress-beds-baths-sqft">
                        %12$s
                        %13$s
                        %14$s
                        %15$s
                        </p>
                        %16$s
                        </div>',
						$prop['listingPrice'],
						$prop['propStatus'],
						$url,
						$prop_image_url,
						htmlspecialchars( $prop['remarksConcat'] ),
						$prop['streetNumber'],
						$prop['streetDirection'],
						$prop['streetName'],
						$prop['unitNumber'],
						$prop['cityName'],
						$prop['state'],
						$this->hide_empty_fields( 'beds', 'Beds', $prop['bedrooms'] ),
						$this->hide_empty_fields( 'baths', 'Baths', $prop['totalBaths'] ),
						$this->hide_empty_fields( 'sqft', 'SqFt', $prop['sqFt'] ),
						$this->hide_empty_fields( 'acres', 'Acres', $prop['acres'] ),
						$this->maybe_add_disclaimer_and_courtesy( $prop ),
						$column_class,
						$target
					),
					$prop,
					$instance,
					$url,
					$prop_image_url,
					$this->maybe_add_disclaimer_and_courtesy( $prop ),
					$column_class,
					$target
				);
			} else {
				$output .= apply_filters(
					'impress_showcase_property_list_html',
					sprintf(
						'<li class="impress-showcase-property-list %13$s">
                        <a href="%2$s" target="%14$s">
                            <p>
                                <span class="impress-price">%1$s</span>
                                <span class="impress-address">
                                    <span class="impress-street">%3$s %4$s %5$s %6$s</span>
                                    <span class="impress-cityname">%7$s</span>,
                                    <span class="impress-state"> %8$s</span>
                                </span>
                                <span class="impress-beds-baths-sqft">
                                    %9$s
                                    %10$s
                                    %11$s
                                    %12$s
                                </span>
                            </p>
                        </a>
                    </li>',
						$prop['listingPrice'],
						$url,
						$prop['streetNumber'],
						$prop['streetDirection'],
						$prop['streetName'],
						$prop['unitNumber'],
						$prop['cityName'],
						$prop['state'],
						$this->hide_empty_fields( 'beds', 'Beds', $prop['bedrooms'] ),
						$this->hide_empty_fields( 'baths', 'Baths', $prop['totalBaths'] ),
						$this->hide_empty_fields( 'sqft', 'SqFt', $prop['sqFt'] ),
						$this->hide_empty_fields( 'acres', 'Acres', $prop['acres'] ),
						$column_class,
						$target
					),
					$prop,
					$instance,
					$url,
					$column_class,
					$target
				);
			}

			if ( 1 == $use_rows && $count != 1 ) {

				// close a row if..
				// num_per_row is a factor of count OR
				// count is equal to the max number of listings to show OR
				// count is equal to the total number of listings available
				if ( $count % $num_per_row == 0 || $count == $total || $count == $max ) {
					$output .= '</div> <!-- .impress-row -->';
				}

				// open a new row if..
				// num per row is a factor of count AND
				// count is not equal to max AND
				// count is not equal to total
				if ( $count % $num_per_row == 0 && $count != $max && $count != $total ) {
					$output .= '<div class="impress-row shortcode impress-property-showcase">';
				}
			}
		}

		return $output;

	}

	// Hide fields that have no data to avoid fields such as 0 Baths from displaying
	public function hide_empty_fields( $field, $display_name, $value ) {
		if ( $value <= 0 ) {
			return '';
		} else {
			return "<span class=\"impress-$field\">$value $display_name</span> ";
		}
	}

	/**
	 * set_missing_core_fields function.
	 *
	 * @access public
	 * @param mixed $prop
	 * @return void
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
	 * target function.
	 *
	 * @access public
	 * @param mixed $new_window
	 * @return void
	 */
	public function target( $new_window ) {
		if ( ! empty( $new_window ) ) {
			// if enabled, open links in new tab/window
			return '_blank';
		} else {
			return '_self';
		}
	}

	/**
	 * Output disclaimer and courtesy if applicable
	 *
	 * @param  array $prop The current property in the loop
	 * @return string       HTML of disclaimer, logo, and courtesy
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

		if ( $output !== '' ) {
			return '<div class="disclaimer">' . $output . '</div>';
		}
	}

	/**
	 * property_carousel_shortcode function.
	 *
	 * @access public
	 * @param array $atts (default: array())
	 * @return void
	 */
	public function property_carousel_shortcode( $atts = array() ) {
		wp_enqueue_style( 'font-awesome-4.7.0', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );

		extract(
			shortcode_atts(
				array(
					'max'           => 4,
					'display'       => 3,
					'autoplay'      => 1,
					'order'         => 'default',
					'property_type' => 'featured',
					'saved_link_id' => '',
					'agent_id'      => '',
					'styles'        => 1,
					'new_window'    => 0,
				),
				$atts
			)
		);

		wp_enqueue_style( 'owl2-css', plugins_url( '../assets/css/widgets/owl2.carousel.css', dirname( __FILE__ ) ) );
		wp_enqueue_script('owl2', plugins_url('../assets/js/owl2.carousel.min.js', dirname(__FILE__)), array('jquery'), NULL, false);

		if ( $styles ) {
			wp_enqueue_style( 'impress-carousel', plugins_url( '../assets/css/widgets/impress-carousel.css', dirname( __FILE__ ) ) );
		}

		if ( ! isset( $new_window ) ) {
			$new_window = 0;
		}

		$target = $this->target( $new_window );

		$prev_link = apply_filters( 'idx_listing_carousel_prev_link', $idx_listing_carousel_prev_link_text = __( '<i class=\"fa fa-caret-left\"></i><span>Prev</span>', 'idxbroker' ) );
		$next_link = apply_filters( 'idx_listing_carousel_next_link', $idx_listing_carousel_next_link_text = __( '<i class=\"fa fa-caret-right\"></i><span>Next</span>', 'idxbroker' ) );

		$output = '';
		if ( ( $property_type ) === 'savedlinks' ) {
			$properties = $this->idx_api->saved_link_properties( $saved_link_id );
			$output    .= '<!-- Saved Link ID: ' . $saved_link_id . ' -->';
		} else {
			$properties = $this->idx_api->client_properties( $property_type );
			$output    .= '<!-- Property Type: ' . $property_type . ' -->';
		}

		// Force type as array.
		$properties = json_encode( $properties );
		$properties = json_decode( $properties, true );

		// If no properties or an error, load message
		if ( empty( $properties ) || ( isset( $properties[0] ) && $properties[0] === 'No results returned' ) || isset( $properties['errors']['idx_api_error'] ) ) {
			if ( isset( $properties['errors']['idx_api_error'] ) ) {
				return $output .= '<p>' . $properties['errors']['idx_api_error'][0] . '</p>';
			} else {
				return $output .= '<p>No properties found</p>';
			}
		}

		if ( 'low-high' == $order ) {
			// sort low to high
			usort( $properties, array( $this->idx_api, 'price_cmp' ) );
		}

		if ( 'high-low' == $order ) {
			usort( $properties, array( $this->idx_api, 'price_cmp' ) );
			$properties = array_reverse( $properties );
		}

		if ( $autoplay == 1 ) {
			$autoplay_param = 'autoplay: true,';
		} else {
			$autoplay_param = '';
		}

		// All Instance Values are strings for shortcodes but not widgets.
		$output .= '
            <script>
              window.addEventListener("DOMContentLoaded", (event) => {
                jQuery(".impress-listing-carousel-' . $display . '").owlCarousel({
                    items: ' . $display . ',
                    ' . $autoplay_param . '
                    nav: true,
                    navText: ["' . $prev_link . '", "' . $next_link . '"],
                    loop: true,
                    lazyLoad: true,
                    addClassActive: true,
                    itemsScaleUp: true,
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
							items: ' . ( round( $display / 2 ) > count( $properties ) ? count( $properties ) : round( $display / 2 ) ) . ',
							  loop: ' . ( round( $display / 2 ) < count( $properties ) ? 'true' : 'false' ) . '
						},
						800:{
							items: ' . ( $display > count( $properties ) ? count( $properties ) : $display ) . ',
							  loop: ' . ( $display < count( $properties ) ? 'true' : 'false' ) . '
						}
                    }
                });
              });
            </script>
            ';

		$count = 0;

		$output .= sprintf( '<div class="impress-carousel impress-listing-carousel-%s impress-carousel-shortcode owl-carousel owl-theme">', $display );

		foreach ( $properties as $prop ) {

			if ( isset( $agent_id, $prop['userAgentID'] ) && ! empty( $agent_id ) ) {
				if ( (int) $agent_id !== (int) $prop['userAgentID'] ) {
					continue;
				}
			}

			if ( ! empty( $max ) && $count == $max ) {
				$output .= '</div><!-- end .impress-listing-carousel -->';
				return $output;
			}

			$prop_image_url = ( isset( $prop['image']['0']['url'] ) ) ? $prop['image']['0']['url'] : 'https://s3.amazonaws.com/mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png';
			$image_alt_tag  = apply_filters( 'impress_carousel_image_alt_tag', esc_html( $prop['address'] ), $prop );

			$count++;

			$prop = $this->set_missing_core_fields( $prop );

			$disclaimer = $this->maybe_add_disclaimer_and_courtesy( $prop );

			// Get URL and add suffix if one exists
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
                        <img class="owl-lazy lazyOwl" data-src="%3$s" alt="%4$s" title="%5$s %6$s %7$s %8$s %9$s, %10$s" />
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
					$prop['listingPrice'],
					$url,
					$prop_image_url,
					$image_alt_tag,
					$prop['streetNumber'],
					$prop['streetDirection'],
					$prop['streetName'],
					$prop['unitNumber'],
					$prop['cityName'],
					$prop['state'],
					$this->hide_empty_fields( 'beds', 'Beds', $prop['bedrooms'] ),
					$this->hide_empty_fields( 'baths', 'Baths', $prop['totalBaths'] ),
					$this->hide_empty_fields( 'sqft', 'SqFt', $prop['sqFt'] ),
					$this->hide_empty_fields( 'acres', 'Acres', $prop['acres'] ),
					$disclaimer,
					$target
				),
				$prop,
				$atts,
				$url,
				$disclaimer
			);
		}

		$output .= '</div><!-- end .impress-carousel -->';

		return $output;
	}

	/**
	 * city_links_shortcode function.
	 *
	 * @access public
	 * @param array $atts (default: array())
	 * @return void
	 */
	public function city_links_shortcode( $atts = array() ) {
		extract(
			shortcode_atts(
				array(
					'city_list'      => 'combinedActiveMLS',
					'mls'            => 'a000',
					'use_columns'    => 1,
					'number_columns' => 4,
					'styles'         => 1,
					'show_count'     => 0,
					'new_window'     => 0,
					'agent_id'       => '',
				),
				$atts
			)
		);

		if ( ! empty( $styles ) ) {
			wp_enqueue_style( 'impress-city-links', plugins_url( '../assets/css/widgets/impress-city-links.css', dirname( __FILE__ ) ) );
		}

		if ( ! isset( $new_window ) ) {
			$new_window = 0;
		}

		if ( ! isset( $mls ) ) {
			$mls = 'a000';
		}

		$target = $this->target( $new_window );

		$city_links  = '<div class="impress-city-links">';
		$city_links .= \IDX\Widgets\Impress_City_Links_Widget::city_list_links( $city_list, $mls, $use_columns, $number_columns, $target, $show_count, $this->idx_api );
		$city_links .= '</div>';

		if ( false == $city_links ) {
			return 'City list ID or MLS ID not found';
		}
		$city_links .= '<style>.impress-city-list-links ul {margin-left: 0;}</style>';
		return $city_links;
	}

	/**
	 * Add support for Shortcake (Shortcode UI)
	 *
	 * @see  https://github.com/fusioneng/Shortcake
	 * @since 1.5
	 */
	public function register_shortcake() {
		if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
			// * Lead Login
			shortcode_ui_register_for_shortcode(
				'lead_login',
				array(
					'label'         => 'Lead Login',
					'listItemImage' => 'dashicons-admin-network',
				)
			);

			// * Lead Signup
			if ( $this->idx_api->platinum_account_type() ) {
				shortcode_ui_register_for_shortcode(
					'lead_signup',
					array(
						'label'         => 'Lead Signup',
						'listItemImage' => 'dashicons-admin-users',
						'attrs'         => array(
							array(
								'label'   => 'Require Phone?',
								'attr'    => 'phone',
								'type'    => 'radio',
								'value'   => 0,
								'options' => array(
									1 => 'Yes',
									0 => 'No',
								),
							),
						),
					)
				);
			}

			// * Property Showcase
			// $saved_links = $this->idx_api->saved_links();
			shortcode_ui_register_for_shortcode(
				'property_showcase',
				array(
					'label'         => 'Property Showcase',
					'listItemImage' => 'dashicons-admin-home',
					'attrs'         => array(
						array(
							'label' => 'Max Number of Listings',
							'attr'  => 'max',
							'type'  => 'number',
							'value' => 8,
						),
						array(
							'label'   => 'Use Rows',
							'attr'    => 'use_rows',
							'type'    => 'radio',
							'value'   => 1,
							'options' => array(
								1 => 'Yes',
								0 => 'No',
							),
						),
						array(
							'label' => 'Number per row',
							'attr'  => 'num_per_row',
							'type'  => 'number',
							'value' => 4,
						),
						array(
							'label'   => 'Order',
							'attr'    => 'order',
							'type'    => 'select',
							'value'   => 'high-low',
							'options' => array(
								'high-low' => 'High to Low',
								'low-high' => 'Low to High',
							),
						),
						array(
							'label'   => 'Show Image',
							'attr'    => 'show_image',
							'type'    => 'radio',
							'value'   => 1,
							'options' => array(
								1 => 'Yes',
								0 => 'No',
							),
						),
						array(
							'label'   => 'Property Type',
							'attr'    => 'property_type',
							'type'    => 'select',
							'value'   => 'featured',
							'options' => array(
								'featured'     => 'Featured',
								'soldpending'  => 'Sold/Pending',
								'historical'   => 'Historical',
								'supplemental' => 'Supplemental',
								'savedlinks'   => 'Saved Link',
							),
						),
						array(
							'label' => 'Saved Link ID',
							'attr'  => 'saved_link_id',
							'type'  => 'text',
							'value' => '',
						),
						array(
							'label' => 'Limit by Agent ID',
							'attr'  => 'agent_id',
							'type'  => 'text',
							'value' => '',
						),
					),
				)
			);

			// * Property Carousel
			shortcode_ui_register_for_shortcode(
				'property_carousel',
				array(
					'label'         => 'Property Carousel',
					'listItemImage' => 'dashicons-admin-home',
					'attrs'         => array(
						array(
							'label' => 'Max Number of Listings',
							'attr'  => 'max',
							'type'  => 'number',
							'value' => 4,
						),
						array(
							'label' => 'Number to Display without scrolling',
							'attr'  => 'display',
							'type'  => 'number',
							'value' => 3,
						),
						array(
							'label'   => 'Order',
							'attr'    => 'order',
							'type'    => 'select',
							'value'   => 'high-low',
							'options' => array(
								'high-low' => 'High to Low',
								'low-high' => 'Low to High',
							),
						),
						array(
							'label'   => 'Autoplay',
							'attr'    => 'autoplay',
							'type'    => 'radio',
							'value'   => 1,
							'options' => array(
								1 => 'Yes',
								0 => 'No',
							),
						),
						array(
							'label'   => 'Property Type',
							'attr'    => 'property_type',
							'type'    => 'select',
							'value'   => 'featured',
							'options' => array(
								'featured'     => 'Featured',
								'soldpending'  => 'Sold/Pending',
								'historical'   => 'Historical',
								'supplemental' => 'Supplemental',
								'savedlinks'   => 'Saved Link',
							),
						),
						array(
							'label' => 'Saved Link ID',
							'attr'  => 'saved_link_id',
							'type'  => 'text',
							'value' => '',
						),
						array(
							'label' => 'Limit by Agent ID',
							'attr'  => 'agent_id',
							'type'  => 'text',
							'value' => '',
						),
					),
				)
			);

			// * City Links
			shortcode_ui_register_for_shortcode(
				'city_links',
				array(
					'label'         => 'City Links',
					'listItemImage' => 'dashicons-editor-ul',
					'attrs'         => array(
						array(
							'label' => 'City List',
							'attr'  => 'city_list',
							'type'  => 'text',
							'value' => 'combinedActiveMLS',
						),
						array(
							'label' => 'MLS ID',
							'attr'  => 'mls',
							'type'  => 'text',
							'value' => 'a000',
						),
						array(
							'label'   => 'Show Number of Listings for each city?',
							'attr'    => 'show_count',
							'type'    => 'radio',
							'value'   => 0,
							'options' => array(
								1 => 'Yes',
								0 => 'No',
							),
						),
						array(
							'label'   => 'Use Columns?',
							'attr'    => 'use_columns',
							'type'    => 'radio',
							'value'   => 1,
							'options' => array(
								1 => 'Yes',
								0 => 'No',
							),
						),
						array(
							'label'   => 'Number of Columns',
							'attr'    => 'number_columns',
							'type'    => 'select',
							'value'   => 4,
							'options' => array(
								2 => '2',
								3 => '3',
								4 => '4',
							),
						),
					),
				)
			);
		}
	}

}
