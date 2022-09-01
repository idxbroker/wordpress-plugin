<?php
namespace IDX\Widgets;

/**
 * IMPress_Lead_Login_Widget class.
 */
class IMPress_Lead_Login_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		parent::__construct(
			'impress_lead_login', // Base ID.
			__( 'IMPress Lead Login', 'idxbroker' ), // Name.
			array(
				'description'                 => __( 'Lead login form', 'idxbroker' ),
				'classname'                   => 'impress-idx-login-widget',
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
	 * Defaults
	 *
	 * @var mixed
	 * @access public
	 */
	public $defaults = [
		'title'          => 'Account Login',
		'custom_text'    => '',
		'styles'         => 1,
		'new_window'     => 0,
		'password_field' => false,
	];

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
			wp_enqueue_style( 'impress-lead-login' );
		}

		if ( ! isset( $instance['new_window'] ) ) {
			$instance['new_window'] = 0;
		}

		$target = $this->target( $instance['new_window'] );

		$title          = apply_filters( 'widget_title', $instance['title'] );
		$custom_text    = $instance['custom_text'];
		$password_field = $instance['password_field'];

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		if ( ! empty( $custom_text ) ) {
			echo '<p>' . esc_html( $custom_text ) . '</p>';
		}

		// Returns hidden if false or not set.
		$password_field_type = $password_field ? 'password' : 'hidden';

		?>
		<form action="<?php echo esc_url( $this->idx_api->subdomain_url() ); ?>ajax/userlogin.php" class="impress-lead-login" method="post" target="<?php echo esc_attr( $target ); ?>" name="leadLoginForm">
			<input type="hidden" name="action" value="login">
			<input type="hidden" name="loginWidget" value="true">
			<label for="impress-widgetEmail"><?php esc_html_e( 'Email Address:', 'idxbroker' ); ?></label>
			<input id="impress-widgetEmail" type="text" name="email" placeholder="Enter your email address">
			<?php
			if ( 'password' === $password_field_type ) {
				echo '<label for="impress-widgetPassword">Password:</label>';}
			?>
			<input id="impress-widgetPassword" type="<?php echo esc_attr( $password_field_type ); ?>" name="password" placeholder="Password"><input id="impress-widgetLeadLoginSubmit" type="submit" name="login" value="Log In">
		</form>
		<?php

		echo $after_widget;
	}

	/**
	 * Target
	 *
	 * @access public
	 * @param mixed $new_window - Window target.
	 * @return string
	 */
	public function target( $new_window ) {
		if ( ! empty( $new_window ) ) {
			// If enabled, open links in new tab/window.
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
		$instance['styles']         = (int) $new_instance['styles'];
		$instance['new_window']     = strip_tags( $new_instance['new_window'] );
		$instance['password_field'] = strip_tags( $new_instance['password_field'] );

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
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'idxbroker' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'custom_text' ) ); ?>"><?php esc_html_e( 'Custom Text', 'idxbroker' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_text' ) ); ?>" value="<?php esc_attr( $instance['custom_text'] ); ?>" rows="5"><?php esc_html( $instance['custom_text'] ); ?></textarea>
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

		<?php

	}
}
