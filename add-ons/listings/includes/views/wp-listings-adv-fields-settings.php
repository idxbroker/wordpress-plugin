<?php

$options                = get_option( 'plugin_wp_listings_settings' );
$advanced_field_options = get_option( 'wp_listings_advanced_field_display_options', [] );

// If no advanced fields are imported but the option to import is enabled, show message on what to do next.
if ( is_array( $advanced_field_options ) && count( $advanced_field_options ) < 1 && isset( $options['wp_listings_import_advanced_fields'] ) && '1' == $options['wp_listings_import_advanced_fields'] ) {
	echo '<div style="font-size:10px;line-height:21px;">
			<span class="dashicons dashicons-warning"></span>
			<span>
				Advanced field data will be gathered during the next listing auto-import, please check back later.
			</span>
		 </div>';
}

echo '
		<style>
				#idx-wp-listings-adv-fields-table {width:100%;border: 2px solid black;}
				#idx-wp-listings-adv-fields-table td {border: 1px solid black;}
				#idx-wp-listings-adv-fields-table .wpl-adv-name-field {width: 40%;padding-left: 5px;}
				#idx-wp-listings-adv-fields-table .wpl-adv-custom-name-field {width: 40%;}
				#idx-wp-listings-adv-fields-table .wpl-adv-custom-name-field input {width: 100%;}
				#idx-wp-listings-adv-fields-table .wpl-adv-show-hide {text-align: center;}
				#wpl-adv-field-button-container .button-primary {margin-left: 5px;border: 0px;}
				#wpl-adv-field-button-container button:focus {outline: 0;box-shadow: none;}
				#wpl-adv-field-button-container {display: flex;flex-direction: row-reverse;padding: 5px;padding-right: 0px;}
				#wpl-clear-custom-names-button {background-color: #E8601B;}
				#wpl-populate-custom-names-button {background-color: mediumseagreen;}
				#wpl-show-all-adv-field-button {background-color: #6698cb;}
				#wpl-hide-all-adv-field-button {background-color: #7fccde;}
				.disabled-adv-field-option {pointer-events: none;opacity: 0.4;}
				#adv-field-cusomization-container {margin-right:20px;}
				#adv-field-cusomization-container .button-primary:active {opacity: 0.7;}
		</style>
';
?>



