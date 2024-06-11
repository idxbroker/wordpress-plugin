<?php

/**
 * Loads the listing location on the map.
 * 
 * @return mixed
 */
function load_listing_on_map ( $post, $options ) {
    ( $options['wp_listings_gmaps_api_key'] ) ? $map_key = $options['wp_listings_gmaps_api_key'] : $map_key = '';
    
    if ($map_key == '' || $map_key == null) {
        return;
    }
    
    $map_info_content = sprintf( '<p style="font-size: 14px; margin-bottom: 0;">%s<br />%s %s, %s</p>', get_post_meta( $post->ID, '_listing_address', true ), get_post_meta( $post->ID, '_listing_city', true ), get_post_meta( $post->ID, '_listing_state', true ), get_post_meta( $post->ID, '_listing_zip', true ) );
    
    wp_enqueue_script( 'idxb-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key . '&loading=async&callback=initialize&libraries=marker', [], '1.0', 'async' );
    echo '
    <script>
        async function initialize() {
            const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");
            var mapCanvas = document.getElementById(\'map-canvas\' );
            var myLatLng = new google.maps.LatLng(' . esc_js( get_post_meta( $post->ID, '_listing_latitude', true ) ) . ', ' . esc_js( get_post_meta( $post->ID, '_listing_longitude', true ) ) . ')
            var mapOptions = {
                center: myLatLng,
                zoom: 14,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapId: "IDX_Listing_Location"
            }
            
            var infoContent = \' ' . $map_info_content  . ' \';

            var infowindow = new google.maps.InfoWindow({
                content: infoContent
            });

            var map = new google.maps.Map(mapCanvas, mapOptions);

            const marker = new google.maps.marker.AdvancedMarkerElement({
                map,
                position: myLatLng,
            });

            infowindow.open(map, marker);
        }
        setTimeout(() => {
            google.maps.event.addListener(window, \'load\', initialize );
          }, 1000);
    </script>
    ';
    echo '<div id="listing-map"style="width: 100%; height: 350px;"><h3>Location Map</h3><div id="map-canvas" style="width:100%;height:100%;"></div></div><!-- .listing-map -->';
}