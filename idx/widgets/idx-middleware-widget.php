<?php
namespace IDX\Widgets;

/**
 * Idx_Middleware_Widget class.
 */
class Idx_Middleware_Widget extends \WP_Widget {

	/**
	 * idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {

		$this->idx_api = new \IDX\Idx_Api();

		parent::__construct(
			'impress_idx_dashboard_widget', // Base ID
			__( 'IMPress - IDX Dashboard Widget', 'idx-broker' ), // Name
			array(
				'description'                 => __( 'Embed an IDX widget created in the IDX Middleware dashboard.', 'idx-broker' ),
				'classname'                   => 'impress-idx-dashboard-widget',
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		echo $before_widget;

		if ( ! empty( $instance['title'] ) ) {
			echo $before_title . $instance['title'] . $after_title;
		}

		// If we have an ID, this is a shortcode, so we need to get the widget url from the ID.
		if ( ! empty( $instance['id'] ) ) {
			$instance['widget'] = $this->get_widget_url( $instance['id'] );
		}

		if ( ! empty( $instance['widget'] ) ) {
			if ( strpos( $instance['widget'], 'mapwidgetjs.php' ) ) {
				echo '<script type="text/javascript" name="custom-scriptLeaf" src="https://d1qfrurkpai25r.cloudfront.net/graphical/javascript/leaflet.js"></script>';
				echo '<script type="text/javascript" name="custom-scriptLeafDraw" src="https://d1qfrurkpai25r.cloudfront.net/graphical/frontend/javascript/maps/plugins/leaflet.draw.js"></script>';
				echo '<script type="text/javascript" name="custom-scriptMQ" src="https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh"></script>';
				wp_enqueue_style( 'cssLeaf', 'https://d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet-1.000.css' );
				wp_enqueue_style( 'cssLeafLabel', 'https://d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.label.css' );
			}
			echo '<script type="text/javascript" src="' . $instance['widget'] . '"></script>';
		}

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance           = array();
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['widget'] = esc_url_raw( $new_instance['widget'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'  => '',
			'widget' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'idx-broker' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php esc_attr_e( $instance['title'] ); ?>" />
		</p>

		<p>
			<?php _e( 'IDX widgets are widgets you have created in your IDX Middleware dashboard. Select one to display here:', 'idx-broker' ); ?>
		</p>

		<p>
			<select class="widefat" id="<?php echo $this->get_field_id( 'widget' ); ?>" name="<?php echo $this->get_field_name( 'widget' ); ?>">
				<option <?php selected( $instance['widget'], '' ); ?> value=""><?php _e( 'Select a widget', 'idx-broker' ); ?></option>
				<?php $this->widget_options( $instance ); ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Echos widget options
	 *
	 * The option values are the IDX widget source urls.
	 * They will be displayed by name.
	 *
	 * This is just a helper to keep the html clean
	 *
	 * @param var $instance
	 */
	public function widget_options( $instance ) {

		$widgets = $this->idx_api->idx_api_get_widgetsrc();

		if ( ! is_array( $widgets ) ) {
			return;
		}

		foreach ( $widgets as $widget ) {
			echo '<option ', selected( $instance['widget'], $widget->url, 0 ), ' value="', $widget->url, '">', $widget->name, '</option>';
		}
	}

	/**
	 * Returns the widget URL given a widget UID.
	 *
	 * @param  string $widget_uid The IDX assigned widget UID.
	 * @return string | false     Widget or URL or false if none found.
	 */
	public function get_widget_url( $widget_uid ) {
		$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
		foreach ( $idx_widgets as $widget ) {
			if ( $widget_uid === $widget->uid ) {
				return $widget->url;
			}
		}
		return false;
	}
}
