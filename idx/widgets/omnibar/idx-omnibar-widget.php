<?php
namespace IDX\Widgets\Omnibar;

/**
 * IDX_Omnibar_Widget class.
 */
class IDX_Omnibar_Widget extends \WP_Widget {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
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
	 * Create_omnibar
	 *
	 * @var mixed
	 * @access public
	 */
	public $create_omnibar;

	/**
	 * Defaults
	 *
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
	 * Form function.
	 *
	 * @access public
	 * @param mixed $instance - Instance.
	 * @return void
	 */
	public function form( $instance ) {
		$defaults = $this->defaults;
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$title    = $instance['title'];
		?>
		<p><label for="<?php echo esc_attr( $title ); ?>">Title: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>"><?php esc_html_e( 'Default Styling?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'styles' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'styles' ) ); ?>" value="1" <?php checked( $instance['styles'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'extra' ) ); ?>"><?php esc_html_e( 'Extra Fields?', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'extra' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'extra' ) ); ?>" value="1" <?php checked( $instance['extra'], true ); ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'min_price' ) ); ?>"><?php esc_html_e( 'Include Min Price? (If Extra Fields is enabled)', 'idxbroker' ); ?></label>
			<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'min_price' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'min_price' ) ); ?>" value="1" <?php checked( $instance['min_price'], true ); ?>>
		</p>
		<?php
	}

	/**
	 * Update function.
	 *
	 * @access public
	 * @param mixed $new_instance - New Instance.
	 * @param mixed $old_instance - New Instance.
	 * @return mixed
	 */
	public function update( $new_instance, $old_instance ) {
		// Merge defaults and new_instance to avoid any missing index warnings when used with the legacy block widget.
		$new_instance          = array_merge( $this->defaults, $new_instance );
		$instance              = $old_instance;
		$instance['title']     = $new_instance['title'];
		$instance['styles']    = (int) $new_instance['styles'];
		$instance['extra']     = (int) $new_instance['extra'];
		$instance['min_price'] = (int) $new_instance['min_price'];
		return $instance;
	}

	/**
	 * Widget function.
	 *
	 * @access public
	 * @param mixed $args - Arguments.
	 * @param mixed $instance - Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$defaults = $this->defaults;

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $args, EXTR_SKIP );

		if ( empty( $instance ) ) {
			$instance = $this->defaults;
		}
		if ( ! isset( $instance['styles'] ) ) {
			$instance['styles'] = $this->defaults['styles'];
		}

		echo $before_widget;
		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$plugin_dir = plugins_url();

		// grab url from database set from get-locations.php
		$idx_url = get_option( 'idx_results_url' );

		// Widget HTML:
		if ( ! empty( $instance['extra'] ) ) {
			echo $this->create_omnibar->idx_omnibar_extra( $plugin_dir, $idx_url, $instance['styles'], $instance['min_price'] );
		} else {
			echo $this->create_omnibar->idx_omnibar_basic( $plugin_dir, $idx_url, $instance['styles'] );
		}
		echo $after_widget;
	}
}
