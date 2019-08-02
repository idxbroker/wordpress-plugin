<?php
/**
 * DEPRECATED as of 2.5.1
 *
 * @package IDX\Widgets
 * @since 8/2/2019
 */
namespace IDX\Widgets;

/**
 * Create_Idx_Widgets class.
 *
 * @since 8/2/2019
 */
class Create_Idx_Widgets {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		$this->create_widgets();
	}

	/**
	 * Begin using the idx_api class.
	 *
	 * @var mixed
	 * @access public
	 * @since 8/2/2019
	 */
	public $idx_api;

	/**
	 * Begin defining the create_widgets function.
	 *
	 * @access public
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public function create_widgets() {

		$widget_cache = $this->idx_api->get_transient( 'idx_widgetsrc_cache' );
		$idx_widgets  = ( $widget_cache ) ? $widget_cache : $this->idx_api->idx_api_get_widgetsrc();

		if ( is_array( $idx_widgets ) ) {
			foreach ( $idx_widgets as $widget ) {
				if ( ! is_numeric( str_replace( '-', '', $widget->uid ) ) ) { // if widget UID is not numeric after removing dashes, then it's not in proper format, continue to next widget.
					continue;
				}

				$widget_class   = 'widget_' . str_replace( '-', '_', $widget->uid ); // format class name, ex: widget_596-12345.
				$widget_id      = 'idx' . str_replace( '-', '_', $widget->uid );
				$bad_characters = array( '{', '}', '[', ']', '%' ); // remove any potential braces or other script breaking characters, then escape them using WP's function esc_html.
				$widget_title   = 'IDX ' . esc_html( str_replace( $bad_characters, '', $widget->name ) ); // set widget title to "IDX [name]".
				$widget_url     = preg_replace( '#^http:#', '', $widget->url );

				$widget_ops = "array('classname' => '{$widget_class}',
                                'description' => __('$widget_title', 'text domain'))"; // to be eval'd upon class creation below.
				// easiest manner to create a dynamically named class is to eval a string to do it for us.
				// all the variables above are escaped properly to prevent any breakage from using the eval function
				// upon creation of the new widget class, it will extend the Idx_Widget class created above, which extends WP_Widget.
				$eval = "class {$widget_class} extends \IDX\Widgets\Idx_Widget_Class {
                    function __construct() {
                       \WP_Widget::__construct('{$widget_id}', __('{$widget_title}', 'text domain'), $widget_ops);
                        \$this->widget_url = '{$widget_url}';
                        \$this->widget_class = '{$widget_class}';
                        \$this->widget_id = '{$widget_id}';
                    }}";

				eval( $eval );
				add_action( 'widgets_init', create_function( '', "return register_widget('{$widget_class}');" ) ); // attach the newly created widget class to the WP widget initializier.

				/*
					Create an anonymous (lambda-style) function
					create_function( string $args , string $code ): string
					create_function() is deprecated as of PHP 7.2, please use full fledged functions or anonymous functions instead.
				*/
			}
		}
	}

	/**
	 * Function to get the widget code by title get_widget_by_uid
	 *
	 * @param string $uid The UID used for the widget.
	 *
	 * @return html code for showing the widget
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public static function get_widget_by_uid( $uid ) {
		$idx_api         = new \IDX\Idx_Api();
		$idx_widgets     = $idx_api->idx_api_get_widgetsrc();
		$idx_widget_code = null;

		if ( $idx_widgets ) {
			foreach ( $idx_widgets as $widget ) {
				if ( strcmp( $widget->uid, $uid ) == 0 ) {
					$idx_widget_link = preg_replace( '#^http:#', '', $widget->url );

					// only load leaflet scripts and styles for map search widget. WP takes care of duplicates automatically.
					if ( strpos( $idx_widget_link, 'mapwidgetjs.php' ) ) {
						wp_enqueue_script( 'custom-scriptLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/javascript/leaflet.js', __FILE__, true );
						wp_enqueue_script( 'custom-scriptLeafDraw', '//d1qfrurkpai25r.cloudfront.net/graphical/frontend/javascript/maps/plugins/leaflet.draw.js', __FILE__, true );
						wp_enqueue_script( 'custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__ );
						wp_enqueue_style( 'cssLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.css', __FILE__,  true );
						wp_enqueue_style( 'cssLeafLabel', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.label.css', __FILE__, true );
						$idx_widget_code = "<script src=\"{$idx_widget_link}\" defer></script>"; // Scripts must be registered/enqueued via wp_enqueue_script.
					} else {
						$idx_widget_code = "<script src=\"{$idx_widget_link}\"></script>"; // Scripts must be registered/enqueued via wp_enqueue_script.
					}

					return $idx_widget_code;
				}
			}
		} else {
			return $idx_widget_code;
		}
	}
}

