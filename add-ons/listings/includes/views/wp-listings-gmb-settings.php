<?php
$google_my_business_manager = WPL_Google_My_Business::get_instance();
$google_my_business_options = $google_my_business_manager->wpl_get_gmb_settings_options();

if ( class_exists( 'Idx_Broker_Plugin' ) ) {
	$idx_api     = new \IDX\Idx_Api();
	$gmb_options = get_option( 'wp_listings_google_my_business_options' );

	if ( $idx_api->engage_account_type() && ! empty( $gmb_options['refresh_token'] ) ) {
		wp_enqueue_script( 'impress-gmb-settings', IMPRESS_IDX_URL . 'assets/js/google-my-business-settings.min.js', [], '1.0.0', true );
		echo '<div id="gmb-settings-app"></div>';
	}
}

// If no refresh token saved, show Google login button.
if ( empty( $google_my_business_options['refresh_token'] ) ) {
	wp_enqueue_style( 'impress-gmb-login', IMPRESS_IDX_URL . 'assets/css/impress-gmb-login.min.css', [], '1.0.0' );
	wp_enqueue_script( 'impress-gmb-login', IMPRESS_IDX_URL . 'assets/js/impress-gmb-login.min.js', [], '1.0.0', true );
	wp_localize_script(
		'impress-gmb-login',
		'impressGmbAdmin',
		[
			'nonce-gmb-initial-tokens' => wp_create_nonce( 'wpl_gmb_set_initial_tokens_nonce' ),
		]
	);

	_e( '<div class="gmb-login-container">
		<h3 style="margin-bottom:0px;">Give Leads More Ways to Reach You</h3>
		<hr>
		<p><a onclick="showLightbox();" href="#">Log in</a> or <a href="https://google.com/business" target="_blank">Create a free Google My Business Profile</a> to connect with IMPress Listings.</p>

			<h3 style="margin-bottom:0px;">Connect to Google My Business</h3>
			<hr>
			<p>Once verified, connect your Google My Business (GMB) profile to IMPress Listings, to generate timely posts and photos of your listings and more… automatically.</p>

			<p>The automatic scheduler can be used to create and share posts to highlight your own featured listings as well as open house announcements, recent sales, local expertise and more.</p>

			<p>Posts have the potential to draw leads and clients directly to your IDX-enabled website for more home search opportunities. Google My Business posts are archived on a weekly basis, so automating the process with the scheduler is an easy way to maintain your real estate business’s online presence.</p> 

			<p><strong>Automatic posting requires a verified Google My Business account with a verified location.</strong>
			<!-- Tooltip -->
			<span class="tooltip"><span class="dashicons dashicons-editor-help wpl-gmb-main-desc-help"></span>
				<span class="tooltiptext">

				Posts made to Google My Business will be of the type "What&apos;s New". For more information about local posts, visit Google&apos;s <a href="https://support.google.com/business/answer/7662907?hl=en" target="_blank">About posts for local businesses</a> page. 

				</span>
			</span>
			</p>
		</div>',
		'wp-listings'
	);
	echo '<div class="wpl-gmb-login-button-container"></div>';

	?>

	<!-- Terms of Service Lightbox -->
	<div id="terms-lightbox" class="lightbox">
		<div class="lightbox-modal">
			<div class="lightbox-title">Terms of Service</div>
			<div class="lightbox-terms-container">
				<p>Important:</p>
				<strong>
					The IMPress Listings plugin is designed to further power and enhance the functionality of websites and applications used by real estate agents, brokers, and technology partners.
					<br><br>
					Using this plugin to publish, or otherwise make public, information related to any listing data which violates your local MLS system agreements in any way is prohibited. URLs, landing pages, listing pages, community pages, or any “linked” resources that contains IDX data must be approved for public display by your MLS system.
				</strong>
			</div>
			<div class="lightbox-button-container">
				<div class="toggle-container">
					Agree to terms:
					<input name="" id="terms-agreement-checkbox" type="checkbox" value="1" class="wpl-gmp-settings-checkbox" onchange="agreeToTermsChecked(this);" autocomplete="off">
					<label for="terms-agreement-checkbox" class="checkbox-label-slider"></label>
				</div>
				<?php
					echo '<a href="https://accounts.google.com/o/oauth2/v2/auth?
					scope=https://www.googleapis.com/auth/plus.business.manage
					&access_type=offline
					&include_granted_scopes=true
					&state=' . rawurlencode( get_admin_url() ) . '
					&redirect_uri=https://hheqsfm21f.execute-api.us-west-2.amazonaws.com/v1/initial-token
					&response_type=code
					&client_id=53079160906-ari2lj7pscegfvu89p6bqjadi60igb01.apps.googleusercontent.com
					&prompt=consent"
					id="agree-to-terms-button" 
					class="button lightbox-modal-button disabled">
					<i style="color: #4a8af4;" class="fab fa-google" aria-hidden="true"></i> Connect with GMB
					</a>';
				?>
				<button id="cancel-terms-button" class="button lightbox-modal-button" onclick="cancelLoginClicked();">Cancel</button>
			</div>
		</div>
	</div>

	<?php
}
