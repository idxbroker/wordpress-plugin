<?php
namespace IDX\Shortcodes;

/**
 * Register_Impress_Lead_Login_Shortcode class.
 */
class Impress_Lead_Login_Shortcode {

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
		// if (function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
		// 	echo "omg";
		// } else {
		// 	echo "chalula";
		// }
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

}