<form id="wp-listings-adv-fields-form" onsubmit="return updateAdvFields(event)">
<input type="hidden" id="adv-fields-nonce" value="<?php echo esc_attr( wp_create_nonce( 'impress_adv_fields_settings_nonce' ) ); ?>" />
<?php
// If any advanced fields are imported, display the adv field customization table.
if ( is_array( $advanced_field_options ) && count( $advanced_field_options ) > 0 ) {
	echo '<div id="adv-field-cusomization-container" class="' . ( 0 == $options['wp_listings_import_advanced_fields'] ? 'disabled-adv-field-option' : '' ) . '">';

	echo '<h1 id="wpl-customize-adv-fields-label">IMPress Listings - Advanced Fields</h1>
			<div id="wpl-adv-field-button-container">
			<button id="wpl-hide-all-adv-field-button" class="button-primary" type="button" onclick="hideAllAdvFields()" title="Set all advanced fields to hide on listing pages">Hide All Fields</button>
			<button id="wpl-show-all-adv-field-button" class="button-primary" type="button" onclick="showAllAdvFields()" title="Set all advanced fields to display on listing pages" >Show All Fields</button>
			<button id="wpl-populate-custom-names-button" class="button-primary" type="button" onclick="populateEmptyAdvCustomNameFields()" title="Generate a best guess name for all fields currently missing a custom name" >Generate Custom Names</button>
			<button id="wpl-clear-custom-names-button" class="button-primary" type="button" onclick="clearAdvCustomNameFields()" title="Remove custom names from all fields" >Clear Custom Names</button>
		</div>
	';

	echo '<table id="idx-wp-listings-adv-fields-table">';
	echo '<tr><th>Field Name</th><th>Custom Name</th><th>Display</th></tr>';
	foreach ( $advanced_field_options as $key => $value ) {
		echo '<tr>
				<td class="wpl-adv-name-field">' . esc_html( $key ) . '</td>
				<td class="wpl-adv-custom-name-field">
					<input name="wp_listings_advanced_field_display_options[' . esc_attr( $key ) . '][custom_name]" id="' . esc_attr( $key ) . '" class="custom-adv-field-name-input" type="text" value="' . esc_attr( $value['custom_name'] ) . '" />
				</td>
				<td class="wpl-adv-show-hide">
					<div class="">
						Show <input name="wp_listings_advanced_field_display_options[' . esc_attr( $key ) . '][display_field]" id="' . esc_attr( $key ) . '-show-checkbox" class="show-radio-button" type="radio" value="show" ' . checked( 'show', $value['display_field'], false ) . ' />
						Hide <input name="wp_listings_advanced_field_display_options[' . esc_attr( $key ) . '][display_field]" id="' . esc_attr( $key ) . '-hide-checkbox" class="hide-radio-button" type="radio" value="hide" ' . checked( 'hide', $value['display_field'], false ) . ' />
					</div>
				</td>
			</tr>';
	}

	echo '</table>';
	echo '<div id="wpl-adv-field-button-container">
			<button id="wpl-hide-all-adv-field-button" class="button-primary" type="button" onclick="hideAllAdvFields()" title="Set all advanced fields to hide on single listing pages.">Hide All Fields</button>
			<button id="wpl-show-all-adv-field-button" class="button-primary" type="button" onclick="showAllAdvFields()" title="Set all advanced fields to show on single listing pages" >Show All Fields</button>
			<button id="wpl-populate-custom-names-button" class="button-primary" type="button" onclick="populateEmptyAdvCustomNameFields()" title="Generates a best guess title for any field missing a custom name." >Generate Custom Names</button>
			<button id="wpl-clear-custom-names-button" class="button-primary" type="button" onclick="clearAdvCustomNameFields()" title="Remove custom names from all fields. Press the Save Settings button to commit any changes." >Clear Custom Names</button>
		</div>
	</div>';
	echo '<hr>';
	echo '<input id="adv-field-settings-submit-button" name="submit" class="button-primary" type="submit" value="Save Settings" aria-label="Submit Advanced Field Display Changes" style="min-width:200px;"/>';
}
?>
</form>

