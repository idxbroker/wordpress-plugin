<?php
namespace IDX\Backward_Compatibility;

class Migrate_Legacy_Widgets {

	public $idx_api;

	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();

		// If widgets have already been migrated, do nothing.
		$migrated = get_option( 'idx_migrated_old_mw_widgets' );
		if ( $migrated ) {
			return;
		}

		add_action( 'wp_loaded', array( $this, 'convert_mw_widgets' ), 10, 1 );
		//add_action( 'wp_loaded', array( $this, 'get_and_convert_active_mw_widgets' ) );
	}

	/**
	 * Get all the active MW widgets.
	 *
	 * @return array|false  array of widget_id and widget_instance, or false if none active
	 */
	public function get_active_mw_widgets() {
		$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();

		if ( ! is_array( $idx_widgets ) ) {
			return;
		}

		foreach ( $idx_widgets as $widget ) {
			// Build our widget_base_id
			$widget_base_id = 'idx' . str_replace( '-', '_', $widget->uid );

			// Get our widget instances based on the option name widget_{widget_base_id}
			$widget_instances = get_option( 'widget_' . $widget_base_id );

			// If there are instances, then loop through them instances and find actives.
			if ( false !== $widget_instances ) {
				foreach ( $widget_instances as $instances => $instance ) {
					// If this $instance is an array, we have a widget here. Add it to the array.
					if ( is_array( $instance ) ) {
						$key = ( is_int( $instances ) ) ? $instances : null;
						if ( null !== $key ) {
							$active_widgets[] = array(
								'widget_id'       => $widget_base_id . '-' . $key,
								'widget_instance' => $instance,
							);
						}
					}
				}
			}
		}

		return ( $active_widgets ) ? $active_widgets : false;
	}


	public function convert_mw_widgets( $active_widgets ) {
		$active_widgets = $this->get_active_mw_widgets();

		foreach ( $active_widgets as $active_widget ) {
			$widgets_converted[] = $this->convert_widget( $active_widget );
		}

		if ( is_array( $widgets_converted ) ) {
			// update_option();
			return true;
		}

		return;
	}
	

	// public function get_and_convert_active_mw_widgets() {
	// 	$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();

	// 	if ( ! is_array( $idx_widgets ) ) {
	// 		return;
	// 	}

	// 	foreach ( $idx_widgets as $widget ) {
	// 		$widget_base_id = 'idx' . str_replace( '-', '_', $widget->uid );
	// 		$widget_instances = get_option( 'widget_' . $widget_base_id );
	// 		$sidebar_widgets = get_option( 'sidebars_widgets' );
	// 		if ( false !== $widget_instances ) {
	// 			foreach ( $widget_instances as $instances => $instance ) {
	// 				// If it's an array, we have a widget here. Convert it.
	// 				if ( is_array( $instance ) ) {
	// 					$key = ( is_int( $instances ) ) ? $instances : null;
	// 					if ( null !== $key ) {
	// 						$active_widgets[] = $widget->uid . '-' . $key;
	// 						$this->convert_widget( $widget->uid, $key, $instance, $sidebar_widgets );
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}

	// 	return ( $active_widgets ) ? $active_widgets : false;
	// }

	public function convert_widget( $active_widget ) {
		if ( ! is_array( $active_widget ) ) {
			return null;
		}
		var_dump($active_widget);
		die;
		// Get the new widget instances.
		$new_widget_instance = get_option( 'widget_impress_idx_dashboard_widget' );

		// Get replaceable widgets.
		$replaceable_widgets = $this->get_replaceable_widgets( $active_widget['widget_instance'] );
		var_dump($replaceable_widgets);
		die;

		// Loop through registered sidebars (widget areas).
		foreach ( $sidebar_widgets as $widget_area_ids => $widgets ) {
		//foreach ( $sidebar_widgets as $widget_areas ) {
			// Make sure it's an array.
			if ( ! is_array( $widgets ) ) {
				return;
			}

			// Loop through widget areas and get widget id's (names).
			foreach ( $widgets as $widget_id ) {
				// If current widget ID matches the widget_base_id+key, this is the one we need to replace.
				if ( $widget_base_id . '-' . $key === $widget_id ) {
					// Set current widget id, which is the $sidebar_widgets array key.
					$current_widget_area_id = $widget_area_ids;
					// Build new widget data. Use existing title and match up URL.
					$new_widget_data = array(
						'title' => $instance['title'],
						'url'   => $this->get_widget_url( $widget_uid ),
					);

					// Retrieve the key of the next widget instance.
					$numeric_keys = array_filter( array_keys( $new_widget_instance ), 'is_int' );
					$next_key = $numeric_keys ? max( $numeric_keys ) + 1 : 2;

					// Merge our new widget data into the new widget instance.
					$new_widget_instance[$next_key] = $new_widget_data;

					// Update the widget option with the new widget instance data.
					// update_option( 'widget_impress_idx_dashboard_widget', $new_widget_instance );

					// Set $new_widget with appended next key.
					$new_widget = 'widget_impress_idx_dashboard_widget' . '-' . $next_key;

					// Finally, update the sidebar widget options to maintain the position of the widget in the sidebar array.
					//var_dump( $sidebar_widgets );
					$sidebar_widgets[ $current_widget_area_id ][ $widgets ] = $new_widget;
					echo '<hr /><hr />';
					//var_dump( $sidebar_widgets );
					// update_option( 'sidebars_widgets', $sidebar_widgets );
				}
			}

			// if ( is_array( $widgets ) ) {
			// 	// Loop through widget areas and get widget id's.
			// 	foreach ( $widget_areas as $widget_key => $widget_id ) {
			// 		var_dump(key($widget_areas));
			// 		// If current widget ID matches the widget_base_id+key, this is the one we need to replace.
			// 		if ( $widget_base_id . '-' . $key === $widget_id ) {
			// 			//$widget_area_id = ?;
			// 			//var_dump($widget_area_id);
			// 			// Build new widget data. Use existing title and match up URL.
			// 			$new_widget_data = array(
			// 				'title' => $instance['title'],
			// 				'url'   => $this->get_widget_url( $widget_uid ),
			// 			);

			// 			// Retrieve the key of the next widget instance.
			// 			$numeric_keys = array_filter( array_keys( $new_widget_instance ), 'is_int' );
			// 			$next_key = $numeric_keys ? max( $numeric_keys ) + 1 : 2;

			// 			// Merge our new widget data into the new widget instance.
			// 			$new_widget_instance[$next_key] = $new_widget_data;

			// 			//var_dump($new_widget_instance);

			// 			// Update the option with the new widget instance data.
			// 			//update_option( 'widget_impress_idx_dashboard_widget', $new_widget_instance );

			// 			// Set $new_widget with appended key.
			// 			$new_widget = 'widget_impress_idx_dashboard_widget' . '-' . $next_key;
			// 			// Update the sidebar widget options to maintain the position of the widget in the sidebar array.
			// 			//var_dump( $sidebar_widgets );
			// 			$sidebar_widgets[ $widget_area_id ][ $widget_key ] = $new_widget;
			// 			echo '<hr /><hr />';
			// 			//var_dump( $sidebar_widgets );
			// 			// update_option( 'sidebars_widgets', $sidebar_widgets );
			// 		}
			// 	}
			// }
		}
		//}
	}

	public function get_replaceable_widgets( $widget_instance ) {
		if ( $widget_instance)
			var_dump($widget_instance);
	}

	public function get_widget_url( $widget_uid ) {
		$idx_widgets = $this->idx_api->idx_api_get_widgetsrc();
		foreach ( $idx_widgets as $widgets ) {
			if ( $widget_uid === $widget->uid ) {
				return $widget->url;
			}
		}
		return false;
	}
}
