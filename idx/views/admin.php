<?php
namespace IDX\Views;

wp_enqueue_script( 'wp-api' );

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
}

?>

<style>
	.update-nag {
		display: none;
	}
	#wpfooter {
		display: none;
	}
	#wpbody-content>*:not(.idx-wp-app) {
		display: none !important;
	}
</style>

<div id="app"></div>
