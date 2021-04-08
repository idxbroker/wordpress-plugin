<?php
namespace IDX\Views;

wp_enqueue_script( 'wp-api' );

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

?>

<style>
	.update-nag {
		display: none;
	}
	#wpfooter {
		display: none;
	}
	#wpbody-content * :not('.idx-wp-app') {
		visibility: hidden;
	}
</style>

<div id="app"></div>
