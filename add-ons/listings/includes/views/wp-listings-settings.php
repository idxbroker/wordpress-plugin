<?php
if ( !class_exists( 'Idx_Broker_Plugin' ) ) {
	echo wp_listings_admin_notice( __( '<strong>Integrate your MLS Listings into WordPress with IDX Broker!</strong> <a href="http://www.idxbroker.com/features/idx-wordpress-plugin">Find out how</a>', 'wp-listings' ), false, 'activate_plugins', 'wpl_notice_idx' );
}

if( isset($_GET['settings-updated']) ) { ?>
	<div id="message" class="updated">
		<p><strong><?php _e('Settings saved.', 'wp-listings'); ?></strong></p>
	</div>
<?php
}

?>
<div id="icon-options-general" class="icon32"></div>
<div class="wrap">
	<h1><?php _e('IMPress Listings Settings', 'wp-listings'); ?></h1>
	<hr>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
		<?php do_meta_boxes('wp-listings-options', 'side', null); ?>
		</div>

		<div id="post-body">
			<div id="post-body-content" style="margin-right:0px;">
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
			</script>
			<ul>
				<li><a href="#tab-general">General</a></li>
				<?php if(class_exists( 'Idx_Broker_Plugin' )) { echo '<li><a href="#tab-idx">IDX</a></li>'; } ?>
				<li><a href="#tab-advanced">Advanced</a></li>
			</ul>

				<?php

				$options = get_option( 'plugin_wp_listings_settings' );

				if ( isset( $options['wp_listings_import_advanced_fields'] ) ) {
					if ( ! get_option( 'wp_listings_advanced_field_display_options' ) ) {
						add_option( 'wp_listings_advanced_field_display_options', [] );
					}
					update_advanced_field_options();
				} else {
					purge_advanced_field_options();
				}

				$advanced_field_options = get_option( 'wp_listings_advanced_field_display_options' );

				$defaults = array(
					'wp_listings_stylesheet_load'         => 0,
					'wp_listings_widgets_stylesheet_load' => 0,
					'wp_listings_default_state'           => '',
					'wp_listings_currency_symbol'         => '',
					'wp_listings_currency_code'           => '',
					'wp_listings_display_currency_code'   => 0,
					'wp_listings_archive_posts_num'       => 9,
					'wp_listings_global_disclaimer'       => '',
					'wp_listings_slug'                    => 'listings',
					'wp_listings_gmaps_api_key'           => '',
					'wp_listings_captcha_site_key'        => '',
					'wp_listings_captcha_secret_key'      => '',
					'wp_listings_default_form'            => '',
					'wp_listings_custom_wrapper'          => 0,
					'wp_listings_start_wrapper'           => '',
					'wp_listings_end_wrapper'             => '',
					'wp_listings_idx_lead_form'           => 1,
					'wp_listings_idx_update'              => 'update-all',
					'wp_listings_idx_sold'                => 'sold-keep',
					'wp_listings_auto_import'             => 0,
					'wp_listings_default_template'        => '',
					'wp_listings_display_idx_link'        => 0,
					'wp_listings_import_author'           => 0,
					'wp_listings_import_title'            => '{{address}}',
					'wp_listings_import_advanced_fields'  => 0,
					'wp_listings_display_advanced_fields' => 0,
					'wp_listings_uninstall_delete'        => 0
				);

				foreach($defaults as $name => $value) {
					if ( !isset($options[$name]) ) {
						$options[$name] = $value;
					}
				}

				?>

				<form action="options.php" method="post" id="wp-listings-settings-options-form">
					<input name="submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" style="float: right; margin: -32px 10px 0 0;" />
					<?php
					settings_fields('wp_listings_options');
					echo '<div id="tab-general">';

					_e("<h3>Default State</h3><p>You can enter a default state that will automatically be output on template pages and widgets that show the state. When you create a listing and leave the state field empty, the default below will be shown. You can override the default on each listing by entering a value into the state field.</p>", 'wp-listings' );
					echo '<p>Default State: <input name="plugin_wp_listings_settings[wp_listings_default_state]" id="wp_listings_default_state" type="text" value="' . $options['wp_listings_default_state'] . '" size="1" /></p><hr>';

					_e("<h3>Default Currency</h3><p>Select a default currency symbol and optional currency code to display on listings.</p>", 'wp-listings' );
					_e('<p>Currency Symbol: ', 'wp-listings');
					echo '<select name="plugin_wp_listings_settings[wp_listings_currency_symbol]" id="wp_listings_currency_symbol">
							 <option value=" " ' . selected($options['wp_listings_currency_symbol'], ' ', false) . '>None</option>
							 <option value="&#36;" ' . selected($options['wp_listings_currency_symbol'], '$', false) . '>&#36;</option>
							 <option value="&#163;" ' . selected($options['wp_listings_currency_symbol'], '£', false) . '>&#163;</option>
							 <option value="&#8364;" ' . selected($options['wp_listings_currency_symbol'], '€', false) . '>&#8364;</option>
							 <option value="&#165;" ' . selected($options['wp_listings_currency_symbol'], '¥', false) . '>&#165;</option>
							 <option value="&#8369;" ' . selected($options['wp_listings_currency_symbol'], '₱', false) . '>&#8369;</option>
							 <option value="&#8361;" ' . selected($options['wp_listings_currency_symbol'], '₩', false) . '>&#8361;</option>
							 <option value="&#402;" ' . selected($options['wp_listings_currency_symbol'], 'ƒ', false) . '>&#402;</option>
							 <option value="&#8358;" ' . selected($options['wp_listings_currency_symbol'], '₦', false) . '>&#8358;</option>
							</select>
						  </p>';
					$codes = array (
						''    => 'None',
						'USD' => 'United States dollar',
						'GBP' => 'British pound',
						'CAD' => 'Canadian dollar',
						'EUR' => 'Euro',
						'MXN' => 'Mexican peso',
						'---' => '---',
						'AED' => 'United Arab Emirates dirham',
						'AFN' => 'Afghan afghani',
						'ALL' => 'Albanian lek',
						'AMD' => 'Armenian dram',
						'AOA' => 'Angolan kwanza',
						'ARS' => 'Argentine peso',
						'AUD' => 'Australian dollar',
						'AWG' => 'Aruban florin',
						'AZN' => 'Azerbaijani manat',
						'BAM' => 'Bosnia and Herzegovina convertible mark',
						'BBD' => 'Barbadian dollar',
						'BDT' => 'Bangladeshi taka',
						'BGN' => 'Bulgarian lev',
						'BHD' => 'Bahraini dinar',
						'BIF' => 'Burundian franc',
						'BMD' => 'Bermudian dollar',
						'BND' => 'Brunei dollar',
						'BOB' => 'Bolivian boliviano',
						'BRL' => 'Brazilian real',
						'BSD' => 'Bahamian dollar',
						'BTN' => 'Bhutanese ngultrum',
						'BWP' => 'Botswana pula',
						'BYR' => 'Belarusian ruble',
						'BZD' => 'Belize dollar',
						'CDF' => 'Congolese franc',
						'CHF' => 'Swiss franc',
						'CLP' => 'Chilean peso',
						'CNY' => 'Chinese yuan',
						'COP' => 'Colombian peso',
						'CRC' => 'Costa Rican colón',
						'CUP' => 'Cuban convertible peso',
						'CVE' => 'Cape Verdean escudo',
						'CZK' => 'Czech koruna',
						'DJF' => 'Djiboutian franc',
						'DKK' => 'Danish krone',
						'DOP' => 'Dominican peso',
						'DZD' => 'Algerian dinar',
						'EGP' => 'Egyptian pound',
						'ERN' => 'Eritrean nakfa',
						'ETB' => 'Ethiopian birr',
						'FJD' => 'Fijian dollar',
						'FKP' => 'Falkland Islands pound',
						'GEL' => 'Georgian lari',
						'GHS' => 'Ghana cedi',
						'GMD' => 'Gambian dalasi',
						'GNF' => 'Guinean franc',
						'GTQ' => 'Guatemalan quetzal',
						'GYD' => 'Guyanese dollar',
						'HKD' => 'Hong Kong dollar',
						'HNL' => 'Honduran lempira',
						'HRK' => 'Croatian kuna',
						'HTG' => 'Haitian gourde',
						'HUF' => 'Hungarian forint',
						'IDR' => 'Indonesian rupiah',
						'ILS' => 'Israeli new shekel',
						'IMP' => 'Manx pound',
						'INR' => 'Indian rupee',
						'IQD' => 'Iraqi dinar',
						'IRR' => 'Iranian rial',
						'ISK' => 'Icelandic króna',
						'JEP' => 'Jersey pound',
						'JMD' => 'Jamaican dollar',
						'JOD' => 'Jordanian dinar',
						'JPY' => 'Japanese yen',
						'KES' => 'Kenyan shilling',
						'KGS' => 'Kyrgyzstani som',
						'KHR' => 'Cambodian riel',
						'KMF' => 'Comorian franc',
						'KPW' => 'North Korean won',
						'KRW' => 'South Korean won',
						'KWD' => 'Kuwaiti dinar',
						'KYD' => 'Cayman Islands dollar',
						'KZT' => 'Kazakhstani tenge',
						'LAK' => 'Lao kip',
						'LBP' => 'Lebanese pound',
						'LKR' => 'Sri Lankan rupee',
						'LRD' => 'Liberian dollar',
						'LSL' => 'Lesotho loti',
						'LTL' => 'Lithuanian litas',
						'LVL' => 'Latvian lats',
						'LYD' => 'Libyan dinar',
						'MAD' => 'Moroccan dirham',
						'MDL' => 'Moldovan leu',
						'MGA' => 'Malagasy ariary',
						'MKD' => 'Macedonian denar',
						'MMK' => 'Burmese kyat',
						'MNT' => 'Mongolian tögrög',
						'MOP' => 'Macanese pataca',
						'MRO' => 'Mauritanian ouguiya',
						'MUR' => 'Mauritian rupee',
						'MVR' => 'Maldivian rufiyaa',
						'MWK' => 'Malawian kwacha',
						'MYR' => 'Malaysian ringgit',
						'MZN' => 'Mozambican metical',
						'NAD' => 'Namibian dollar',
						'NGN' => 'Nigerian naira',
						'NIO' => 'Nicaraguan córdoba',
						'NOK' => 'Norwegian krone',
						'NPR' => 'Nepalese rupee',
						'NZD' => 'New Zealand dollar',
						'OMR' => 'Omani rial',
						'PAB' => 'Panamanian balboa',
						'PEN' => 'Peruvian nuevo sol',
						'PGK' => 'Papua New Guinean kina',
						'PHP' => 'Philippine peso',
						'PKR' => 'Pakistani rupee',
						'PLN' => 'Polish złoty',
						'PRB' => 'Transnistrian ruble',
						'PYG' => 'Paraguayan guaraní',
						'QAR' => 'Qatari riyal',
						'RON' => 'Romanian leu',
						'RSD' => 'Serbian dinar',
						'RUB' => 'Russian ruble',
						'RWF' => 'Rwandan franc',
						'SAR' => 'Saudi riyal',
						'SBD' => 'Solomon Islands dollar',
						'SCR' => 'Seychellois rupee',
						'SDG' => 'Singapore dollar',
						'SEK' => 'Swedish krona',
						'SGD' => 'Singapore dollar',
						'SHP' => 'Saint Helena pound',
						'SLL' => 'Sierra Leonean leone',
						'SOS' => 'Somali shilling',
						'SRD' => 'Surinamese dollar',
						'SSP' => 'South Sudanese pound',
						'STD' => 'São Tomé and Príncipe dobra',
						'SVC' => 'Salvadoran colón',
						'SYP' => 'Syrian pound',
						'SZL' => 'Swazi lilangeni',
						'THB' => 'Thai baht',
						'TJS' => 'Tajikistani somoni',
						'TMT' => 'Turkmenistan manat',
						'TND' => 'Tunisian dinar',
						'TOP' => 'Tongan paʻanga',
						'TRY' => 'Turkish lira',
						'TTD' => 'Trinidad and Tobago dollar',
						'TWD' => 'New Taiwan dollar',
						'TZS' => 'Tanzanian shilling',
						'UAH' => 'Ukrainian hryvnia',
						'UGX' => 'Ugandan shilling',
						'UYU' => 'Uruguayan peso',
						'UZS' => 'Uzbekistani som',
						'VEF' => 'Venezuelan bolívar',
						'VND' => 'Vietnamese đồng',
						'VUV' => 'Vanuatu vatu',
						'WST' => 'Samoan tālā',
						'XAF' => 'Central African CFA franc',
						'XCD' => 'East Caribbean dollar',
						'XOF' => 'West African CFA franc',
						'XPF' => 'CFP franc',
						'YER' => 'Yemeni rial',
						'ZAR' => 'South African rand',
						'ZMW' => 'Zambian kwacha',
						'ZWL' => 'Zimbabwean dollar'
					);
					_e('<p>Currency Code: ', 'wp-listings');
					echo '<select name="plugin_wp_listings_settings[wp_listings_currency_code]" id="plugin_wp_listings_settings[wp_listings_currency_code]" data-currency="USD">';
						foreach ($codes as $code => $currency_name) {
							echo '<option value=' . $code . ' ' . selected($options['wp_listings_currency_code'], $code, false) . '>' . $currency_name . '</option>';
						}
					echo '</select>
					  </p>';

					_e('<p><input name="plugin_wp_listings_settings[wp_listings_display_currency_code]" id="wp_listings_display_currency_code" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_display_currency_code'], 0 ) . ' /> Display currency code?</p><hr>', 'wp-listings' );

					_e("<h3>Default Number of Posts</h3><p>The default number of posts displayed on a listing archive page is 9. Here you can set a custom number. Enter <span style='color: #f00;font-weight: 700;'>-1</span> to display all listing posts.<br /><em>If you have more than 20-30 posts, it's not recommended to show all or your page will load slow.</em></p>", 'wp-listings' );
					_e('<p>Number of posts on listing archive page: <input name="plugin_wp_listings_settings[wp_listings_archive_posts_num]" id="wp_listings_archive_posts_num" type="text" value="' . $options['wp_listings_archive_posts_num'] . '" size="1" /></p><hr>', 'wp-listings' );

					_e("<h3>Default Disclaimer</h3><p>Optionally enter a disclaimer to show on single listings. This can be overridden on individual listings.</p>", 'wp-listings' );
					_e('<p><textarea name="plugin_wp_listings_settings[wp_listings_global_disclaimer]" id="wp_listings_global_disclaimer" type="text" value="' . esc_html($options['wp_listings_global_disclaimer']) . '" rows="4" style="width: 80%">' . esc_html($options['wp_listings_global_disclaimer']) . '</textarea></p><hr>', 'wp-listings' );

					_e('<h3>Listings slug</h3><p>Optionally change the slug of the listing post type<br /><input type="text" name="plugin_wp_listings_settings[wp_listings_slug]" value="' . $options['wp_listings_slug'] . '" /></p>', 'wp-listings' );
					_e("<em>Don't forget to <a href='../wp-admin/options-permalink.php'>reset your permalinks</a> if you change the slug!</em></p>", 'wp-listings' );
					echo '</div><!-- #tab-general -->';

					echo '<div id="tab-advanced">';

					_e('<h3>Include CSS?</h3>', 'wp-listings');
					if ($options['wp_listings_stylesheet_load'] == 1)
						echo '<p style="color:red; font-weight: bold;">The plugin\'s main stylesheet (wp-listings.css) has been deregistered<p>';
					if ($options['wp_listings_widgets_stylesheet_load'] == 1)
						echo '<p style="color:red; font-weight: bold;">The plugin\'s widget stylesheet (wp-listings-widgets.css) has been deregistered<p>';
					_e('<p>Here you can deregister the WP Listings CSS files and move to your theme\'s css file for ease of customization</p>', 'wp-listings');
					_e('<p><input name="plugin_wp_listings_settings[wp_listings_stylesheet_load]" id="wp_listings_stylesheet_load" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_stylesheet_load'], false ) . ' /> Deregister WP Listings main CSS (wp-listings.css)?</p>', 'wp-listings' );

					_e('<p><input name="plugin_wp_listings_settings[wp_listings_widgets_stylesheet_load]" id="wp_listings_widgets_stylesheet_load" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_widgets_stylesheet_load'], false ) . ' /> Deregister WP Listings widgets CSS (wp-listings-widgets.css)?</p><hr>', 'wp-listings' );

					_e('<h3>Forms</h3><h4>Google reCAPTCHA v2 (anti-spam)</h4><p>With the default contact form, you can choose to add Google reCAPTCHA v2 to prevent spam, or use a form shortcode plugin with anti-spam protection. To use Google reCAPTCHA v2, you must first <a href="https://www.google.com/recaptcha/admin" target="_blank">sign up for a v2 key</a>, then enter the site and secret key below:</p>', 'wp-listings' );
					_e('<p>Site key: <input name="plugin_wp_listings_settings[wp_listings_captcha_site_key]" id="wp_listings_captcha_site_key" type="text" value="' . esc_html($options['wp_listings_captcha_site_key']) . '" size="40" /></p>', 'wp-listings');
					_e('<p>Secret key: <input name="plugin_wp_listings_settings[wp_listings_captcha_secret_key]" id="wp_listings_captcha_secret_key" type="text" value="' . esc_html($options['wp_listings_captcha_secret_key']) . '" size="40" /></p>', 'wp-listings');
					_e("<h4>Default Form shortcode</h4><p>If you use a Contact Form plugin, you may enter the form shortcode here to display on all listings. Additionally, each listing can use a custom form. If no shortcode is entered, the template will use a default contact form:</p>", 'wp-listings' );
					_e('<p>Form shortcode: <input name="plugin_wp_listings_settings[wp_listings_default_form]" id="wp_listings_default_form" type="text" value="' . esc_html($options['wp_listings_default_form']) . '" size="40" /></p>', 'wp-listings');

					if(class_exists( 'Idx_Broker_Plugin' )) {
						_e("<h4>Add default form entries to IDX Broker?</h4><p>Check this option to enable form entries to be sent to IDX Broker as a lead.<br/><strong>Note: This only works if using the default contact form.</strong></p>", 'wp-listings' );
						_e('<p><input name="plugin_wp_listings_settings[wp_listings_idx_lead_form]" id="wp_listings_idx_lead_form" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_idx_lead_form'], 0) . ' /> Enable?</p><hr>', 'wp-listings' );
					}

					_e('<h3>Maps</h3><h4>Google Maps</h4><p>Listings can be automatically mapped if they have a latitude and longitude. You will need a <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps API key</a> to use this feature. Enter your API key below.</p>', 'wp-listings' );
					_e('<p>Browser key: <input name="plugin_wp_listings_settings[wp_listings_gmaps_api_key]" id="wp_listings_gmaps_api_key" type="text" value="' . esc_html($options['wp_listings_gmaps_api_key']) . '" size="40" /></p><hr>', 'wp-listings');

					_e("<h3>Custom Wrapper</h3><p>If your theme's content HTML ID's and Classes are different than the included template, you can enter the HTML of your content wrapper beginning and end:</p>", 'wp-listings' );
					_e('<p><label><input name="plugin_wp_listings_settings[wp_listings_custom_wrapper]" id="wp_listings_custom_wrapper" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_custom_wrapper'], false ) . ' /> Use Custom Wrapper?</p>', 'wp-listings' );
					_e('<p><label>Wrapper Start HTML: </p><input name="plugin_wp_listings_settings[wp_listings_start_wrapper]" id="wp_listings_start_wrapper" type="text" value="' . esc_html($options['wp_listings_start_wrapper']) . '" size="80" /></label>', 'wp-listings' );
					_e('<p><label>Wrapper End HTML: </p><input name="plugin_wp_listings_settings[wp_listings_end_wrapper]" id="wp_listings_end_wrapper" type="text" value="' . esc_html($options['wp_listings_end_wrapper']) . '" size="80" /></label><hr>', 'wp-listings' );

					_e( '<h3>Install Information Data Collection</h3>', 'wp-listings' );
					_e( '<p>IDX Broker collects general install information to help improve our WordPress plugins. </p>', 'wp-listings' );
					_e( "<input onclick='impressListingsDataCollectionOptOut()' id='impress-data-optout-checkbox' type='checkbox' value='1' class='wpl-gmp-settings-checkbox'  " . ( get_option( 'impress_data_optout' ) ? 'checked' : '' ) . "/><span>Opt-out</span><hr>", 'wp-listings' );

					_e('<h3>Delete data on uninstall?</h3>', 'wp-listings');
					_e('<p>Checking this option will delete <strong>all</strong> plugin data when uninstalling the plugin.</p>', 'wp-listings');
					_e('<p><input name="plugin_wp_listings_settings[wp_listings_uninstall_delete]" id="wp_listings_uninstall_delete" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_uninstall_delete'], false ) . ' /> <strong style="color: red;">Delete plugin data on uninstall</strong></p>', 'wp-listings' );

					echo '</div><!-- #tab-advanced -->';

					if(class_exists( 'Idx_Broker_Plugin' )) {
						echo '<div id="tab-idx">';
						_e( '<h3>IDX Imported Listings</h3><p>These settings apply to any imported IDX listings. Imported listings are updated via the latest API response twice daily.</p>', 'wp-listings' );
						_e("<h2>Update Listings</h2>", 'wp-listings' );
						_e('<div class="idx-import-option update-all"><label><h4>Update All</h4> <span class="dashicons dashicons-update"></span><input name="plugin_wp_listings_settings[wp_listings_idx_update]" id="wp_listings_idx_update" type="radio" value="update-all" class="code" ' . checked('update-all', $options['wp_listings_idx_update'], false ) . ' /> <p>Update all imported fields including gallery and featured image. <br /><em>* Excludes Post Title and Post Content</em></p></label></div>', 'wp-listings' );
						_e('<div class="idx-import-option update-noimage"><label><h4>Update Excluding Images</h4> <span class="dashicons dashicons-update"></span><input name="plugin_wp_listings_settings[wp_listings_idx_update]" id="wp_listings_idx_update" type="radio" value="update-noimage" class="code" ' . checked('update-noimage', $options['wp_listings_idx_update'], false ) . ' /> <p>Update all imported fields, but excluding the gallery and featured image.<br /><em>* Also excludes Post Title and Post Content</em></p></label></div>', 'wp-listings' );
						_e('<div class="idx-import-option update-none"><label><h4>Do Not Update</h4> <span class="dashicons dashicons-dismiss"></span><input name="plugin_wp_listings_settings[wp_listings_idx_update]" id="wp_listings_idx_update" type="radio" value="update-none" class="code" ' . checked('update-none', $options['wp_listings_idx_update'], false ) . ' /> <p><strong>Not recommended as displaying inaccurate MLS data may violate your IDX agreement.</strong><br /> Does not update any fields.<br /><em>* Listing will be changed to sold status if it exists in the sold data feed.</em></p></label></div>', 'wp-listings' );

						_e("<br style=\"clear: both;\"><h2>Sold Listings</h2>", 'wp-listings' );
						_e('<div class="idx-import-option sold-keep"><label><h4>Keep All</h4> <span class="dashicons dashicons-admin-post"></span><input name="plugin_wp_listings_settings[wp_listings_idx_sold]" id="wp_listings_idx_sold" type="radio" value="sold-keep" class="code" ' . checked('sold-keep', $options['wp_listings_idx_sold'], false ) . ' /> <p>This will keep all imported listings published with the status changed to reflect as sold.</p></label></div>', 'wp-listings' );
						_e('<div class="idx-import-option sold-draft"><label><h4>Keep as Draft</h4> <span class="dashicons dashicons-hidden"></span><input name="plugin_wp_listings_settings[wp_listings_idx_sold]" id="wp_listings_idx_sold" type="radio" value="sold-draft" class="code" ' . checked('sold-draft', $options['wp_listings_idx_sold'], false ) . ' /> <p>This will keep all imported listings that have been sold, but they will be changed to draft status in WordPress.</p></label></div>', 'wp-listings' );
						_e('<div class="idx-import-option sold-delete"><label><h4>Delete Sold</h4> <span class="dashicons dashicons-trash"></span><input name="plugin_wp_listings_settings[wp_listings_idx_sold]" id="wp_listings_idx_sold" type="radio" value="sold-delete" class="code" ' . checked('sold-delete', $options['wp_listings_idx_sold'], false ) . ' /> <p><strong>Not recommended</strong> <br />This will delete all sold listings and attached featured images from your WordPress database and media library.</p></label></div>', 'wp-listings' );

						_e("<hr style=\"margin: 25px 0; clear: both;\"><h2>Additional Import Options</h2>", 'wp-listings' );
						_e('<p><input name="plugin_wp_listings_settings[wp_listings_auto_import]" id="wp_listings_auto_import" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_auto_import'], false ) . ' /> Automatically import new listings?</p>', 'wp-listings' );

						_e('<p>Optionally select a default single listing template to use for imported listings.<br />
							<select name="plugin_wp_listings_settings[wp_listings_default_template]" id="listing_template" class="dropdown">');
						_e('<option value="">Default</option>', 'wp-listings');
						
						$single_listing_template = new Single_Listing_Template();
						$listing_templates = $single_listing_template->get_listing_templates();
						/** Loop through templates, make them options */
						foreach ( (array) $listing_templates as $template_file => $template_name ) {
							$selected = ( $template_file == $options['wp_listings_default_template'] ) ? ' selected="selected"' : '';
							$opt = '<option value="' . esc_attr( $template_file ) . '"' . $selected . '>' . esc_html( $template_name ) . '</option>';
							echo $opt;
						}
						
						_e('</select>');

						_e('<p>Select an author to use when importing listings <br />' . wp_dropdown_users(array('selected' => $options['wp_listings_import_author'], 'name' => 'plugin_wp_listings_settings[wp_listings_import_author]', 'id' => 'wp_listings_import_author', 'echo' => false, 'who' => 'authors' )) . '</p>', 'wp-listings' );
						_e('<p><input name="plugin_wp_listings_settings[wp_listings_display_idx_link]" id="wp_listings_display_idx_link" type="checkbox" value="1" class="code" ' . checked(1, $options['wp_listings_display_idx_link'], false ) . ' /> Display a link to IDX Broker details page?</p>', 'wp-listings' );

						_e('<p><label><h2>Import Title</h2>
							By default, imported listings use the street address as the title and permalink. You can customize that further using these available tags:<br />
							<strong><code>{{listingid}}</code> <code>{{address}}</code> <code>{{city}}</code> <code>{{state}}</code> <code>{{zipcode}}</code></strong>
							</p><input name="plugin_wp_listings_settings[wp_listings_import_title]" id="wp_listings_import_title" type="text" value="' . esc_html( $options['wp_listings_import_title'] ) . '" size="80" /></label><hr style="clear: both;">', 'wp-listings' );

						_e("<h2>Advanced Field Settings</h2>", 'wp-listings' );

						echo '<p>';
						echo '<input 
							name="plugin_wp_listings_settings[wp_listings_import_advanced_fields]"
							id="wp_listings_import_advanced_fields" 
							type="checkbox" 
							value="1" 
							onclick="toggleAdvFieldImportSetting()" 
							class="code" 
							' . checked( 1, $options['wp_listings_import_advanced_fields'], false ) . '
						/>';
						_e('Import advanced field data?', 'wp-listings');
						echo '</p>';

						echo '<p id="wp_listings_display_advanced_fields_container" class="' . ($options['wp_listings_import_advanced_fields'] === 0 ? 'disabled-adv-field-option' : '' ) . '">';
						echo '<input
							name="plugin_wp_listings_settings[wp_listings_display_advanced_fields]" 
							id="wp_listings_display_advanced_fields" type="checkbox" 
							value="1" 
							class="code"
							' . checked(1, $options['wp_listings_display_advanced_fields'], false ) . ' 
						/>';
						_e('Display advanced fields on single listing pages?', 'wp-listings');
						echo '</p>';

						// If no advanced fields are imported but the option to import is enabled, show message on what to do next.
						if ( count( $advanced_field_options ) < 1 && isset( $options['wp_listings_import_advanced_fields'] ) && $options['wp_listings_import_advanced_fields'] === "1" ) {
							echo '<div style="font-size:10px;line-height:21px;">
											<span class="dashicons dashicons-warning"></span>
											<span>
												Once new listings are imported or existing listings update after enabling the "Import advanced field data" option, advanced field display setting options will appear here.
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
										#adv-field-cusomization-container .button-primary:active {opacity: 0.7;}
								</style>
						';
                           
						// If any advanced fields are imported, display the adv field customization table.
						if ( count( $advanced_field_options ) > 0 ) {
							_e( '<div id="adv-field-cusomization-container" class="' . ($options['wp_listings_import_advanced_fields'] === 0 ? 'disabled-adv-field-option' : '' ) . '">', 'wp-listings' );
					
							_e(
								'<h2 id="wpl-customize-adv-fields-label">Customize Advanced Fields</h2>
									<div id="wpl-adv-field-button-container">
									<button id="wpl-hide-all-adv-field-button" class="button-primary" type="button" onclick="hideAllAdvFields()" title="Set all advanced fields to hide on listing pages">Hide All Fields</button>
									<button id="wpl-show-all-adv-field-button" class="button-primary" type="button" onclick="showAllAdvFields()" title="Set all advanced fields to display on listing pages" >Show All Fields</button>
									<button id="wpl-populate-custom-names-button" class="button-primary" type="button" onclick="populateEmptyAdvCustomNameFields()" title="Generate a best guess name for all fields currently missing a custom name" >Generate Custom Names</button>
									<button id="wpl-clear-custom-names-button" class="button-primary" type="button" onclick="clearAdvCustomNameFields()" title="Remove custom names from all fields" >Clear Custom Names</button>
								</div>
							',
								'wp-listings'
							);

							_e( "<table id='idx-wp-listings-adv-fields-table'>", 'wp-listings' );
							_e( "<tr><th>Field Name</th><th>Custom Name</th><th>Display</th></tr>", 'wp-listings' );
							foreach ( $advanced_field_options as $key => $value ) {
								_e(
									"<tr>
										<td class='wpl-adv-name-field'>$key</td>
										<td class='wpl-adv-custom-name-field'>
											<input name='wp_listings_advanced_field_display_options[$key][custom_name]' id='$key' class='custom-adv-field-name-input' type='text' value='" . esc_attr( $value['custom_name'] ) . "' />
										</td>
										<td class='wpl-adv-show-hide'>
											<div class=''>
												Show <input name='wp_listings_advanced_field_display_options[$key][display_field]' id='$key-show-checkbox' class='show-radio-button' type='radio' value='show' " . checked( 'show', $value['display_field'], false ) . " />
												Hide <input name='wp_listings_advanced_field_display_options[$key][display_field]' id='$key-hide-checkbox' class='hide-radio-button' type='radio' value='hide' " . checked( 'hide', $value['display_field'], false ) . " />
											</div>
										</td>
									</tr>",
									'wp-listings'
								);
							}

							_e( '</table>', 'wp-listings' );
							_e(
								'<div id="wpl-adv-field-button-container">
									<button id="wpl-hide-all-adv-field-button" class="button-primary" type="button" onclick="hideAllAdvFields()" title="Set all advanced fields to hide on single listing pages.">Hide All Fields</button>
									<button id="wpl-show-all-adv-field-button" class="button-primary" type="button" onclick="showAllAdvFields()" title="Set all advanced fields to show on single listing pages" >Show All Fields</button>
									<button id="wpl-populate-custom-names-button" class="button-primary" type="button" onclick="populateEmptyAdvCustomNameFields()" title="Generates a best guess title for any field missing a custom name." >Generate Custom Names</button>
									<button id="wpl-clear-custom-names-button" class="button-primary" type="button" onclick="clearAdvCustomNameFields()" title="Remove custom names from all fields. Press the Save Settings button to commit any changes." >Clear Custom Names</button>
								</div>
							',
								'wp-listings'
							);
							echo '</div>';
						}
						echo '<hr>';

						// GMB Settings Section.
						$idx_api     = new \IDX\Idx_Api();
						$gmb_options = get_option( 'wp_listings_google_my_business_options' );
						if ( $idx_api->platinum_account_type() && ! empty( $gmb_options['refresh_token'] ) ) {
							$google_my_business_manager = WPL_Google_My_Business::get_instance();
							// $google_my_business_options = $google_my_business_manager->wpl_get_gmb_settings_options();.
							// Location list control.
							_e( '<h3>Google My Business</h3>', 'wp-listings' );
							if ( ! empty( $gmb_options['posting_logs']['last_post_status_message'] ) ) {
								_e( '<div id="wpl-gmb-last-status-container"><strong>Last Post Status:&nbsp;</strong>' . $gmb_options['posting_logs']['last_post_status_message'] . '<button onclick="clearLastPostStatus(event);"><span class="dashicons dashicons-no-alt"></span></button></div>', 'wp-listings' );
							}
							_e( '<div class="gmb-reset-next-post-container">', 'wp-listings' );
							_e( '<strong>Next Post Date:&nbsp;</strong><span id="wpl-gmb-next-post-label"> ' . $google_my_business_manager->wpl_gmb_get_next_post_time() . '</span>', 'wp-listings' );
							_e( '</div>', 'wp-listings' );
							_e( '<button id="wpl-reset-next-post-time-button" title="Resets next scheduled post to 12 hours from now." class="button">Reset Next Scheduled Post Time</button>', 'wp-listings' );
							_e( '<h4 class="gmb-location-header">Locations:</h4>', 'wp-listings');
							echo '<div id="gmb-location-picker-container">';
							$gmb_locations = $google_my_business_manager->get_saved_gmb_locations();
							foreach ( $gmb_locations as $key => $value ) {
								echo '<div class="wpl-gmb-location-tag">';
								_e( "<input onclick='locationToggled()' id='$key' type='checkbox' value='1' class='wpl-gmp-settings-checkbox'  " . ( 1 == $value['share_to_location'] ? "checked" : "" ) . "/>", 'wp-listings' );
								_e( "<label for='$key' class='checkbox-label-slider'></label>", 'wp-listings' );
								_e( '<strong> ' . $value['location_name'] . ':</strong> ' . $value['street_address'], 'wp-listings' );
								echo '</div>';
							}
							echo '</div>';
							_e( '<div id="wpl-gmb-clear-btn-container" ><a id="wpl-gmb-clear-settings-button" href="#">Disconnect from Google My Business</a></div>', 'wp-listings' );
							echo '<hr>';
						}
						echo '</div><!-- #idx-tab -->';
					}

					?>
					<input name="submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" style="margin: 0 0 10px 10px;"/>
				</form>
			</div>
		</div>
	</div>
</div>

<?php

/**
 * Purges any saved advanced fields/Customizations currently saved.
 */
function purge_advanced_field_options() {
	update_option( 'wp_listings_advanced_field_display_options', [] );
}
/**
 * Gathers all advanced fields present to allow for cuztomization in settings.
 */
function update_advanced_field_options() {

	if ( ! get_option( 'wp_listings_advanced_field_display_options' ) ) {
		add_option( 'wp_listings_advanced_field_display_options', [] );
	}

	$adv_field_options = get_option( 'wp_listings_advanced_field_display_options' );
	if ( ! is_array( $adv_field_options ) ) {
		$adv_field_options = [];
	}

	$adv_fields = [];
	$listing_posts = get_posts(
		[
			'numberposts' => '-1',
			'post_type'   => 'listing',
		]
	);

	if ( ! is_array( $listing_posts ) ) {
		return;
	}

	foreach ( $listing_posts as $key => $value ) {
		$listing_post_meta = get_post_meta( $value->ID );
		// Get advanced fields from all listings and remove any duplicates.
		if ( ! empty( $listing_post_meta['_advanced_fields'][0] ) ) {
			$adv_fields = array_unique( array_merge( $adv_fields, array_keys( maybe_unserialize( $listing_post_meta['_advanced_fields'][0] ) ) ) );
		}
	}
	if ( ! empty( $adv_fields ) ) {
		sort( $adv_fields );
		foreach ( $adv_fields as $value ) {
			if ( ! array_key_exists( $value, $adv_field_options ) ) {
				$adv_field_options[ $value ] = [
					'custom_name'  => '',
					'display_field' => 'show',
				];
			}
		}
	}

	update_option( 'wp_listings_advanced_field_display_options', $adv_field_options );
}

?>
