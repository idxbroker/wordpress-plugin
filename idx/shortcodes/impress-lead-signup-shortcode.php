<?php
	namespace IDX\Shortcodes;

	/**
	 * Begin defining the Impress_Lead_Signup_Shortcode class
	 *
	 * @since 2.5.10
	 */
	class Impress_Lead_Signup_Shortcode {
		/**
		 * Begine including the idx_api class.
		 *
		 * @since 2.5.10
		 * @var class $idx_api
		 */
		public $idx_api;
		/**
		 * Begine including the idx_api class.
		 *
		 * @since 2.5.10
		 * @var class $error_message
		 */
		public $error_message;

		/**
		 * __construct function begins constructing the class
		 */
		public function __construct() {
			$this->idx_api = new \IDX\Idx_Api();

			if ( isset( $_GET['error'] ) ) { // no sanitization of $_GET data.
				$this->error_message = $this->handle_errors( $_GET['error'] ); // no sanitization of $_GET data.
			} else {
				$this->error_message = '';
			}
			add_shortcode( 'impress_lead_signup', array( $this, 'shortcode_output' ) );
		}

		/**
		 * The shortcode_output function renders
		 *
		 * @param array $atts contains arguments for shortcode output.
		 * @since 2.5.10
		 * @return text $widget
		 */
		public function shortcode_output( $atts ) {
			// extract() usage is highly discouraged, due to the complexity and unintended issues it might cause.
			extract( shortcode_atts( array(
				'phone'          => 0,
				'styles'         => 1,
				'new_window'     => 0,
				'agent_id'       => '',
				'password_field' => false,
				'button_text'    => 'Sign Up!',
			), $atts ) );

			if ( ! empty( $styles ) ) {
				// Resource version not set in call to wp_enqueue_style(). This means new versions of the style will not always be loaded due to browser caching.
				wp_enqueue_style( 'impress-lead-signup', plugins_url( '../assets/css/widgets/impress-lead-signup.css', dirname( __FILE__ ) ) );
			}

			if ( ! isset( $new_window ) ) {
				$new_window = 0;
			}

			$target = $this->target( $new_window );

			$wpl_options = get_option( 'plugin_wp_listings_settings' );

			// Validate fields.
			// In footer ($in_footer) is not set explicitly wp_register_script; It is recommended to load scripts in the footer. Please set this value to `true` to load it in the footer, or explicitly `false` if it should be loaded in the header.
			// Resource version not set in call to wp_register_script(). This means new versions of the script will not always be loaded due to browser caching.
			wp_register_script( 'impress-lead-signup', plugins_url( '../assets/js/idx-lead-signup.min.js', dirname( __FILE__ ) ) );
			wp_localize_script( 'impress-lead-signup', 'idxLeadLoginUrl', $this->lead_login_page() );
			wp_enqueue_script( 'impress-lead-signup' );

			if ( $wpl_options['wp_listings_captcha_site_key']  != '' || get_option( 'idx_recaptcha_site_key' )  != '' ) {
				// In footer ($in_footer) is not set explicitly wp_enqueue_script; It is recommended to load scripts in the footer. Please set this value to `true` to load it in the footer, or explicitly `false` if it should be loaded in the header.
				// Resource version not set in call to wp_enqueue_script(). This means new versions of the script will not always be loaded due to browser caching.
				wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js' );
			}

			$hidden_fields = ( $agent_id || has_filter( 'impress_lead_signup_agent_id_field' ) ) ? apply_filters( 'impress_lead_signup_agent_id_field', '<input type="hidden" name="agentOwner" value="' . $agent_id . '">' ) : '';

			$widget = sprintf('
			<form action="%1$sajax/usersignup.php" class="impress-lead-signup" method="post" target="%2$s" name="LeadSignup" id="LeadSignup">
			%3$s
			<input type="hidden" name="action" value="addLead">
			<input type="hidden" name="signupWidget" value="true">
			<input type="hidden" name="contactType" value="direct">
			%4$s

			<label id="impress-widgetfirstName-label" class="ie-only" for="IDX-widgetfirstName">First Name:</label>
			<input id="impress-widgetfirstName" type="text" name="firstName" placeholder="First Name" required>

			<label id="impress-widgetlastName-label" class="ie-only" for="IDX-widgetlastName">Last Name:</label>
			<input id="impress-widgetlastName" type="text" name="lastName" placeholder="Last Name" required>

			<label id="impress-widgetemail-label" class="ie-only" for="IDX-widgetemail">Email:</label>
			<input id="impress-widgetemail" type="email" name="email" placeholder="Email" required>', $this->idx_api->subdomain_url(), $target, $this->error_message, $hidden_fields);

			if ( $password_field ) {
				$widget .= sprintf('
				<label for="impress-widgetPassword">Password:</label>
				<input id="impress-widgetPassword" type="password" name="password" placeholder="Password">');
			}

			if ( $phone ) {
				$widget .= sprintf( '
				<label id="impress-widgetphone-label" class="ie-only" for="IDX-widgetphone">Phone:</label>
				<input id="impress-widgetphone" type="tel" name="phone" placeholder="Phone">' );
			}

			if ( '' !== wpl_options['wp_listings_captcha_site_key'] || '' !== get_option( 'idx_recaptcha_site_key' ) ) {
				$site_key = ( '' !== $wpl_options['wp_listings_captcha_site_key'] ) ? $wpl_options['wp_listings_captcha_site_key'] : get_option( 'idx_recaptcha_site_key' );
				$widget  .= sprintf( '<div id="recaptcha" class="g-recaptcha" data-sitekey="%s"></div>', $site_key );
			}

			$widget .= sprintf('<input id="impress-widgetsubmit" type="submit" name="submit" value="%s">
			</form>', $button_text);

			return $widget;
		}
		/**
		 * The lead_login_page function
		 *
		 * @since 2.5.10
		 * @return text HTML markup of userlogin link location.
		 */
		public function lead_login_page() {
			$links = $this->idx_api->idx_api_get_systemlinks();
			if ( empty( $links ) ) {
				return '';
			}
			foreach ( $links as $link ) {
				if ( preg_match( '/userlogin/i', $link->url ) ) {
					return $link->url;
				}
			}
		}



		/**
		 * For error handling since this is a cross domain request.
		 *
		 * @since 2.5.10
		 * @param text $error Contains information regarding error messages.
		 * @return text $output HTML markup of the error that was generated.
		 */
		public function handle_errors( $error ) {
			$output = '';

			// ser already has an account.
			if ( stristr( $error, 'lead' ) ) {
			// Redirect to lead login page.
			// wp_redirect() found. Using wp_safe_redirect(), along with the allowed_redirect_hosts filter if needed, can help avoid any chances of malicious redirects within code. It is also important to remember to call exit() after a redirect so that no other unwanted code is executed.
			return wp_redirect( $this->lead_login_page() );
			// Other form error.
			} elseif ( stristr( $error, 'true' ) ) {
				$output .= '<div class="error">';
				$output .= 'There is an error in the form. Please double check that your email address is valid.';
				$output .= '</div>';
			}

			return $output;
		}
		/**
		 * The target function specifies if a link should open in a new window or not.
		 *
		 * @since 2.5.10
		 * @param text $new_window contains the setting for the link.
		 * @return text for either _blank or _self depending on the setting in $new_window
		 */
		public function target( $new_window ) {
			if ( ! empty( $new_window ) ) {
				// if enabled, open links in new tab/window.
				return '_blank';
			} else {
				return '_self';
			}
		}

	}
