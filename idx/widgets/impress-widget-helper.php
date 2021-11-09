<?php
/**
 * IMPress-Widget-Helper.php
 *
 * Contains helper functions to be used across multiple IMPress widgets.
 *
 * @package WordPress
 * @subpackage IMPress for IDX Broker
 * @since 3.0.0
 */

/**
 * Price Selector function.
 *
 * @param mixed $property - A real or Supplemental listing from IDX Broker.
 * @return string
 */
function price_selector( $property ) {

	$listing_price   = empty( $property['listingPrice'] ) ? '' : $property['listingPrice'];
	$options         = get_option( 'plugin_wp_listings_settings', 0 );
	$currency_symbol = ( empty( $options['wp_listings_currency_symbol'] ) || 'none' === $options['wp_listings_currency_symbol'] ) ? '$' : $options['wp_listings_currency_symbol'];

	// Supplemental listings.
	if ( ! empty( $property['idxID'] ) && 'a999' === $property['idxID'] ) {
		// Sold supplemental listings.
		if ( stripos( $property['status'], 'sold' ) !== false || stripos( $property['status'], 'closed' ) !== false ) {
			return empty( $property['soldPrice'] ) ? $listing_price : $currency_symbol . number_format( $property['soldPrice'] );
		}
		// Return rntLsePrice if rntLse field is set to any value besides 'neither'.
		if ( 'neither' !== $property['rntLse'] ) {
			return empty( $property['rntLsePrice'] ) ? $listing_price : $currency_symbol . number_format( $property['rntLsePrice'] );
		}
		// Return listing price if not sold or rental/lease.
		return $listing_price;
	}

	$prop_type = empty( $property['propType'] ) ? '' : $property['propType'];

	// If $prop_type is empty, try for idxPropType.
	if ( empty( $prop_type ) ) {
		$prop_type = empty( $property['idxPropType'] ) ? '' : $property['idxPropType'];
	}

	// Active non-supplemental listings.
	if ( ! empty( $property['idxStatus'] ) && 'active' === $property['idxStatus'] ) {
		if ( stripos( $prop_type, 'lease' ) !== false || stripos( $prop_type, 'rent' ) !== false ) {
			return empty( $property['rntLsePrice'] ) ? $listing_price : $currency_symbol . number_format( $property['rntLsePrice'] );
		}
		return $listing_price;
	}

	// Off market non-supplemental listings.
	if ( ! empty( $property['idxStatus'] ) && 'sold' === $property['idxStatus'] ) {
		return empty( $property['soldPrice'] ) ? $listing_price : $currency_symbol . number_format( $property['soldPrice'] );
	}

	return $listing_price;
}
