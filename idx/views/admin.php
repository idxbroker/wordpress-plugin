<?php
namespace IDX\Views;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

?>

<style>
	#wpbody-content {
		display: none;
	}
	#wpfooter {
		display: none;
	}
</style>

<div id="app"></div>
