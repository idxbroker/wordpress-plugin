<?php
namespace IDX\Views;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

global $api_error;
$idx_api = new \IDX\Idx_Api();

// get wpl options if they exist
$wpl_options = get_option( 'plugin_wp_listings_settings' );

if ( ! $api_error ) {
	$system_links = $idx_api->idx_api_get_systemlinks();
	if ( is_wp_error( $system_links ) ) {
		$api_error = $system_links->get_error_message();
	}
}
/**
 * check wrapper page exist or not
 */
$wrapper_page_id  = get_option( 'idx_broker_dynamic_wrapper_page_id' );
$post_title       = '';
$wrapper_page_url = '';
if ( $wrapper_page_id ) {
	if ( ! get_page_uri( $wrapper_page_id ) ) {
		update_option( 'idx_broker_dynamic_wrapper_page_id', '', false );
		$wrapper_page_id = '';
	} else {
		$post_title       = get_post( $wrapper_page_id )->post_title;
		$wrapper_page_url = get_page_link( $wrapper_page_id );
	}
}

?>

<div id="idxPluginWrap" class="wrap">
	<div>
		<a href="http://www.idxbroker.com" target="_blank" class="logo-link">
				<div id="logo"></div>
		</a>
		<h2 class="flft">IMPress for IDX Broker&reg; Settings</h2>
	</div>
	<form method="post" action="options.php" id="idx_broker_options">
		<?php wp_nonce_field( 'update-options' ); ?>
		<div id="blogUrl" style="display: none;" ajax="<?php bloginfo( 'wpurl' ); ?>"></div>
				<div id="genSettings">
					<h3 class="hndle">
						Get an API Key<a href="https://middleware.idxbroker.com/mgmt/apikey.php" target="_blank"><img class="help-icon" src="<?php echo plugins_url( '../../assets/images/helpIcon.svg', __FILE__ ); ?>" alt="help"></a>
					</h3>
					<div class="inlineBlock">
						<div>
							<label for="idx_broker_apikey">Enter Your API Key: </label>
							<input name="idx_broker_apikey" type="text" id="idx_broker_apikey" value="<?php echo get_option( 'idx_broker_apikey' ); ?>" />
							<input type="button" name="api_update" id="api_update" value="Refresh Plugin Options" class="button-primary" />
							<span class="refresh_status"></span>
						</div>
						<p class="error hidden" id="idx_broker_apikey_error">
							Please enter your API key to continue.
							<br>
							If you do not have an IDX Broker account, please contact the IDX Broker team at 800-421-9668.
						</p>
						<?php
						if ( $api_error ) {
							echo '<p class="error" style="display:block;">' . $api_error . '</p>';
						}
						?>
					</div>
				</div>
				<div id="refresh-cron-schedule" class="inlineBlock">
					<h3>Background Cron:</h3>
					<p>Choose how often the background refresh runs:</p>
					<?php
					$schedules = wp_get_schedules();


						$idx_cron_setting = get_option( 'idx_cron_schedule' );
					?>
				  <label for="idx_cron_schedule">Choose Schedule: </label>
					<select id="idx-cron-schedule" name="idx_cron_schedule">
					<?php
					foreach ( $schedules as $schedule_name => $schedule ) {
						echo '<option value="' . __( $schedule_name ) . '"' . selected( $idx_cron_setting, $schedule_name ) . '>' . __( $schedule['display'] ) . '</option>';
					}
					?>
						<option value="disabled" <?php selected( $idx_cron_setting, 'disabled' ); ?>>Disabled</option>
					</select>
					<?php submit_button( 'Update Schedule' ); ?>
				</div>
				<!-- dynamic wrapper page -->
				<div id="dynamic_page">
					<h3>Create the Global Wrapper<a href="http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper" target="_blank"><img class="help-icon" src="<?php echo plugins_url( '../../assets/images/helpIcon.svg', __FILE__ ); ?>" alt="help"></a></h3>
					<div class="help-text">Setting this up will match the IDX pages to your website design automatically every few hours.<div>Example: Properties</div></div>
					<label for="idx_broker_dynamic_wrapper_page_name">Page Name:</label>
					<input name="idx_broker_dynamic_wrapper_page_name" type="text" id="idx_broker_dynamic_wrapper_page_name" value="<?php echo $post_title; ?>" />
					<input name="idx_broker_dynamic_wrapper_page_id" type="hidden" id="idx_broker_dynamic_wrapper_page_id" value="<?php echo get_option( 'idx_broker_dynamic_wrapper_page_id' ); ?>" />
					<input type="button" class="button-primary" id="idx_broker_create_wrapper_page" value="<?php echo $post_title ? 'Update' : 'Create'; ?>" />
					<?php
					if ( $wrapper_page_id != '' ) {
						?>
						<input type="button" class="button-secondary" id="idx_broker_delete_wrapper_page" value="Delete" />
										<?php
					}
					?>
					<span class="wrapper_status"></span>
					<p class="error hidden">Please enter a page title</p>
					<span id="protocol" class="label hidden"></span>
					<input id="page_link" class="hidden" type="text" value="<?php echo $wrapper_page_url; ?>" readonly>
				</div>

				<!-- Add reCAPTCHA field if a key has not already been entered in WP Listings settings -->
				<?php if ( $wpl_options['wp_listings_captcha_site_key'] == '' || $wpl_options['wp_listings_captcha_site_key'] == null ) { ?>
					<div id="recaptcha-key">
						<h3>Add Google reCAPTCHA</h3>
						<div class="help-text">You can choose to add Google reCAPTCHA v2 to prevent spam lead signups. To use Google reCAPTCHA, you must first <a href="https://www.google.com/recaptcha/admin" target="_blank">sign up for a v2 key</a>, then enter the site key below:</div>
						<label for="idx_recaptcha_site_key">Site Key:</label>
						<input name="idx_recaptcha_site_key" type="text" id="idx_recaptcha_site_key" size="40" value="<?php echo get_option( 'idx_recaptcha_site_key' ); ?>" />
						<input type="button" class="button-primary" id="idx_update_recaptcha_key" value="Update" />
					</div>
					<?php
}
		settings_fields( 'idx-platinum-settings-group' );
?>
	</form>

</div>
