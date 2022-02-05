<?php

add_action( 'wp_loaded', new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode() );
/**
 * Listing Inquiry Form
 * Default inquiry form for listing posts.
 *
 * @return mixed
 */
function listing_inquiry_form( $post ) {
	?>
	<div id="contact-tabs" class="contact-data">
		<ul>
			<li><a href="#inquiry-tab">Listing Inquiry</a></li>

			<li><a href="#signup-tab">Sign Up</a></li>
		</ul>
		<div id="inquiry-tab" itemprop="signup">
			<form id="listing-inquiry-form" name="" onsubmit="return listingInquiry(event)">
				<input type="hidden" name="inquiryPostID" value="<?php echo esc_attr( $post->ID ); ?>" />
				<label>First Name:</label>
				<input type="text" name="inquiryFirstname" id="inquiryFirstname" required/>
				<label>Last Name:</label>
				<input type="text" name="inquiryLastname" id="inquiryLastname" required/>
				<label>Email:</label>
				<input type="email" name="inquiryEmail" id="inquiryEmail" required/>
				<label>Phone:</label>
				<input type="tel" name="inquiryPhone" id="inquiryPhone" />
				<label>Comment:</label>
				<textarea name="inquiryComment" id="inquiryComment" form="listing-inquiry-form" rows="5"></textarea>
				<input id="submit-inquiry-button" type="submit"></input>
				<div id="loading-icon-container"><span class="dashicons dashicons-update"></span></div>
			</form>
		</div>

		<div id="signup-tab" itemprop="signup">
			<div id="signup-notification">Please sign up for a Listing Manager account below to inquire about this listing</div>
			<?php echo do_shortcode( '[impress_lead_signup new_window="1"]' ); ?>
		</div>
	<?php
}
