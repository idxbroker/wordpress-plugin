<?php
namespace IDX\Widgets;

/**
 * DEPRECATED as of 2.5.1
 * File to manpulate IDX widgets. Create widgets based upon IDX widget API.
 * Based upon API response, iterate through array and create classes for widgets.
 *
 * @author IDX, LLC
 */

/**
 * Idx_Widget class provides a 'factory'-like class for the creation of widgets below this class definition.
 * Doing it this way allows us to give the dynamic class access to all of WP_Widget's methods.
 *
 * @since 8/2/2019
 * @author allen-mcnichols
 */
class Idx_Widget_Class extends \WP_Widget {

	/**
	 * Begin defining the widget_class class.
	 *
	 * @var mixed
	 * @access public
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public $widget_class; // Holds name of widget's class, ex: widget_596_12345.

	/**
	 * Begin defining widget_url.
	 *
	 * @var mixed
	 * @access public
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public $widget_url; // URL location of widget's JS file in IDX MW.

	/**
	 * Begin defining widget_id.
	 *
	 * @var mixed
	 * @access public
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public $widget_id; // The id of widget, ex: idx596_12345.

	/**
	 * Begin defining the widget function.
	 *
	 * @access public
	 * @param mixed $args the arguments for the widget.
	 * @param mixed $instance the instance of the widget.
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public function widget( $args, $instance ) {
		/**
		 * The vars contained in $args:
		 * name ->  Title of the containing div (ex: Main Widget Area or Secondary Widget Area).
		 * id   ->  sidebar-1 or location of widget on page.
		 * description  ->  Description of what the containing div does.
		 * class    -> unknown.
		 * before_widget    -> output before widget drawn.
		 * before_title -> output before widget title drawn.
		 * after_title  -> same as above but drawn after. after_title must come before after_widget.
		 * after_widget -> same as above but drawn after.
		 * widget_id    -> IDX widget class appended with revision if there are multiple widgets with same UID, such as cached. (ex: idx596_15570-2).
		 * widget_name  ->  IDX Name assigned within plat MW.
		 *
		 * @since 8/2/2019
		 * @author sheparddw
		 */

		/**
		 * The vars contained in $instance:
		 * title -> title assigned within WP UI Widgets Editor.
		 *
		 * @since 8/2/2019
		 * @author sheparddw
		 */

		/**
		 * IDX props for IDX widget objects
		 * uid  -> AID-WidgetID (ex: 596-12345)
		 * name -> Title assigned within IDX Broker MW
		 * url  -> Link to the widget hosted in our MW
		 *
		 * @since 8/2/2019
		 * @author sheparddw
		 */

		/**
		 * Best Practices: extract() usage is highly discouraged, due to the complexity and unintended issues it might cause.
		 *
		 * @since 8/2/2019
		 * @author allen-mcnichols
		 */
		extract( $args ); // vars contained within
		echo $before_widget;
		echo $before_title;

		if ( ( ! empty( $instance['title'] ) ) && $instance['title'] == '!%hide_title!%' ) { // if client puts in '!%hide_title!%' for widget title in WP front-end, will display no title.
			echo '';
		} elseif ( ! empty( $instance['title'] ) ) { // Else if WP title isn't empty, display that.
			echo $instance['title']; // All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$before_widget'.
		} else {
			echo $widget_name; // All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$widget_name'.
		}
		// If no WP title and not specifically set to 'none', display IDX Widget title which is in $args param only load leaflet scripts and styles for map search widget. WP takes care of duplicates automatically.
		if ( strpos( $this->widget_url, 'mapwidgetjs.php' ) ) {
			wp_enqueue_script( 'custom-scriptLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/javascript/leaflet.js', __FILE__ );
			wp_enqueue_script( 'custom-scriptLeafDraw', '//d1qfrurkpai25r.cloudfront.net/graphical/frontend/javascript/maps/plugins/leaflet.draw.js', __FILE__ );
			wp_enqueue_script( 'custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v1.0/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', __FILE__ );
			wp_enqueue_style( 'cssLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.css' );
			wp_enqueue_style( 'cssLeafLabel', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.label.css' );
			echo $after_title . "<script src=\"{$this->widget_url}\" defer></script>" . $after_widget;

		} else {
			echo $after_title;
			echo "<script src='{$this->widget_url}'></script>";
			echo $after_widget;
		}

			/**
			 * All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$after_title', '$after_widget'.
			 *
			 * @since 8/2/2019
			 * @author allen-mcnichols
			 */

	} // End widget function.

	/**
	 * Form will display a editing UI for changing widget title.
	 *
	 * @param  [array] $instance [description].
	 * @since 8/2/2019
	 * @author sheparddw
	 */
	public function form( $instance ) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : ''; // if instance has a title already, display that pup, otherwise display empty input
		echo "<div id='{$this->widget_id}-admin-panel'>";
		echo "<label for='{$this->get_field_id('title')}'>Widget Title:</label>";
		echo "<input type='text' name='{$this->get_field_name('title')}' id='{$this->get_field_id('title')}' value='{$title}' />";
		echo '<br /><br />';
		echo '</div>';

		/**
		 * All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"<div id='{$this->widget_id}-admin-panel'>"', '"<label for='{$this->get_field_id('title')}'>Widget Title:</label>"', '"<input type='text' name='{$this->get_field_name('title')}' id='{$this->get_field_id('title')}' value='{$title}' />"'.
		 *
		 * @since 8/2/2019
		 * @author allen-mcnichols
		 */

	} // end form fn

	/**
	 * The update function will take new values inputted into the editor and return the values after stripping the tags.
	 *
	 * @param  array $new_instance [new vals].
	 * @param  array $old_instance [old vals].
	 * @return array [new vals w/ tags stripped]
	 * @since 8/2/2019
	 * @author allen-mcnichols
	 */
	public function update( $new_instance, $old_instance ) {
		$return             = array();
		$return['title']    = strip_tags( $new_instance['title'] );
		$return['text']     = strip_tags( $new_instance['text'] );
		$return['textarea'] = strip_tags( $new_instance['textarea'] );
		return $return;
	} // end update fn

} // end Idx_Widget class definition