<script>
	jQuery( function() {
		jQuery( "#post-body-content" ).tabs();
	} );
	function hideAllAdvFields() {
		document.querySelectorAll('.hide-radio-button').forEach(function(value){
			value.checked = true
		})
	}
	function showAllAdvFields() {
		document.querySelectorAll('.show-radio-button').forEach(function(value){
			value.checked = true
		})
	}
	function clearAdvCustomNameFields() {
		document.querySelectorAll('.custom-adv-field-name-input').forEach(function(value){
			value.value = ''
		})
	}
	function toggleAdvFieldImportSetting() {
		if ( document.querySelector('#wp_listings_import_advanced_fields').checked ) {
			document.querySelector('#wp_listings_display_advanced_fields_container').classList.remove('disabled-adv-field-option')
			document.querySelector('#wpl-customize-adv-fields-label').classList.remove('disabled-adv-field-option')
			document.querySelector('#adv-field-cusomization-container').classList.remove('disabled-adv-field-option')
		} else {
			document.querySelector('#wp_listings_display_advanced_fields_container').classList.add('disabled-adv-field-option')
			document.querySelector('#wpl-customize-adv-fields-label').classList.add('disabled-adv-field-option')
			document.querySelector('#adv-field-cusomization-container').classList.add('disabled-adv-field-option')
			document.querySelector('#wp_listings_display_advanced_fields').checked = false
		}
	}
	function populateEmptyAdvCustomNameFields() {
		document.querySelectorAll('.custom-adv-field-name-input').forEach(function(value){
			if (value.value !== '') {
				return
			}
			var fieldNameFragments = value.id.split(/(?=[A-Z])/);
			fieldNameFragments.forEach(function(value, index){
				// Capitalize first word
				if (index === 0) {
					fieldNameFragments[0] = value.charAt(0).toUpperCase() + value.slice(1);
				}
				// Cleanup 'YN'
				if ( value.match(/^y$/i) || value.match(/^n$/i) || value.match(/^yn$/i) ) {
					fieldNameFragments[index] = ''
				}
				// Handle common abreviations
				// Acres
				if ( value.match(/^ac$/i) || value.match(/^acr$/i) ) {
					fieldNameFragments[index] = "Acres"
				}
				// Approximate
				if ( value.match(/^apx$/i) || value.match(/^apox$/i) || value.match(/^appox$/i) ) {
					fieldNameFragments[index] = "Approximate"
				}
				// Basement
				if ( value.match(/^bsmt$/i) || value.match(/^bsmnt$/i) ) {
					fieldNameFragments[index] = "Basement"
				}
				// Bedroom
				if ( value.match(/^bdrm$/i) ) {
					fieldNameFragments[index] = "Bedroom"
				}
				// Building
				if ( value.match(/^bldg$/i) ) {
					fieldNameFragments[index] = "Building"
				}
				if ( value.match(/^bldgs$/i) ) {
					fieldNameFragments[index] = "Buildings"
				}
				// Days on Market
				if ( value.match(/^dom$/i) ) {
					fieldNameFragments[index] = "Days on Market"
				}
				// Description
				if ( value.match(/^desc$/i) || value.match(/^descrip$/i) ) {
					fieldNameFragments[index] = "Description"
				}
				// Dimensions
				if ( value.match(/^dim$/i) ) {
					fieldNameFragments[index] = "Dimensions"
				}
				// Finished /^([a-zA-Z0-9_-]){3,5}$/
				if ( value.match(/^fin$/i) ) {
					fieldNameFragments[index] = "Finished"
				}
				// Half
				if ( value === "12" ) {
					fieldNameFragments[index] = "Half"
				}
				// HOA
				if ( value.match(/^hoa$/i) ) {
					fieldNameFragments[index] = "HOA"
				}
				// Percent
				if ( value.match(/^pct$/i) || value.match(/^prcnt$/i) ) {
					fieldNameFragments[index] = "Percent"
				}
				// Room
				if ( value.match(/^rm$/i) ) {
					fieldNameFragments[index] = "Room"
				}
				// SqFt
				if ( value.match(/^sqft$/i) ) {
					fieldNameFragments[index] = "SqFt"
				}
				// Total
				if ( value.match(/^ttl$/i) ) {
					fieldNameFragments[index] = "Total"
				}
				// Unfinished
				if ( value.match(/^unfin$/i) ) {
					fieldNameFragments[index] = "Unfinished"
				}
			});
			value.value = fieldNameFragments.join(' ');
		})
	}

	function updateAdvFields (event) {
		event.preventDefault()
		document.getElementById('adv-field-settings-submit-button').value = "Saving..."
		document.getElementById('adv-field-settings-submit-button').disabled = true
		jQuery.post(
			ajaxurl, {
			action: 'update_adv_fields',
			nonce: document.getElementById('adv-fields-nonce').value,
			formdata: jQuery('#wp-listings-adv-fields-form').serialize()
			}, function (response) {
				document.getElementById('adv-field-settings-submit-button').disabled = false
				switch (response) {
					case 'Success':
						document.getElementById('adv-field-settings-submit-button').value = "Saved!"
						break;
					case 'Check user permissions':
						document.getElementById('adv-field-settings-submit-button').value = "Check user permissions"
						break;
					case 'Nonce verification failed':
						document.getElementById('adv-field-settings-submit-button').value = "Nonce validation failed"
						break;
					default:
						document.getElementById('adv-field-settings-submit-button').value = "Save failed"
				}
				setTimeout(function () {
					document.getElementById('adv-field-settings-submit-button').value = "Save Settings"
				}, 1000);
			})
	}
</script>
