<?php
	global $api_error;
	$search_item = array('_','-');
	$display_class = '';
	$savedlinks = '';
	$systemlinks = '';
	$check_sys_option = '';
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	if(!$api_error) {
		$systemlinks = get_transient('idx_systemlinks_cache');
		if(!$systemlinks)
			$systemlinks = idx_platinum_get_systemlinks();
		if( is_wp_error($systemlinks) ) {
			$api_error = $systemlinks->get_error_message();
			$systemlinks = '';
		}

		$savedlinks = get_transient('idx_savedlink_cache');
		if (!$savedlinks)
			idx_platinum_get_savedlinks();

		if( is_wp_error($savedlinks) ) {
			$api_error = $savedlinks->get_error_message();
			$savedlinks = '';
		}
	}
	/**
	 * check wrapper page exist or not
	 */
	$wrapper_page_id = get_option('idx_broker_dynamic_wrapper_page_id');
	$post_title = '';
	$wrapper_page_url = '';
	if ($wrapper_page_id) {
		if (!get_page_uri($wrapper_page_id)) {
			update_option('idx_broker_dynamic_wrapper_page_id', '');
			$wrapper_page_id = '';
		} else {
			$post_title = get_post($wrapper_page_id)->post_title;
			$wrapper_page_url = get_page_link($wrapper_page_id);
		}
	}
?>

