<?php
wp_nonce_field( 'wp_listings_metabox_save', 'wp_listings_metabox_nonce' );

global $post;

echo '<div style="width: 45%; float: left">';

foreach ( (array) $this->property_details['col1'] as $label => $key ) {
	printf( '<p><label>%s<br /><input type="text" name="wp_listings[%s]" value="%s" /></label></p>', esc_html( $label ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
}

echo '</div>';

echo '<div style="width: 45%; float: right;">';

foreach ( (array) $this->property_details['col2'] as $label => $key ) {
	printf( '<p><label>%s<br /><input type="text" name="wp_listings[%s]" value="%s" /></label></p>', esc_html( $label ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
}

echo '</div><br style="clear: both;" />';

echo '<h4>Extended Details:</h4>';
echo '<div style="width: 45%; float: left">';

foreach ( (array) $this->extended_property_details['col1'] as $label => $key ) {
	printf( '<p><label>%s<br /><input type="text" name="wp_listings[%s]" value="%s" /></label></p>', esc_html( $label ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
}

echo '</div>';

echo '<div style="width: 45%; float: right;">';

foreach ( (array) $this->extended_property_details['col2'] as $label => $key ) {
	printf( '<p><label>%s<br /><input type="text" name="wp_listings[%s]" value="%s" /></label></p>', esc_html( $label ), esc_attr( $key ), esc_attr( get_post_meta( $post->ID, $key, true ) ), esc_attr( get_post_meta( $post->ID, $key, true ) ) );
}

echo '</div><br style="clear: both;" />';

// Advanced fields area.

$options = get_option( 'plugin_wp_listings_settings' );
if ( ! empty( $options['wp_listings_display_advanced_fields'] ) ) {
	$adv_fields = generate_adv_field_list( $post );
	if ( ! empty( $adv_fields['col1'] ) || ! empty( $adv_fields['col2'] ) ) {
		echo '<h4>Advanced Fields:</h4>';

		echo '<div style="width: 45%; float: left">';
		foreach ( $adv_fields['col1'] as $key => $value ) {
			printf( '<p><label>%s:<br /><input type="text" name="wp_listings[_advanced_fields][%s]" value="%s" /></label></p>', esc_html( get_adv_field_display_name( $key ) ), esc_attr( $key ), esc_attr( $value ) );
		}
		echo '</div>';

		echo '<div style="width: 45%; float: right;">';
		foreach ( $adv_fields['col2'] as $key => $value ) {
			printf( '<p><label>%s:<br /><input type="text" name="wp_listings[_advanced_fields][%s]" value="%s" /></label></p>', esc_html( get_adv_field_display_name( $key ) ), esc_attr( $key ), esc_attr( $value ) );
		}
		echo '</div><br style="clear: both;" />';
	}
}

// End advanced fields area.

echo '<div style="width: 45%; float: left">';
echo '<h4>Price Options:</h4>';
printf( '<p><label>Hide the price from visitors?<br /> <input type="checkbox" name="wp_listings[_listing_hide_price]" value="1" %s /></label></p>', checked( get_post_meta( $post->ID, '_listing_hide_price', true ), 1, 0 ) );

echo '<p><label>Text to display instead of price (or leave blank):<br />';
printf( '<input type="text" name="wp_listings[_listing_price_alt]" value="%s" /></label></p>', esc_attr( htmlentities( get_post_meta( $post->ID, '_listing_price_alt', true ) ) ) );
echo '</div>';

echo '<div style="width: 90%; float: left;">';

echo '<h4>Custom Overlay Text</h4>';
echo '<p><label>Custom text to display as overlay on featured listings<br />';
printf( '<input type="text" name="wp_listings[_listing_text]" value="%s" /></label></p>', esc_attr( htmlentities( get_post_meta( $post->ID, '_listing_text', true ) ) ) );

echo '</div><br style="clear: both;" /><br /><br /><hr>';

echo '<div style="width: 100%; float: left;">';

echo '<p><label>Photo Gallery (use Add Media button to insert Gallery):<br />';

$wplistings_gallery_content   = get_post_meta( $post->ID, '_listing_gallery', true );
$wplistings_gallery_editor_id = '_listing_gallery';

$wplistings_gallery_editor_settings = [
	'wpautop'          => false,
	'textarea_name'    => 'wp_listings[_listing_gallery]',
	'editor_class'     => 'wplistings_gallery',
	'textarea_rows'    => 20,
	'tinymce'          => true,
	'quicktags'        => true,
	'drag_drop_upload' => true,
];

wp_editor( $wplistings_gallery_content, $wplistings_gallery_editor_id, $wplistings_gallery_editor_settings );

echo '</div><br style="clear: both;" /><br /><br />';

echo '<div style="width: 90%; float: left;">';

echo '<p><label>Enter Video or Virtual Tour Embed Code (<a href="https://wordpress.org/plugins/jetpack/" target="_blank" rel="nofollow">Jetpack</a> offers several <a href="http://jetpack.me/support/shortcode-embeds/" target="_blank" rel="nofollow">video shortcodes</a>.):<br />';
echo '<textarea name="wp_listings[_listing_video]" rows="5" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_video', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

echo '<div style="width: 90%; float: left;">';

echo '<div style="width: 45%; float: left">';
echo '<h4>Map Options</h4>';

if ( get_post_meta( $post->ID, '_listing_automap', 1 ) == false ) {
	update_post_meta( $post->ID, '_listing_automap', 'y' );
}
printf(
	'<p><label>Automatically insert map based on latitude/longitude? <strong>Will be overridden if a shortode is entered below.</strong><br /> <input type="radio" name="wp_listings[_listing_automap]" value="y" %s>Yes</input> <input type="radio" name="wp_listings[_listing_automap]" value="n" %s>No</input></label></p>',
	checked( get_post_meta( $post->ID, '_listing_automap', true ), 'y', 0 ),
	checked( get_post_meta( $post->ID, '_listing_automap', true ), 'n', 0 )
);
echo '</div>';
echo '<div style="clear: both; width: 45%; float: left;">';
printf( '<p><label>Latitude: <br /><input type="text" name="wp_listings[_listing_latitude]" value="%s" /></label></p>', esc_attr( get_post_meta( $post->ID, '_listing_latitude', true ) ) );
echo '</div>';
echo '<div style="width: 45%; float: right;">';
echo '<p><label>Longitude: <br /><input type="text" name="wp_listings[_listing_longitude]" value="' . esc_attr( get_post_meta( $post->ID, '_listing_longitude', true ) ) . '" /></label></p>';
echo '</div><br style="clear: both;" />';

echo '<p><label>Or enter Map Embed Code or shortcode from Map plugin (such as <a href="http://jetpack.me/support/shortcode-embeds/" target="_blank" rel="nofollow">Jetpack Shortcodes</a>, <a href="https://wordpress.org/plugins/simple-google-maps-short-code/" target="_blank" rel="nofollow">Simple Google Maps Short Code</a> or <a href="https://wordpress.org/plugins/mappress-google-maps-for-wordpress/" target="_blank" rel="nofollow">MapPress</a>):<br /><em>Recommend size: 660x300 (If possible, use 100% width, or your themes content width)</em><br />';
echo '<textarea name="wp_listings[_listing_map]" rows="5" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_map', true ) ) ) . '</textarea></label></p>';

echo '</div>';

echo '<div style="width: 90%; float: left;">';
echo '<h4>Contact Form</h4>';

echo '<p><label>If you use a Contact Form plugin, you may enter the Contact Form shortcode here. Otherwise, the single listing template will use a default contact form:<br />';
echo '<textarea name="wp_listings[_listing_contact_form]" rows="1" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_contact_form', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';
