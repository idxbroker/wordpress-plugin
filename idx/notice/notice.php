<?php

namespace IDX\Notice;

/**
 * Notice - Holds notice state and logic for notices being displayed.
 */
class Notice {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed  $handle Handle.
	 * @param mixed  $message Message.
	 * @param mixed  $type Type.
	 * @param string $url (default: '') URL.
	 * @param string $link_text (default: 'info') Link Text.
	 */
	public function __construct( $handle, $message, $type, $url = '', $link_text = 'info' ) {
		$this->name      = $handle;
		$this->message   = $message;
		$this->type      = $type;
		$this->url       = $url;
		$this->link_text = $link_text;
	}


	/**
	 * Displays notice only if in an IMPress menu page.
	 *
	 * @access public
	 */
	public function create_notice() {
		$current_page = get_current_screen();
		if ( 'idx-broker' !== $current_page->parent_file ) {
			return;
		}

		\IDX\Views\Notice::create_notice( $this->name, $this->message, $this->type, $this->url, $this->link_text );
	}
}
