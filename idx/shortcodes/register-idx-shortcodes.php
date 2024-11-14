<?php
namespace IDX\Shortcodes;

/**
 * Register_Idx_Shortcodes class.
 */
class Register_Idx_Shortcodes {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		// Adding shortcodes
		add_shortcode( 'idx-platinum-link', array( $this, 'show_link' ) );
		add_shortcode( 'idx-platinum-saved-link', array( $this, 'show_saved_link' ) );
		add_shortcode( 'idx-platinum-system-link', array( $this, 'show_system_link' ) );
		add_shortcode( 'idx-platinum-widget', array( $this, 'show_widget' ) );
		add_shortcode( 'idx-wrapper-tags', array( $this, 'wrapper_tags' ) );
	}

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	const SHORTCODE_SYSTEM_LINK = 'idx-platinum-system-link';
	const SHORTCODE_SAVED_LINK  = 'idx-platinum-saved-link';
	const SHORTCODE_WIDGET      = 'idx-platinum-widget';

	/**
	 * Function to show a idx widget with shortcode of type:
	 * [idx-platinum-widget id="widget-id"]
	 *
	 * @param array $atts
	 * @return html code for showing the widget/ bool false
	 */
	public function show_widget( $atts ) {
		extract(
			shortcode_atts(
				array(
					'id' => null,
				),
				$atts
			)
		);

		if ( ! is_null( $id ) ) {
			$id = esc_attr($id);
			$url    = $this->get_widget_url( $id );
			$widget = '';
			$widget_id = explode( '-', $id );
			$widget   .= '<script type="text/javascript" id="idxwidgetsrc-' . ( empty( $widget_id[1] ) ? '' : $widget_id[1] ) . '" src="' . $url . '"></script>';
			return $widget;
		} else {
			return false;
		}
	}

	/**
	 * FUnction to show a idx system link with shortcode of type:
	 * [idx-platinum-system-link title="title here"]
	 *
	 * @param array $atts
	 * @return string|boolean
	 */
	public function show_system_link( $atts ) {
		extract(
			shortcode_atts(
				array(
					'id'    => null,
					'title' => null,
				),
				$atts
			)
		);

		if ( ! is_null( $id ) ) {
			$id = esc_attr($id);
			$link = $this->idx_get_link_by_uid( $id, 0 );
			if ( is_object( $link ) ) {
				if ( ! is_null( $title ) ) {
					$title = esc_attr($title);
					$link->name = $title;
				}
				return '<a href="' . $link->url . '">' . $link->name . '</a>';
			}
		} else {
			return false;
		}
	}

	/**
	 * Function to show a idx link with shortcode of type:
	 * [idx-platinum-link title="title here"]
	 *
	 * @param array $atts
	 * @return html code for showing the link/ bool false
	 */
	public function show_link( $atts ) {
		extract(
			shortcode_atts(
				array(
					'title' => null,
				),
				$atts
			)
		);

		if ( ! is_null( $title ) ) {
			$title = esc_attr($title);
			$page      = get_page_by_title( $title );
			return '<a href="' . get_permalink( $page->ID ) . '">' . $page->post_title . '</a>';
		} else {
			return false;
		}
	}

	/**
	 * FUnction to show a idx ssaved link with shortcode of type:
	 * [idx-platinum-saved-link title="title here"]
	 *
	 * @param array $atts
	 * @return string|boolean
	 */
	public function show_saved_link( $atts ) {
		extract(
			shortcode_atts(
				array(
					'id'    => null,
					'title' => null,
				),
				$atts
			)
		);

		if ( ! is_null( $id ) ) {
			$id = esc_attr($id);
			$link = $this->idx_get_link_by_uid( $id, 1 );
			if ( is_object( $link ) ) {
				if ( ! is_null( $title ) ) {
					$title = esc_attr($title);
					$link->name = $title;
				}
				return '<a href="' . $link->url . '">' . $link->name . '</a>';
			}
		} else {
			return false;
		}
	}

	/**
	 * idx_get_link_by_uid function.
	 *
	 * @access public
	 * @param mixed $uid
	 * @param int   $type (default: 0)
	 * @return void
	 */
	public function idx_get_link_by_uid( $uid, $type = 0 ) {
		if ( $type == 0 ) {
			// if the cache has expired, send an API request to update them. Cache expires after 2 hours.
			$idx_links = $this->idx_api->idx_api_get_systemlinks();
		} elseif ( $type == 1 ) {
			$idx_links = $this->idx_api->idx_api_get_savedlinks();
		}

		$selected_link = '';

		if ( $idx_links ) {
			foreach ( $idx_links as $link ) {
				if ( strcmp( $link->uid, $uid ) == 0 ) {
					$selected_link = $link;
				}
			}
		}
		return $selected_link;
	}

	/**
	 * wrapper_tags function.
	 *
	 * @access public
	 * @return void
	 */
	public function wrapper_tags() {
		return "\r\n<div id=\"idxStart\"></div>\r\n<div id=\"idxStop\"></div>\r\n";
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
			if ( ! empty( $widget->uid ) && $widget_uid === $widget->uid ) {
				return $widget->url;
			}
		}
		return false;
	}

}
