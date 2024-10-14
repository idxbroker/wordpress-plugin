<?php
namespace IDX\Widgets;

/**
 * Create_Impress_Widgets class.
 */
class Create_Impress_Widgets {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		add_action( 'widgets_init', array( $this, 'register_impress_widgets' ) );

	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Register_impress_widgets function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_impress_widgets() {
		register_widget( '\IDX\Widgets\Impress_Showcase_Widget' );
		register_widget( '\IDX\Widgets\Impress_Carousel_Widget' );
		register_widget( '\IDX\Widgets\Impress_City_Links_Widget' );
		register_widget( '\IDX\Widgets\Impress_Lead_Login_Widget' );
		register_widget( '\IDX\Widgets\Idx_Middleware_Widget' );
		if ( $this->idx_api->platinum_account_type() ) {
			register_widget( '\IDX\Widgets\Impress_Lead_Signup_Widget' );
		}
	}

	/**
	 * Lead_login_shortcode function.
	 *
	 * @access public
	 * @return void
	 */
	public function lead_login_shortcode() {
		echo '
			<form action="' . esc_attr( $idx_api->subdomain_url() ) . 'ajax/userlogin.php" method="post" target="" name="leadLoginForm">
				<input type="hidden" name="action" value="login">
				<input type="hidden" name="loginWidget" value="true">
				<label for="bb-IDX-widgetEmail">Email Address:</label>
				<input id="bb-IDX-widgetEmail" type="text" name="email" placeholder="Enter your email address">
				<input id="bb-IDX-widgetPassword" type="hidden" name="password" value="">
				<input id="bb-IDX-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
			</form>
		';
	}