<div id="idxPluginWrap" class="wrap">
	<a href="http://www.idxbroker.com" target="_blank">
		<div id="logo"></div>
	</a>
	<div style="display: table; width: 87%;">
		<h2 class="flft">IDX Broker Platinum&reg; Plugin Settings</h2>
		<br clear="all"/>
		<span class="label">Useful Links:</span>
		<ul class="usefulLinks">
			<li><a href="http://kb.idxbroker.com/Knowledgebase/List/Index/16/wordpress-integration" target="_blank">IDX Broker Platinum Knowledgebase</a></li>
			<li><a href="http://middleware.idxbroker.com/mgmt/login.php" target="_blank">Login to Your Control Panel</a></li>
			<li><a href="mailto:help@idxbroker.com?Subject=Help me with WordPress" target="_blank">Contact IDX Broker</a></li>
		</ul>
		<br clear="all"/>
	</div>
	<form method="post" action="options.php" id="idx_broker_options">
		<?php wp_nonce_field('update-options'); ?>
		<div id="blogUrl" style="display: none;" ajax="<?php bloginfo('wpurl'); ?>"></div>
		<div id="tabs_container">
		<input type="hidden" id="currentTab" name="idx_broker_admin_page_tab" value="<?php echo get_option('idx_broker_admin_page_tab');?>">
			<ul id="tabs">
				<li class="active"><a href="#integration">Integration</a></li>
				<li><a href="#setting">Setting</a></li>
			</ul>
		</div>
		<div id="tabs_content_container">
			<div id="integration" class="tab_content" style="display: block;">
				<div class="widgSettings">
					<h3>
						<label>Add IDX Widgets</label>
					</h3>
					<p>Widgets give you a way to add Quick Search, Featured Listings, Agents, and Custom Links to your WordPress pages. IDX Broker Platinum comes with a default set of Widgets. If you have created additional, custom Widgets, simply click the "Refresh Plugin Options" button in Setting tab and visit your <a href="widgets.php">Widgets Tab</a> in WordPress to drag-and-drop IDX Widgets into your sidebar.</p>
				</div>
				<div class="widgSettings">
					<h3>
						<label>Add IDX System Navigation Links</label>
					</h3>
				  	<p>Basic Search, Map Search, Advanced Search, Featured Listings, Roster Page links and any other search form thay you've created in IDX Broker Platinum can be easily added to your website navigation. All of your search links are hosted on a subdomain or <a href="http://kb.idxbroker.com/index.php?/Knowledgebase/Article/View/7/0/using-a-custom-subdomain">custom subdomain</a> that maintains the look and feel of your website. To add these to your website navigation, simply add them to a <a href="nav-menus.php">Custom Menu</a>, or reorder the display of these pages using your <a href="edit.php?post_type=page">Pages Tab</a> in WordPress. Note that each page is the equivalent of a link, and that you do not need to enter any information into the pages themselves to get them to display correctly. IDX Broker Platinum will do that for you.</p>
				</div>
				<div>
					<p>You do not have any system links because you may have entered an incorrect API key. Please review API key in the Setting tab.</p>
					<p>Check the box next to the page link you wish to add to your navigation. To remove an IDX page, simply uncheck the box next to the page you wish to remove and click the "Update System Links" button.</p>
				</div>
				<ul class="linkList">
					<?php
						if (empty($systemlinks))
						{
							$display_class = 'dispNone';
						}
						else
						{
							$check_sys_option = (get_option('idx_systemlink_group') == 1)?'checked="checked"':'';
							$my_system_links = get_my_system_links();
							foreach($systemlinks as $system_link)
							{
								if($system_link->systemresults != '1')
								{
									$std_check_options = (in_array($system_link->uid,$my_system_links))? 'checked = "checked"': '';
					?>
							<li>
								<input type="checkbox" value="<?php echo $system_link->url;?>" name="idx_platinum_system_<?php echo $system_link->uid;?>" id="idx_platinum_system_<?php echo $system_link->uid;?>" <?php echo $std_check_options; ?> class="systemLink idx_platinum_sl" />
								<label for="idx_platinum_system_<?php echo $system_link->uid;?>" class="linkLabel">- <?php echo str_replace($search_item, ' ', $system_link->name); ?></label>
							</li>
					<?php
								}
							}
						}
					?>
            	</ul>
	            <?php
	            	if(count($systemlinks) > 0)
	            	{
				?>
		            <span>
						<input type="checkbox" value="idx_systemlink_group" name="idx_systemlink_group" id="idx_systemlink_group" <?php echo $check_sys_option;?> />
						<label for="idx_systemlink_group" class="link-label">- Add/Remove All Pages</label>
					</span>
				<?php
					}
				?>
	            <div class="linkHeader <?php echo $display_class; ?>">
					<span class="system_status"></span>
				</div>

				<?php
					if (empty($savedlinks))
					{
						$display_class = 'dispNone';
					} else {
						$display_class = '';
					}
					$check_saved_option = (get_option('idx_savedlink_group') == 1)?' checked="checked"':'';
				?>
				<?php
				/**
				 * We want the client the ability to place any custom built links in the system
				 *	in the main navigation.  First lets grab them.
				 *
				 * Ther are no custom links in the system, so just display some text and a link to the admin to
				 *	add custom links.
				 *
				 */
					if (empty($savedlinks))
					{
				?>
				<div class="widgSettings">
					<h3>
						<label>Add Your Custom Links (Neighborhood, Custom Map Search, etc.)</label>
		          	</h3>
			  		<p>
			  			You may create and save an unlimited number of Saved Links (e.g., neighborhood results, short sale results, etc).
			  			<br><br>
						To create your saved links, <a href="http://middleware.idxbroker.com/mgmt/login.php" target="_blank">login to IDX Broker Platinum</a> and go to <a href="http://middleware.idxbroker.com/mgmt/savedlinks.php" target="_blank">Saved Links.</a> Once you have built and saved your saved links, revisit this page and hit the refresh button. Your new links will automatically appear below. Simply choose the custom links that you wish to display in your theme header navigation and IDX Broker Platinum will add those pages and link to the corresponding IDX results.
					</p>
				</div>
				<?php
					}
					else
					{
				?>
				<div id="widgSettings">
					<h3>
						<label>Add Your Custom Links (Neighborhood, Custom Map Search, etc.)</label>
		          	</h3>
	 				<p>Add custom neighborhood, subdivision, and other special links to your website. To create or edit saved links, login to IDX Broker and view the <a href="http://middleware.idxbroker.com/mgmt/savedlinks.php" target="_blank">Saved Links</a> page. Once you've created links, open your IDX Broker Platinum Plugin settings page and click the Refresh Plugin Options to add your saved links to this list. Your new links will appear below. Click to add or remove any page links that you don't want to add.</p>
		 		</div>
				<ul class="savedLinklist">
				<?php
						$my_saved_links = get_my_saved_links();
						foreach ($savedlinks as $saved_link)
						{
							$checkOption = (in_array($saved_link->uid,$my_saved_links))? 'checked = "checked"': '';
				?>
							<li>
								<input type="checkbox" value="<?php echo $saved_link->url;?>" name="idx_platinum_saved_<?php echo $saved_link->uid;?>" id="idx_platinum_saved_<?php echo $saved_link->uid;?>" <?php echo $checkOption; ?> class="savedLink idx_platinum_sdl"/>
								<label for="idx_platinum_saved_<?php echo $saved_link->uid;?>" style="padding-left: 2px;" class="linkLabel">- <?php echo str_replace($search_item, ' ', $saved_link->linkName); ?></label>
							</li>
				<?php
						}
					}
				?>
				</ul>

			    <?php if(count($savedlinks) > 0):?>
			    <span>
					<input type="checkbox" value="idx_savedlink_group" name="idx_savedlink_group" id="idx_savedlink_group" <?php echo $check_saved_option;?> />
					<label for="idx_savedlink_group" class="linkLabel">- Add/Remove All Pages</label>
				</span>
				<?php endif;?>
				<div class="linkHeader <?php echo $display_class; ?>" style="border-bottom: none;">
					<span class="saved_status" style="border-bottom: none;"></span>
				</div>
			</div>
			<div id="setting" class="tab_content">
				<div id="genSettings">
					<h3 class="hndle">
						<label>Get an API Key</label>
						<a href="http://kb.idxbroker.com/index.php?/Knowledgebase/Article/View/98/16/idx-broker-platinum-wordpress-plugin" class="helpIcon" target="_blank"></a>
					</h3>
					<div class="inlineBlock">
						<div>
							<label for="idx_broker_apikey">Enter Your API Key: </label>
							<input name="idx_broker_apikey" type="text" id="idx_broker_apikey" value="<?php echo get_option('idx_broker_apikey'); ?>" />
							<input type="button" name="api_update" id="api_update" value="Refresh Plugin Options" class="button-primary" style="width:auto;" />
							<span class="refresh_status"></span>
						</div>
						<p class="error hidden" id="idx_broker_apikey_error">
							Please enter your API key to continue.
							<br>
							If you do not have an IDX Broker Platinum account, please contact the IDX Broker team at 800-421-9668.
						</p>
						<?php
							if($api_error) {
								echo '<p class="error" style="display:block;">'.$api_error.'</p>';
							}
						?>
					</div>
				</div>
				<!-- dynamic wrapper page -->
				<div id="dynamic_page">
					<h3>Create a Dynamic Wrapper Page</h3>
					<label for="idx_broker_dynamic_wrapper_page">Page Name:</label>
					<input name="idx_broker_dynamic_wrapper_page_name" type="text" id="idx_broker_dynamic_wrapper_page_name" value="<?php echo $post_title; ?>" />
					<input name="idx_broker_dynamic_wrapper_page_id" type="hidden" id="idx_broker_dynamic_wrapper_page_id" value="<?php echo get_option('idx_broker_dynamic_wrapper_page_id'); ?>" />
					<input type="button" class="button-primary" id="idx_broker_creaet_wrapper_page" value="<?php echo $post_title ? 'Update' : 'Create' ?>" />
					<?php
						if ($wrapper_page_id != '')
						{
					?>
						<input type="button" class="button-primary" id="idx_broker_delete_wrapper_page" value="Delete" />
					<?php
						}
					?>
					<a href="http://kb.idxbroker.com/Knowledgebase/Article/View/189/0/automatically-create-dynamic-wrapper-page-in-wordpress" target="_blank"><img src="<?php echo plugins_url('../images/helpIcon.png', __FILE__); ?>" alt="help"></a>
					<span class="wrapper_status"></span>
					<p class="error hidden">Please enter a page title</p>
					<div id="dynamic_page_url" style="display: none;">
						<span class="label">Dynamic Page Link:</span>
						<div class="input-prepend">
							<span id="protocol" class="label"></span>
							<input id="page_link" type="text" value="<?php echo $wrapper_page_url; ?>" readonly>
						</div>
					</div>
				</div>
			</div>
		</div>

	<div class="saveFooter">
		<input type="submit" value="<?php esc_html_e('Save Changes') ?>" id="save_changes" class="button-primary update_idxlinks"  />
		<span class="status"></span>
		<input type="hidden" name="action_mode" id="action_mode" value="" />
	</div>
	<?php settings_fields( 'idx-platinum-settings-group' ); ?>
	</form>

</div>