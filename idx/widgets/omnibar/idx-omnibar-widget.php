<?php
namespace IDX\Widgets\Omnibar;

/**
 * IDX_Omnibar_Widget class.
 */
class IDX_Omnibar_Widget extends \WP_Widget {

	/**
	 * __construct function.
	 *
	 * @since 2.5.10
	 * @access public
	 */
	public function __construct() {
		$this->create_omnibar = new \IDX\Widgets\Omnibar\Create_Omnibar();
		$widget_ops           = array(
			'classname'                   => 'IDX_Omnibar_Widget',
			'description'                 => 'An Omnibar Search Widget for use with IDX WordPress Sites',
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'IDX_Omnibar_Widget', 'IMPress Omnibar Search', $widget_ops );
	}

	/**
	 * Begin create_omnibar.
	 *
	 * @since 2.5.10
	 * @var mixed
	 * @access public
	 */
	public $create_omnibar;

	/**
	 * Begin defaults.
	 *
	 * @since 2.5.10
	 * @var mixed
	 * @access public
	 */
	public $defaults = array(
		'title'     => '',
		'min_price' => 0,
		'styles'    => 1,
		'extra'     => 0,
	);

	/**
	 * The form function.
	 *
	 * @since 2.5.10
	 * @access public
	 * @param mixed $instance containst the instance information.
	 */
	public function form( $instance ) {
		$defaults = $this->defaults;
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title    = $instance['title'];
		// All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$this'.
		?>
		<p><label for="<?php echo esc_attr( $title ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'styles' ); ?>"><?php _e( 'Default Styling?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'styles' ); ?>" name="<?php echo $this->get_field_name( 'styles' ); ?>" value="1" <?php checked( $instance['styles'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'extra' ); ?>"><?php _e( 'Extra Fields?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'extra' ); ?>" name="<?php echo $this->get_field_name( 'extra' ); ?>" value="1" <?php checked( $instance['extra'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'min_price' ); ?>"><?php _e( 'Include Min Price? (If Extra Fields is enabled)', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'min_price' ); ?>" name="<?php echo $this->get_field_name( 'min_price' ); ?>" value="1" <?php checked( $instance['min_price'], true ); ?>>
		</p>
		<?php
	}

	/**
	 * The update function.
	 *
	 * @since 2.5.10
	 * @access public
	 * @param mixed $new_instance contains information from the new instance.
	 * @param mixed $old_instance contains information from the old instance.
	 * @return mixed $instance contains information for the updated instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = $new_instance['title'];
		$instance['styles']    = (int) $new_instance['styles'];
		$instance['extra']     = (int) $new_instance['extra'];
		$instance['min_price'] = (int) $new_instance['min_price'];
		return $instance;
	}

	/**
	 * The widget function.
	 *
	 * @since 2.5.10
	 * @access public
	 * @param mixed $args contains arguments.
	 * @param mixed $instance contains the information for the instance.
	 */
	public function widget( $args, $instance ) {
		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );
		// extract() usage is highly discouraged, due to the complexity and unintended issues it might cause.
		extract( $args, EXTR_SKIP );

		if ( empty( $instance ) ) {
			$instance = $this->defaults;
		}
		if ( ! isset( $instance['styles'] ) ) {
			$instance['styles'] = $this->defaults['styles'];
		}

		//All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$before_widget'.
		echo $before_widget;
		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {
			// All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$before_widget'.
			echo $before_title . $title . $after_title;
		}

		$plugin_dir = plugins_url();

		// grab url from database set from get-locations.php.
		$idx_url = get_option( 'idx_results_url' );

		// Widget HTML:
		// All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$before_widget'.
		if ( ! empty( $instance['extra'] ) ) {
			echo $this->create_omnibar->idx_omnibar_extra( $plugin_dir, $idx_url, $instance['styles'], $instance['min_price'] );
		} else {
			echo $this->create_omnibar->idx_omnibar_basic( $plugin_dir, $idx_url, $instance['styles'] );
		}
		// All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$before_widget'.
		echo $after_widget;
	}
}
