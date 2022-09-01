<?php
namespace IDX\Widgets;

/**
 * Impress_Lead_Signup_Widget class.
 */
class Impress_Lead_Signup_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		if ( isset( $_GET['error'] ) ) {
			$this->error_message = $this->handle_errors( (string) sanitize_text_field( wp_unslash( $_GET['error'] ) ) );
		} else {
			$this->error_message = '';
		}

		parent::__construct(
			'impress_lead_signup', // Base ID.
			__( 'IMPress Lead Sign Up', 'idxbroker' ), // Name.
			array(
				'description'                 => __( 'Lead sign up form', 'idxbroker' ),
				'classname'                   => 'impress-idx-signup-widget',
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Error_message
	 *
	 * @var mixed
	 * @access public
	 */
	public $error_message;

	/**
	 * Defaults
	 *
	 * @var mixed
	 * @access public
	 */
	public $defaults = array(
		'title'          => 'Lead Sign Up',
		'custom_text'    => '',
		'phone_number'   => false,
		'styles'         => 1,
		'new_window'     => 0,
		'agentID'        => '',
		'password_field' => false,
		'button_text'    => 'Sign Up!',
	);

	/**
	 * Front-end display of widget
	 *
	 * @see WP_Widget::widget()
	 *
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

		if ( ! empty( $instance['styles'] ) ) {
			wp_enqueue_style( 'impress-lead-signup', IMPRESS_IDX_URL . 'assets/css/widgets/impress-lead-signup.min.css', [], '1.0.0' );
		}

		if ( ! isset( $instance['new_window'] ) ) {
			$instance['new_window'] = 0;
		}

		$target = $this->target( $instance['new_window'] );

		$title       = apply_filters( 'widget_title', $instance['title'] );
		$custom_text = $instance['custom_text'];

		$wpl_options = get_option( 'plugin_wp_listings_settings' );

		// Validate fields.
		wp_localize_script( 'impress-lead-signup', 'idxLeadLoginUrl', [ $this->lead_login_page() ] );
		wp_enqueue_script( 'impress-lead-signup' );

		if ( ! empty( get_option( 'idx_recaptcha_enabled' ) ) || ! empty( get_option( 'idx_recaptcha_site_key' ) ) ) {
			wp_enqueue_script( 'idx-recaptcha' );
			wp_enqueue_script( 'idx-google-recaptcha' );
			wp_enqueue_script( 'jquery' );
		}

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		if ( ! empty( $custom_text ) ) {
			echo '<p>' . esc_html( $custom_text ) . '</p>';
		}

		?>
		<form action="<?php echo esc_url( $this->idx_api->subdomain_url() . 'ajax/usersignup.php' ); ?>" class="impress-lead-signup" method="post" target="<?php echo esc_attr( $target ); ?>" name="LeadSignup" id="LeadSignup">
			<?php echo esc_html( $this->error_message ); ?>
			<input type="hidden" name="action" value="addLead">
			<input type="hidden" name="signupWidget" value="true">
			<input type="hidden" name="contactType" value="direct">

			<?php
			if ( has_filter( 'impress_lead_signup_agent_id_field' ) ) {
				echo wp_kses_post( apply_filters( 'impress_lead_signup_agent_id_field', '<input type="hidden" name="agentOwner" value="' . $instance['agentID'] . '">' ) );
			}
			?>

			<label id="impress-widgetfirstName-label" class="ie-only" for="impress-widgetfirstName"><?php esc_html_e( 'First Name:', 'idxbroker' ); ?></label>
			<input id="impress-widgetfirstName" type="text" name="firstName" placeholder="First Name" required>

			<label id="impress-widgetlastName-label" class="ie-only" for="impress-widgetlastName"><?php esc_html_e( 'Last Name:', 'idxbroker' ); ?></label>
			<input id="impress-widgetlastName" type="text" name="lastName" placeholder="Last Name" required>

			<label id="impress-widgetemail-label" class="ie-only" for="impress-widgetemail"><?php esc_html_e( 'Email:', 'idxbroker' ); ?></label>
			<input id="impress-widgetemail" type="email" name="email" placeholder="Email" required>

			<?php
			if ( true == $instance['password_field'] ) {
				echo '
				<label for="impress-widgetPassword">Password:</label>
				<input id="impress-widgetPassword" type="password" name="password" placeholder="Password">';
			}
			?>

			<?php
			if ( true == $instance['phone_number'] ) {
				echo '
				<label id="impress-widgetphone-label" class="ie-only" for="impress-widgetphone">' . esc_html__( 'Phone:', 'idxbroker' ) . '</label>
				<input id="impress-widgetphone" type="tel" name="phone" placeholder="Phone">';
			}
			?>

			<?php
			// Include Google reCAPTCHA hidden field if setting enabled.
			if ( ! empty( get_option( 'idx_recaptcha_enabled' ) ) || ! empty( get_option( 'idx_recaptcha_site_key' ) ) ) {
				echo '<input type="hidden" name="recaptchaToken" id="IDX-recaptcha-usersignup" data-action="usersignup" class="IDX-recaptchaToken" value>';
				echo '<button id="bb-IDX-widgetsubmit" type="submit" name="btnSubmit" data-action="submit" data-callback="onSubmit" data-sitekey="6LcUhOYUAAAAAF694SR5_qDv-ZdRHv77I6ZmSiij">';
				echo esc_attr( apply_filters( 'impress_lead_signup_submit_text', $instance['button_text'] ) );
				echo '</button>';
			} else {
				echo '<button id="bb-IDX-widgetsubmit" type="submit" name="btnSubmit">';
				echo esc_attr( apply_filters( 'impress_lead_signup_submit_text', $instance['button_text'] ) );
				echo '</button>';
			}
			?>
		</form>
		<?php

		echo $after_widget;
	}

	/**
	 * Target
	 *
	 * @access public
	 * @param mixed $new_window - New window settings value.
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
		$new_instance               = array_merge( $this->defaults, $new_instance );
		$instance                   = array();
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['custom_text']    = htmlentities( $new_instance['custom_text'] );
		$instance['phone_number']   = $new_instance['phone_number'];
		$instance['styles']         = (int) $new_instance['styles'];
		$instance['new_window']     = strip_tags( $new_instance['new_window'] );
		$instance['password_field'] = strip_tags( $new_instance['password_field'] );
		$instance['agentID']        = (int) $new_instance['agentID'];
		$instance['button_text']    = strip_tags( $new_instance['button_text'] );

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
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'custom_text' ) ); ?>"><?php esc_html_e( 'Custom Text', 'idxbroker' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_text' ) ); ?>" value="<?php esc_attr( $instance['custom_text'] ); ?>" rows="5"><?php esc_attr( $instance['custom_text'] ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'phone_number' ) ); ?>"><?php esc_html_e( 'Show phone number field?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'phone_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone_number' ) ); ?>" value="1" <?php checked( $instance['phone_number'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>"><?php esc_html_e( 'Default Styling?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'styles' ) ); ?>" value="1" <?php checked( $instance['styles'], true ); ?>>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>"><?php esc_html_e( 'Open in a New Window?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'new_window' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'new_window' ) ); ?>" value="1" <?php checked( $instance['new_window'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'password_field' ) ); ?>"><?php esc_html_e( 'Add password form field?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'password_field' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'password_field' ) ); ?>" value="1" <?php checked( $instance['password_field'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'agentID' ) ); ?>"><?php esc_html_e( 'Route to Agent:', 'idxbroker' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'agentID' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'agentID' ) ); ?>">
				<?php $this->idx_api->get_agents_select_list( $instance['agentID'] ); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Button text:', 'idxbroker' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" value="<?php esc_attr( $instance['button_text'] ); ?>">
		</p>
		<?php

	}

	/**
	 * Lead_login_page
	 *
	 * @access public
	 * @return mixed
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
	 * For error handling since this is a cross domain request
	 *
	 * @access public
	 * @param mixed $error Error.
	 * @return string
	 */
	public function handle_errors( $error ) {
		$output = '';

		// User already has an account.
		if ( stristr( $error, 'lead' ) ) {
			// Redirect to lead login page.
			include WPINC . '/pluggable.php';
			return wp_redirect( $this->lead_login_page() );
			// Other form error.
		} elseif ( stristr( $error, 'true' ) ) {
			$output .= '<div class="error">';
			$output .= 'There is an error in the form. Please double check that your email address is valid.';
			$output .= '</div>';
		}

		return $output;
	}

}
