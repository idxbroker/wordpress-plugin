<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.','impress_agents'); ?></strong></p>
    </div>
<?php
}

?>
<div id="icon-options-general" class="icon32"></div>
<div class="wrap">
	<h1><?php _e('IMPress Agents Settings', 'impress_agents'); ?></h1>
	<hr>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
		<?php //do_meta_boxes('impress-agents-options', 'side', null); ?>
		</div>

        <div id="post-body">
            <div id="post-body-content" class="has-sidebar-content">

            	<?php $options = get_option('plugin_impress_agents_settings');

            	$defaults = array(
            		'impress_agents_stylesheet_load'         => 0,
            		'impress_agents_archive_posts_num'       => 9,
            		'impress_agents_slug'                    => 'employees',
            		'impress_agents_custom_wrapper'          => 0,
            		'impress_agents_start_wrapper'           => '',
            		'impress_agents_end_wrapper'             => ''
            		);

            	foreach($defaults as $name => $value) {
            		if ( !isset($options[$name]) ) {
						$options[$name] = $value;
					}
            	}

				if ($options['impress_agents_stylesheet_load'] == 1)
					echo '<p style="color:red; font-weight: bold;">The plugin\'s main stylesheet (impress-agents.css) has been deregistered<p>';
				?>
				<form action="options.php" method="post" id="impress-agents-settings-options-form">
					<?php
					settings_fields('impress_agents_options');


					_e('<h3>Include CSS?</h3>', 'impress_agents');
					_e('<p>Here you can deregister the IMPress Agents CSS files and move to your theme\'s css file for ease of customization</p>', 'impress_agents');
					_e('<p><input name="plugin_impress_agents_settings[impress_agents_stylesheet_load]" id="impress_agents_stylesheet_load" type="checkbox" value="1" class="code" ' . checked(1, $options['impress_agents_stylesheet_load'], false ) . ' /> Deregister IMPress Agents main CSS (impress-agents.css)?</p>', 'impress-agents' );

					_e("<h3>Default Number of Posts</h3><p>The default number of posts displayed on a employee archive page is 9. Here you can set a custom number. Enter <span style='color: #f00;font-weight: 700;'>-1</span> to display all employee posts.<br /><em>If you have more than 20-30 posts, it's not recommended to show all or your page will load slow.</em></p>", 'impress_agents' );
				    _e('<p>Number of posts on employee archive page: <input name="plugin_impress_agents_settings[impress_agents_archive_posts_num]" id="impress_agents_archive_posts_num" type="text" value="' . $options['impress_agents_archive_posts_num'] . '" size="1" /></p><hr>', 'impress-agents' );


					_e("<h3>Custom Wrapper</h3><p>If your theme's content HTML ID's and Classes are different than the included template, you can enter the HTML of your content wrapper beginning and end:</p>", 'impress_agents' );
					_e('<p><label><input name="plugin_impress_agents_settings[impress_agents_custom_wrapper]" id="impress_agents_custom_wrapper" type="checkbox" value="1" class="code" ' . checked(1, $options['impress_agents_custom_wrapper'], false ) . ' /> Use Custom Wrapper?</p>', 'impress-agents' );
				    _e('<p><label>Wrapper Start HTML: </p><input name="plugin_impress_agents_settings[impress_agents_start_wrapper]" id="impress_agents_start_wrapper" type="text" value="' . esc_html($options['impress_agents_start_wrapper']) . '" size="80" /></label>', 'impress-agents' );
				    _e('<p><label>Wrapper End HTML: </p><input name="plugin_impress_agents_settings[impress_agents_end_wrapper]" id="impress_agents_end_wrapper" type="text" value="' . esc_html($options['impress_agents_end_wrapper']) . '" size="80" /></label><hr>', 'impress-agents' );

					_e('<h3>Install Information Data Collection</h3><p>IDX Broker collects general install information to help improve our WordPress plugins.</p>', 'impress-agents' );
					_e( '<p><label><input id="impress_agents_data_optout" onchange="impressAgentsDataCollectionOptOut(this);" type="checkbox" value="1" class="code" ' . checked( 1, get_option( 'impress_data_optout' ), false ) . ' /> Opt-out</p><hr>', 'impress-agents' );

					_e('<h3>Directory slug</h3><p>Optionally change the slug of the employee post type<br /><input type="text" name="plugin_impress_agents_settings[impress_agents_slug]" value="' . $options['impress_agents_slug'] . '" /></p>', 'impress-agents' );
					_e("<em>Don't forget to <a href='../wp-admin/options-permalink.php'>reset your permalinks</a> if you change the slug!</em></p>", 'impress-agents' );

					?>
					<input name="submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" />
				</form>
            </div>
        </div>
    </div>
</div>
