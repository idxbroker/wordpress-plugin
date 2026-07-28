<?php

namespace IDX\Notice;

// Holds notice state and logic for notices being displayed
class Notice {
	/** @var bool When true, show on any wp-admin screen (not only IMPress IDX pages). */
	public $show_on_all_admin_pages = false;

	function __construct( $handle, $message, $type, $url = '', $link_text = 'info', $show_on_all_admin_pages = false ) {
		$this->name                   = $handle;
		$this->message                = $message;
		$this->type                   = $type;
		$this->url                    = $url;
		$this->link_text              = $link_text;
		$this->show_on_all_admin_pages = (bool) $show_on_all_admin_pages;
	}

	// Displays notice only if in an IMPress menu page
	public function create_notice() {
		if ( ! $this->show_on_all_admin_pages ) {
			$current_page = get_current_screen();
			if ( ! $current_page || 'idx-broker' !== $current_page->parent_file ) {
				return;
			}
		} elseif ( ! is_admin() ) {
			return;
		}

		\IDX\Views\Notice::create_notice( $this->name, $this->message, $this->type, $this->url, $this->link_text );
	}
}
