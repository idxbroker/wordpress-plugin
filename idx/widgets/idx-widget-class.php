<?php
namespace IDX\Widgets;

/**
 * DEPRECATED as of 2.5.1
 * File to manpulate IDX widgets. Create widgets based upon IDX widget API.
 * Based upon API response, iterate through array and create classes for widgets.
 *
 * @author IDX, LLC
 */
// Idx_Widget class provides a 'factory'-like class for the creation of widgets below this class definition.
// Doing it this way allows us to give the dynamic class access to all of WP_Widget's methods
class Idx_Widget_Class extends \WP_Widget {

	/**
	 * widget_class
	 *
	 * @var mixed
	 * @access public
	 */
	public $widget_class; // holds name of widget's class, ex: widget_596_12345

	/**
	 * widget_url
	 *
	 * @var mixed
	 * @access public
	 */
	public $widget_url; // URL location of widget's JS file in IDX MW

	/**
	 * widget_id
	 *
	 * @var mixed
	 * @access public
	 */
	public $widget_id; // id of widget, ex: idx596_12345

	/**
	 * widget function.
	 *
	 * @access public
	 * @param mixed $args
	 * @param mixed $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		/**
		 * vars contained in $args:
		 * name ->  Title of the containing div (ex: Main Widget Area or Secondary Widget Area)
		 * id   ->  sidebar-1 or location of widget on page
		 * description  ->  Description of what the containing div does
		 * class    -> unknown
		 * before_widget    -> output before widget drawn
		 * before_title -> output before widget title drawn
		 * after_title  -> same as above but drawn after. after_title must come before after_widget
		 * after_widget -> same as above but drawn after
		 * widget_id    -> IDX widget class appended with revision if there are multiple widgets with same UID, such as cached. (ex: idx596_15570-2)
		 * widget_name  ->  IDX Name assigned within plat MW
		 */

		/**
		 * vars contained in $instance:
		 * title -> title assigned within WP UI Widgets Editor
		 */

		/**
		 * IDX props for IDX widget objects
		 * uid  -> AID-WidgetID (ex: 596-12345)
		 * name -> Title assigned within IDX Broker MW
		 * url  -> Link to the widget hosted in our MW
		 */
		extract( $args ); // vars contained within
		echo $before_widget;
		echo $before_title;

		if ( ( ! empty( $instance['title'] ) ) && $instance['title'] == '!%hide_title!%' ) { // if client puts in '!%hide_title!%' for widget title in WP front-end, will display no title
			echo '';
		} elseif ( ! empty( $instance['title'] ) ) { // else if WP title isn't empty, display that
			echo $instance['title'];
		} else {
			echo $widget_name;
		}
		// if no WP title and not specifically set to 'none', display IDX Widget title which is in $args param
		// only load leaflet scripts and styles for map search widget. WP takes care of duplicates automatically
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

	} // end widget function

	/**
	 * form will display a editing UI for changing widget title
	 *
	 * @param  [array] $instance [description]
	 * @return [type]           [description]
	 */
	public function form( $instance ) {
		$title = ( isset( $instance['title'] ) ) ? $instance['title'] : ''; // if instance has a title already, display that pup, otherwise display empty input
		echo "<div id='{$this->widget_id}-admin-panel'>";
		echo "<label for='{$this->get_field_id('title')}'>Widget Title:</label>";
		echo "<input type='text' name='{$this->get_field_name('title')}' id='{$this->get_field_id('title')}' value='{$title}' />";
		echo '<br /><br />';
		echo '</div>';

	} // end form fn

	/**
	 * update will take new values inputted into the editor and return the values after stripping the tags
	 *
	 * @param  [array] $new_instance [new vals]
	 * @param  [array] $old_instance [old vals]
	 * @return [array]               [new vals w/ tags stripped]
	 */
	public function update( $new_instance, $old_instance ) {
		$return             = array();
		$return['title']    = strip_tags( $new_instance['title'] );
		$return['text']     = strip_tags( $new_instance['text'] );
		$return['textarea'] = strip_tags( $new_instance['textarea'] );
		return $return;
	} // end update fn

} // end Idx_Widget class definition
