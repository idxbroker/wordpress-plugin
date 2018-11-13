<?php
namespace IDX\Shortcodes;

class Impress_Lead_Signup_Shortcode {

	public $idx_api;
	public $error_message;

	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		if ( isset( $_GET['error'] ) ) {
			$this->error_message = $this->handle_errors( $_GET['error'] );
		} else {
			$this->error_message = '';
		}

		add_shortcode( 'impress_lead_signup', array( $this, 'shortcode_output' ) );
	}

	public function shortcode_output( $atts ) {
		extract(
			shortcode_atts(
				array(
					'phone'          => 0,
					'styles'         => 1,
					'new_window'     => 0,
					'agent_id'       => '',
					'password_field' => false,
					'button_text'    => 'Sign Up!',
				),
				$atts
			)
		);

		if ( ! empty( $styles ) ) {
			wp_enqueue_style( 'impress-lead-signup', plugins_url( '../assets/css/widgets/impress-lead-signup.css', dirname( __FILE__ ) ) );
		}

		if ( ! isset( $new_window ) ) {
			$new_window = 0;
		}

		$target = $this->target( $new_window );

		$wpl_options = get_option( 'plugin_wp_listings_settings' );

		// Validate fields
		wp_register_script( 'impress-lead-signup', plugins_url( '../assets/js/idx-lead-signup.min.js', dirname( __FILE__ ) ) );
		wp_localize_script( 'impress-lead-signup', 'idxLeadLoginUrl', $this->lead_login_page() );
		wp_enqueue_script( 'impress-lead-signup' );

		if ( $wpl_options['wp_listings_captcha_site_key'] != '' || get_option( 'idx_recaptcha_site_key' ) != '' ) {
			wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js' );
		}

		$hidden_fields = ( $agent_id || has_filter( 'impress_lead_signup_agent_id_field' ) ) ? apply_filters( 'impress_lead_signup_agent_id_field', '<input type="hidden" name="agentOwner" value="' . $agent_id . '">' ) : '';

		$widget = sprintf(
			'
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
                <input id="impress-widgetemail" type="email" name="email" placeholder="Email" required>',
			$this->idx_api->subdomain_url(),
			$target,
			$this->error_message,
			$hidden_fields
		);

		if ( $password_field ) {
			$widget .= sprintf(
				'
				<label for="impress-widgetPassword">Password:</label>
                <input id="impress-widgetPassword" type="password" name="password" placeholder="Password">'
			);
		}

		if ( $phone ) {
			$widget .= sprintf(
				'
            <label id="impress-widgetphone-label" class="ie-only" for="IDX-widgetphone">Phone:</label>
            <input id="impress-widgetphone" type="tel" name="phone" placeholder="Phone">'
			);
		}

		if ( $wpl_options['wp_listings_captcha_site_key'] != '' || get_option( 'idx_recaptcha_site_key' ) != '' ) {
			$site_key = ( $wpl_options['wp_listings_captcha_site_key'] != '' ) ? $wpl_options['wp_listings_captcha_site_key'] : get_option( 'idx_recaptcha_site_key' );
			$widget  .= sprintf( '<div id="recaptcha" class="g-recaptcha" data-sitekey="%s"></div>', $site_key );
		}

		$widget .= sprintf(
			'<input id="impress-widgetsubmit" type="submit" name="submit" value="%s">
            </form>',
			$button_text
		);

		return $widget;
	}

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



	// For error handling since this is a cross domain request.
	public function handle_errors( $error ) {
		$output = '';

		// User already has an account.
		if ( stristr( $error, 'lead' ) ) {
			// Redirect to lead login page.
			return wp_redirect( $this->lead_login_page() );
			// Other form error.
		} elseif ( stristr( $error, 'true' ) ) {
			$output .= '<div class="error">';
			$output .= 'There is an error in the form. Please double check that your email address is valid.';
			$output .= '</div>';
		}

		return $output;
	}

	public function target( $new_window ) {
		if ( ! empty( $new_window ) ) {
			// if enabled, open links in new tab/window
			return '_blank';
		} else {
			return '_self';
		}
	}

}
