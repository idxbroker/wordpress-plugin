<?php
/**
 * Migrate Legacy Widgets.
 *
 * @package idxbroker-platinum
 */

/* Exit if accessed directly. */
namespace IDX\Backward_Compatibility;

defined( 'ABSPATH' ) || exit;


/**
 * Migrate_Legacy_Widgets class.
 */
class Migrate_Legacy_Widgets {

	/**
	 * IDX API.
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

		// For testing.
		// delete_option( 'idx_migrated_old_mw_widgets' );
		//
		// If widgets have already been migrated, do nothing.
		$migrated = get_option( 'idx_migrated_old_mw_widgets' );
		if ( $migrated ) {
			return;
		}

		// Instantiate old widget class to create the legacy widgets one last time.
		new \IDX\Widgets\Create_Idx_Widgets();

		// After everything is loaded, convert all active widgets.
		add_action( 'wp_loaded', array( $this, 'convert_mw_widgets' ) );
	}

	/**
	 * Gets all active widgets and converts them. Updates migrated option on completion.
	 *
	 * @return void
	 */
	public function convert_mw_widgets() {
		$active_widgets = $this->get_active_mw_widgets();
		if ( $active_widgets ) {
			foreach ( $active_widgets as $active_widget ) {
				$widgets_converted[] = $this->convert_widget( $active_widget );
			}
		}

		if ( $widgets_converted || false === $active_widgets ) {
			update_option( 'idx_migrated_old_mw_widgets', true, false );
		}
	}

	/**
	 * Converts the active legacy widget to the new widget.
	 *
	 * @param  array $active_widget The active widget data.
	 * @return bool                 True if successful, else false.
	 */
	public function convert_widget( $active_widget ) {
		// Do nothing if we don't have a widget.
		if ( ! is_array( $active_widget ) ) {
			return false;
		}

		// Get sidebar widgets.
		$sidebar_widgets = get_option( 'sidebars_widgets' );

		// Make sure $sidebar_widgets is an array.
		if ( ! is_array( $sidebar_widgets ) ) {
			return false;
		}

		// Loop through registered sidebars (widget areas).
		foreach ( $sidebar_widgets as $widget_area_id => $widgets ) {
			// Make sure $widgets is an array.
			if ( ! is_array( $widgets ) ) {
				return false;
			}

			// Loop through widget areas and get widget id's (names).
			foreach ( $widgets as $widget_key => $widget_id ) {
				// Get the new widget instances.
				$new_widget_instances = get_option( 'widget_impress_idx_dashboard_widget' );

				// If current widget ID matches the widget_base_id+key, this is the one we need to replace.
				if ( $active_widget['widget_base_id'] . '-' . $active_widget['key'] === $widget_id ) {
					// Build new widget data. Use existing title and match up URL using widget_uid.
					$new_widget_data = array(
						'title'  => $active_widget['widget_instance']['title'],
						'widget' => $this->get_widget_url( $active_widget['widget_uid'] ),
					);

					// Retrieve the key of the next new widget instance.
					$numeric_keys = array_filter( array_keys( $new_widget_instances ), 'is_int' );
					$next_key     = $numeric_keys ? max( $numeric_keys ) + 1 : 2;

					// Merge our new widget data into the new widget instance.
					$new_widget_instance[ $next_key ] = $new_widget_data;
					$new_widget_instances             = array_replace( $new_widget_instance, $new_widget_instances );

					// Update the widget option with the new widget instance data and delete the old one.
					update_option( 'widget_impress_idx_dashboard_widget', $new_widget_instances );
					delete_option( 'widget_' . $active_widget['widget_base_id'] );

					// Set $new_widget id with appended next key.
					$new_widget = 'impress_idx_dashboard_widget' . '-' . $next_key;

					// Finally, update the sidebar widget options with new widget id,
					// maintaining the position of the widget in the sidebar array.
					$sidebar_widgets[ $widget_area_id ][ $widget_key ] = $new_widget;
					update_option( 'sidebars_widgets', $sidebar_widgets );

					// Return true on success.
					return true;
				}
			}
		}
	}

	/**
	 * Get all the active MW widgets.
	 *
	 * @return array|false  array of active widget data, or false if none active
	 */
	public function get_active_mw_widgets() {
		$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();

		if ( ! is_array( $idx_widgets ) ) {
			return;
		}

		foreach ( $idx_widgets as $widget ) {
			// Build our widget_base_id.
			$widget_base_id = 'idx' . str_replace( '-', '_', $widget->uid );

			// Get our widget instances based on the option name widget_{widget_base_id}.
			$widget_instances = get_option( 'widget_' . $widget_base_id );

			// If there are instances, then loop through them instances and find actives.
			if ( $widget_instances && is_array( $widget_instances ) ) {
				foreach ( $widget_instances as $instances => $instance ) {
					// If this $instance is an array, we have a widget here. Add it to new active_widgets array.
					if ( is_array( $instance ) ) {
						$key = ( is_int( $instances ) ) ? $instances : null;
						if ( null !== $key ) {
							$active_widgets[] = array(
								'widget_base_id'  => $widget_base_id,
								'key'             => $key,
								'widget_uid'      => $widget->uid,
								'widget_instance' => $instance,
							);
						}
					}
				}
			} else {
				return false;
			}
		}

		return ( $active_widgets ) ? $active_widgets : false;
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