	/**
	 * lead_signup_shortcode function.
	 *
	 * @access public
	 * @param mixed $atts
	 * @return void
	 */
	public function lead_signup_shortcode( $atts ) {

		extract(
			shortcode_atts(
				array(
					'phone' => 0,
				),
				$atts
			)
		);

		$widget = sprintf(
			'
            <form action="%sajax/usersignup.php" method="post" target="" name="LeadSignup">
                <input type="hidden" name="action" value="addLead">
                <input type="hidden" name="signupWidget" value="true">
                <input type="hidden" name="contactType" value="direct">

                <label id="bb-IDX-widgetfirstName-label" class="ie-only" for="IDX-widgetfirstName">First Name:</label>
                <input id="bb-IDX-widgetfirstName" type="text" name="firstName" placeholder="First Name">

                <label id="bb-IDX-widgetlastName-label" class="ie-only" for="IDX-widgetlastName">Last Name:</label>
                <input id="bb-IDX-widgetlastName" type="text" name="lastName" placeholder="Last Name">

                <label id="bb-IDX-widgetemail-label" class="ie-only" for="IDX-widgetemail">Email:</label>
                <input id="bb-IDX-widgetemail" type="text" name="email" placeholder="Email">',
			$this->idx_api->subdomain_url()
		);

		if ( $phone ) {
			$phone = sanitize_text_field( $phone );
			$widget .= sprintf(
				'
            <label id="bb-IDX-widgetphone-label" class="ie-only" for="IDX-widgetphone">Phone:</label>
            <input id="bb-IDX-widgetphone" type="text" name="phone" placeholder="Phone">'
			);
		}

		$widget .= sprintf(
			'<input id="bb-IDX-widgetsubmit" type="submit" name="submit" value="Sign Up!">
            </form>'
		);

		return $widget;
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
					'order'         => 'high-low',
					'property_type' => 'featured',
					'saved_link_id' => '',
				),
				$atts
			)
		);

		$saved_link_id = sanitize_text_field( $saved_link_id );
		$property_type = sanitize_text_field( $property_type );

		if ( ( $property_type ) == 'savedlink' ) {
			$properties = $this->idx_api->saved_link_properties( $saved_link_id );
		} else {
			$properties = $this->idx_api->client_properties( $property_type );
		}

		if ( empty( $properties ) ) {
			return 'No properties found';
		}

		$total = count( $properties );
		$count = 0;

		$output = '';

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

		// sort low to high
		usort( $properties, array( $idx_api, 'price_cmp' ) );

		if ( 'high-low' == $order ) {
			$properties = array_reverse( $properties );
		}

		foreach ( $properties as $prop ) {

			if ( ! empty( $max ) && $count == $max ) {
				return $output;
			}

			$prop_image_url = $prop['image']['0']['url'] ?? $prop['image']['1']['url'] ?? plugins_url( '/idx-broker-platinum/assets/images/noPhotoFull.png' );

			if ( 1 == $use_rows && $count == 0 && $max != '1' ) {
				$output .= '<div class="shortcode property-showcase row">';
			}

			if ( empty( $prop['propStatus'] ) ) {
				$prop['propStatus'] = 'none';
			}

			$count++;

			if ( 1 == $show_image ) {
				$output .= sprintf(
					'<div class="showcase-property %15$s">
                        <a href="%3$s" class="showcase-photo">
                            <img src="%4$s" alt="%5$s" title="%5$s" />
                            <span class="price">%1$s</span>
                            <span class="status">%2$s</span>
                        </a>
                        <a href="%3$s">
                            <p class="address">
                                <span class="street">%6$s %7$s %8$s %9$s</span>
                                <span class="cityname">%10$s</span>,
                                <span class="state"> %11$s</span>
                            </p>
                        </a>
                        <p class="beds-baths-sqft">
                            <span class="beds">%12$s Beds</span>
                            <span class="baths">%13$s Baths</span>
                            <span class="sqft">%14$s Sq Ft</span>
                        </p>
                    </div>',
					$prop['listingPrice'],
					$prop['propStatus'],
					$idx_api->details_url() . '/' . $prop['detailsURL'],
					$prop_image_url,
					$prop['remarksConcat'],
					$prop['streetNumber'],
					$prop['streetName'],
					$prop['streetDirection'],
					$prop['unitNumber'],
					$prop['cityName'],
					$prop['state'],
					$prop['bedrooms'],
					$prop['totalBaths'],
					$prop['sqFt'],
					$column_class
				);
			} else {
				$output .= sprintf(
					'<li class="showcase-property-list %12$s">
                        <a href="%2$s">
                            <p>
                                <span class="price">%1$s</span>
                                <span class="address">
                                    <span class="street">%3$s %4$s %5$s %6$s</span>
                                    <span class="cityname">%7$s</span>,
                                    <span class="state"> %8$s</span>
                                </span>
                                <span class="beds-baths-sqft">
                                    <span class="beds">%9$s Beds</span>
                                    <span class="baths">%10$s Baths</span>
                                    <span class="sqft">%11$s Sq Ft</span>
                                </span>
                            </p>
                        </a>
                    </li>',
					$prop['listingPrice'],
					$idx_api->details_url() . '/' . $prop['detailsURL'],
					$prop['streetNumber'],
					$prop['streetName'],
					$prop['streetDirection'],
					$prop['unitNumber'],
					$prop['cityName'],
					$prop['state'],
					$prop['bedrooms'],
					$prop['totalBaths'],
					$prop['sqFt'],
					$column_class
				);
			}

			if ( 1 == $use_rows && ( 1 !== $count || 1 === $total ) ) {

				// close a row if..
				// num_per_row is a factor of count OR
				// count is equal to the max number of listings to show OR
				// count is equal to the total number of listings available
				if ( $count % $num_per_row == 0 || $count == $total || $count == $max ) {
					$output .= '</div> <!-- .row -->';
				}

				// open a new row if..
				// num per row is a factor of count AND
				// count is not equal to max AND
				// count is not equal to total
				if ( $count % $num_per_row == 0 && $count != $max && $count != $total ) {
					$output .= '<div class="row impress-row shortcode property-showcase">';
				}
			}
		}

		return $output;

	}

	/**
	 * property_carousel_shortcode function.
	 *
	 * @access public
	 * @param array $atts (default: array())
	 * @return void
	 */
	public function property_carousel_shortcode( $atts = array() ) {

		extract(
			shortcode_atts(
				array(
					'max'           => 4,
					'display'       => 3,
					'autoplay'      => 1,
					'order'         => 'high-low',
					'property_type' => 'featured',
					'saved_link_id' => '',
				),
				$atts
			)
		);

		$display = (int) sanitize_text_field( $display );

		wp_enqueue_style( 'owl-css' );
		wp_enqueue_script( 'owl' );

		$prev_link = apply_filters( 'idx_listing_carousel_prev_link', $idx_listing_carousel_prev_link_text = __( '<i class=\"fas fa-chevron-circle-left\" aria-label=\"Previous Listing\"></i><span>Prev</span>', 'idxbroker' ) );
		$next_link = apply_filters( 'idx_listing_carousel_next_link', $idx_listing_carousel_next_link_text = __( '<i class=\"fas fa-chevron-circle-right\" aria-label=\"Next Listing\"></i><span>Next</span>', 'idxbroker' ) );

		if ( ( $property_type ) == 'savedlink' ) {
			$properties = $this->idx_api->saved_link_properties( $saved_link_id );
		} else {
			$properties = $this->idx_api->client_properties( $property_type );
		}

		if ( empty( $properties ) ) {
			return 'No properties found';
		}

		// sort low to high
		usort( $properties, array( $this->idx_api, 'price_cmp' ) );

		if ( 'high-low' == $order ) {
			$properties = array_reverse( $properties );
		}

		if ( $autoplay == 1 ) {
			$autoplay_param = 'autoPlay: true,';
		} else {
			$autoplay_param = '';
		}

		if ( $display === 1 ) {
			$output = '
            <script>
            jQuery(function( $ ){
                jQuery(".equity-listing-carousel-' . $display . '").owlCarousel({
                    singleItem: true,
                    ' . $autoplay_param . '
                    navigation: true,
                    navigationText: ["' . $prev_link . '", "' . $next_link . '"],
                    pagination: false,
                    lazyLoad: true,
                    addClassActive: true,
                    itemsScaleUp: true
                });
            });
            </script>
            ';
		} else {
			$output = '
            <script>
            jQuery(function( $ ){
                jQuery(".equity-listing-carousel-' . $display . '").owlCarousel({
                    items: ' . $display . ',
                    ' . $autoplay_param . '
                    navigation: true,
                    navigationText: ["' . $prev_link . '", "' . $next_link . '"],
                    pagination: false,
                    lazyLoad: true,
                    addClassActive: true,
                    itemsScaleUp: true
                });
            });
            </script>
            ';
		}

		$count = 0;

		$output .= sprintf( '<div class="equity-idx-carousel equity-listing-carousel-%s carousel-shortcode">', $display );

		foreach ( $properties as $prop ) {

			if ( ! empty( $max ) && $count == $max ) {
				$output .= '</div><!-- end .equity-listing-carousel -->';
				return $output;
			}

			$prop_image_url = $prop['image']['0']['url'] ?? $prop['image']['1']['url'] ?? plugins_url( '/idx-broker-platinum/assets/images/noPhotoFull.png' );

			$count++;

			$output .= sprintf(
				'<div class="carousel-property">
                    <a href="%2$s" class="carousel-photo">
                        <img class="lazyOwl" data-src="%3$s" alt="%4$s" title="%4$s" />
                        <span class="price">%1$s</span>
                    </a>
                    <a href="%2$s">
                        <p class="address">
                            <span class="street">%5$s %6$s %7$s %8$s</span>
                            <span class="cityname">%9$s</span>,
                            <span class="state"> %10$s</span>
                        </p>
                    </a>
                    <p class="beds-baths-sqft">
                        <span class="beds">%11$s Beds</span>
                        <span class="baths">%12$s Baths</span>
                        <span class="sqft">%13$s Sq Ft</span>
                    </p>
                </div>',
				$prop['listingPrice'],
				$idx_api->details_url() . '/' . $prop['detailsURL'],
				$prop_image_url,
				$prop['remarksConcat'],
				$prop['streetNumber'],
				$prop['streetName'],
				$prop['streetDirection'],
				$prop['unitNumber'],
				$prop['cityName'],
				$prop['state'],
				$prop['bedrooms'],
				$prop['totalBaths'],
				$prop['sqFt']
			);
		}

		$output .= '</div><!-- end .equity-listing-carousel -->';

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
				),
				$atts
			)
		);

		$city_list = sanitize_text_field( $city_list );
		$mls = sanitize_text_field( $mls );
		$use_columns = (int) sanitize_text_field( $use_columns );
		$number_columns = (int) sanitize_text_field( $number_columns );

		$city_links = \IDX\Widgets\Impress_City_Links_Widget::city_list_links( $city_list, $mls, '_self', $this->idx_api, $use_columns, $number_columns );

		if ( false == $city_links ) {
			return 'City list ID or MLS ID not found';
		}
		$city_links .= '<style>.city-list-links ul {margin-left: 0;}</style>';
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
								'savedlink'    => 'Saved Link',
							),
						),
						array(
							'label' => 'Saved Link ID',
							'attr'  => 'saved_link_id',
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
								'savedlink'    => 'Saved Link',
							),
						),
						array(
							'label' => 'Saved Link ID',
							'attr'  => 'saved_link_id',
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
