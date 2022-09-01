<?php

// Featured on.
global $post;

echo '<div style="width: 90%; float: left;">';

echo '<p><label>Featured on (allows shortcodes):<br /><textarea name="wp_listings[_listing_featured_on]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_featured_on', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// Home Summary.

echo '<div style="width: 90%; float: left;">';

echo '<p><label>Home Summary (allows shortcodes):<br /><textarea name="wp_listings[_listing_home_sum]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_home_sum', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// Kitchen Summary.
echo '<div style="width: 90%; float: left;">';

echo '<p><label>Kitchen Summary (allows shortcodes):<br /><textarea name="wp_listings[_listing_kitchen_sum]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_kitchen_sum', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// Living Room.
echo '<div style="width: 90%; float: left;">';

echo '<p><label>Living Room (allows shortcodes):<br /><textarea name="wp_listings[_listing_living_room]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_living_room', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// Master Suite.
echo '<div style="width: 90%; float: left;">';

echo '<p><label>Master Suite (allows shortcodes):<br /><textarea name="wp_listings[_listing_master_suite]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_master_suite', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// School and Neighborhood Info.
echo '<div style="width: 90%; float: left;">';

echo '<p><label>School and Neighborhood Info (allows shortcodes):<br /><textarea name="wp_listings[_listing_school_neighborhood]" rows="5" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_school_neighborhood', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';

// Disclaimer.
echo '<div style="width: 90%; float: left;">';

echo '<p><label>Disclaimer:<br /><textarea name="wp_listings[_listing_disclaimer]" rows="3" cols="18" style="width: 99%;">' . wp_kses_post( htmlentities( get_post_meta( $post->ID, '_listing_disclaimer', true ) ) ) . '</textarea></label></p>';

echo '</div><br style="clear: both;" />';
