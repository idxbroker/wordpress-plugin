<?php

namespace IDX\Notice;

/**
 * Beging Notice class
 * Holds notice state and logic for notices being displayed
 *
 * @since 2.5.10
 */
class Notice {
	/**
	 * Begin construct function
	 *
	 * @since 2.5.10
	 * @param mixed $handle contains the handle.
	 * @param mixed $message contains the message.
	 * @param mixed $type contais tne notice type.
	 * @param mixed $url contains the link for the notice.
	 * @param mixed $link_text contains the text for the link.
	 */
	public function __construct( $handle, $message, $type, $url = '', $link_text = 'info' ) {
		$this->name      = $handle;
		$this->message   = $message;
		$this->type      = $type;
		$this->url       = $url;
		$this->link_text = $link_text;
	}

	/**
	 * Begin create_notice function
	 * Displays notice only if in an IMPress menu page
	 *
	 * @since 2.5.10
	 * @return null
	 */
	public function create_notice() {
		$current_page = get_current_screen();
		if ( 'idx-broker' !== $current_page->parent_file ) {
			return;
		}

		\IDX\Views\Notice::create_notice( $this->name, $this->message, $this->type, $this->url, $this->link_text );
	}
}
