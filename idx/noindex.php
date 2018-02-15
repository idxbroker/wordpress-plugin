<?php

namespace IDX;

class Noindex {
	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'noindex_alert' ) );
	}

	function noindex_alert() {
		$this->yoast_alert();
	}

	private function yoast_alert() {
		$data = get_option('wpseo_xml');
		$wrapper_no_index = (boolean) $data['post_types-idx-wrapper-not_in_sitemap'];
		$pages_no_index = (boolean) $data['post_types-idx_page-not_in_sitemap'];
		if($wrapper_no_index || $pages_no_index) {
			add_action( 'admin_notices', array( $this, 'add_notice' ) );
		}
	}

	function add_notice() {
		?>
		<div class="notice notice-warning is-dismissible">
        	<p><?php _e( 'IMPress for IDX: Yoast is not allowing your IDX pages to be indexed!' ); ?></p>
    	</div>
    	<?php
	}
}
