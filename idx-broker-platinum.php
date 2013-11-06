<?php



/*



Plugin Name: IDX Broker Platinum



Plugin URI: http://www.idxbroker.com



Description: Over 550 IDX/MLS feeds serviced. The #1 IDX/MLS solution just got even better!



Version: 1.0.7



Author: IDX, Inc.



Author URI: http://www.idxbroker.com/



License: GPL



*/



// Report all errors during development. Remember to hash out when sending to production. 



//error_reporting(E_ALL);



//Prevent script timeout when API response is slow



set_time_limit(0);



// The function below adds a settings link to the plugin page. 



$plugin = plugin_basename(__FILE__); 



$api_error = false; 



define('SHORTCODE_SYSTEM_LINK', 'idx-platinum-system-link');



define('SHORTCODE_SAVED_LINK', 'idx-platinum-saved-link');



define('SHORTCODE_WIDGET', 'idx-platinum-widget');



//Adds a comment declaring the version of the WordPress.



function display_wpversion() {



	echo "\n\n<!-- Wordpress Version ";



	echo bloginfo('version');



	echo " -->";



}



//Adds legacy start and stop tag function only when original IDX plugin is not installed

function idx_original_plugin_check() { 

	if (function_exists('idx_start')) {

		echo '';

	}

	else {

		function idx_start() {

			return '<div id="idxStart" style="display: none;"></div>';

		}

		function idx_stop() {

			return '<div id="idxStop" style="display: none;"></div>';

		}

	}

}



/**



 * Function that is executed when plugin is activated.



 */



function idx_activate() {



	global $wpdb;



	if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."posts_idx'") != $wpdb->prefix.'posts_idx') {



		$sql = "CREATE TABLE " . $wpdb->prefix."posts_idx" . " (



		`id` int(11) NOT NULL AUTO_INCREMENT,



		`post_id` int(11) NOT NULL,



		`uid` varchar(255) NOT NULL,



		`link_type` int(11) NOT NULL COMMENT '0 for system link and 1 for saved link',



		PRIMARY KEY (`id`)



		)";



		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');



		dbDelta($sql);



	}



}



//Adds a comment declaring the version of the IDX Broker plugin if it is activated.



function idx_broker_activated() {







	echo "\n<!-- IDX Broker Platunum WordPress Plugin v1.0.7 Activated -->\n\n";



}







function idx_broker_platinum_plugin_actlinks( $links ) { 



	// Add a link to this plugin's settings page



	$settings_link = '<a href="options-general.php?page=idx-broker-platinum">Settings</a>'; 



	array_unshift( $links, $settings_link ); 



	return $links; 



}





add_action('wp_head', 'idx_original_plugin_check');



add_action('wp_head', 'display_wpversion');



add_action('wp_head', 'idx_broker_activated');



add_action('admin_menu', 'idx_broker_platinum_menu');



add_action('admin_menu', 'idx_broker_platinum_options_init' ); 



add_filter("plugin_action_links_$plugin", 'idx_broker_platinum_plugin_actlinks' );



add_action('wp_ajax_idx_refresh_api', 'idx_refreshapi' );



add_action('wp_ajax_idx_update_links', 'idx_update_links' );











add_action('wp_ajax_idx_update_systemlinks', 'idx_update_systemlinks' );



add_action('wp_ajax_idx_update_savedlinks', 'idx_update_savedlinks' );



//Adding shortcodes







add_shortcode('idx-platinum-link', 'show_link');



add_shortcode('idx-platinum-system-link', 'show_system_link');



add_shortcode('idx-platinum-saved-link', 'show_saved_link');



add_shortcode('idx-platinum-widget', 'show_widget');







//Register the idx button



add_action('init', 'idx_buttons');







/**



 * registers the buttons for use



 * @param array $buttons



 */



function register_idx_buttons($buttons) {



	// inserts a separator between existing buttons and our new one



	array_push($buttons, "|", "idx_button");



	return $buttons;



}







/**



 * filters the tinyMCE buttons and adds our custom buttons



 */



function idx_buttons() {



	// Don't bother doing this stuff if the current user lacks permissions



	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )



		return;







	// Add only in Rich Editor mode



	if ( get_user_option('rich_editing') == 'true') {



		// filter the tinyMCE buttons and add our own



		add_filter("mce_external_plugins", "add_idx_tinymce_plugin");



		add_filter('mce_buttons', 'register_idx_buttons');



	}



}







/**



 * add the button to the tinyMCE bar



 * @param array $plugin_array



 */



function add_idx_tinymce_plugin($plugin_array) {



	$plugin_array['idx_button'] = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__)) .  '/idx-buttons.js';



	return $plugin_array;



}



/**



 * This adds the options page to the WP admin.



 * 



 * @params void



 * @return Admin Menu 



 */







function idx_broker_platinum_menu() {



	



	add_options_page('IDX Broker Platinum Plugin Options', 'IDX Broker Platinum', 'administrator', 'idx-broker-platinum', 'idx_broker_platinum_admin_page');	



}



	//Include dependecy files for IDX plugin



	if (file_exists(dirname(__FILE__) . '/idx-broker-platinum-api.php')) {



		include dirname(__FILE__) . '/idx-broker-platinum-api.php';



	} else {



		echo '<!-- Couldn\'t find the form template. //-->' . "\n";



	}



	if (file_exists(dirname(__FILE__) . '/idx-broker-widgets.php')) {



		include dirname(__FILE__) . '/idx-broker-widgets.php';



	} else {



		echo '<!-- Couldn\'t find the form template. //-->' . "\n";



	}







/**



* This function runs on plugin activation.  It sets up all options that will need to be



* saved that we know of on install, including cid, pass, domain, and main nav links from



* the idx broker system.



* 



* @params void



* @return void



*/







function idx_broker_platinum_options_init() {



	



	global $api_error;



	//register our settings



	register_setting( 'idx-platinum-settings-group', "idx_broker_apikey" );		



	/*



	 *	Since we have custom links that can be added and deleted inside



	 *	the IDX Broker admin, we need to grab them and set up the options



	 *	to control them here.  First let's grab them, if the API is not blank.



	 */



	if (get_option('idx_broker_apikey') != '') {	



		$system_links_cache = get_transient('idx_systemlinks_cache');



		$saved_links_cache = get_transient('idx_savedlink_cache');



		



		if($system_links_cache) {



			$systemlinks = $system_links_cache;



		} else {



			$systemlinks = idx_platinum_get_systemlinks();



			if( is_wp_error($systemlinks) ) {



				$api_error = $systemlinks->get_error_message();



				$systemlinks = '';



			}



		}



		



		if($saved_links_cache) {



			$savedlinks = $saved_links_cache;



		} else {



			



			$savedlinks = idx_platinum_get_savedlinks();



			if( is_wp_error($savedlinks) ) {



				$api_error = $savedlinks->get_error_message();



				$savedlinks = '';



			}



		}



		$_COOKIE = false;



		if($_COOKIE["api_refresh"] == 1)



		{



			update_system_page_links($systemlinks);



			update_saved_page_links($savedlinks);



		}







	}	



	



	wp_enqueue_script('idxjs', plugins_url('idx-broker-platinum/idxbroker.js'), 'jquery');



	wp_enqueue_style('idxcss', plugins_url('idx-broker-platinum/css/idxbroker.css'));



	



}







/**



 * Function to updated the system links data in posts and postmeta table



 * @param object $systemlinks



 */



function update_system_page_links($systemlinks) {



	global $wpdb;







	foreach($systemlinks as $systemlink){



		$post_id = $wpdb->get_var("SELECT post_id from ".$wpdb->prefix."posts_idx WHERE uid = '$systemlink->uid' AND link_type = 0");



		



		if($post_id) {



			//update the system links



			$rows_updated = $wpdb->update($wpdb->postmeta, array('meta_value' => $systemlink->url), array('post_id' => $post_id));



			$post_title = str_replace('_', ' ', $systemlink->name);



			$post_name = str_replace('', '_', $systemlink->name);



			$wpdb->update($wpdb->posts, array('post_title' => $post_title, 



					'post_name' => $post_name), array('ID' => $post_id));			



		}



	}



}







/**



 * Function to updated the saved links data in posts and postmeta table



 * @param object $savedlinks



 */



function update_saved_page_links($savedlinks)



{



	global $wpdb;







	foreach($savedlinks as $savedlink){



		$post_id = $wpdb->get_var("SELECT post_id from ".$wpdb->prefix."posts_idx WHERE uid = '$savedlink->uid' AND link_type = 1");



	



		if($post_id) {



			//update the saved links



			$wpdb->update($wpdb->postmeta, array('meta_value' => $savedlink->url), array('post_id' => $post_id));



			$post_title = str_replace('_', ' ', $savedlink->linkName);



			$post_name = str_replace('', '_', $savedlink->linkName);



			$wpdb->update($wpdb->posts, array('post_title' => $post_title, 



					'post_name' => $post_name), array('ID' => $post_id));



		}



	}



}



/**



 * This is tiggered and is run by idx_broker_menu, it's the actual IDX Broker Admin page and display.



 * 



 * @params void



 * @return void



*/







function idx_broker_platinum_admin_page() {



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



		$systemlinks = idx_platinum_get_systemlinks();



		if( is_wp_error($systemlinks) ) {



			$api_error = $systemlinks->get_error_message();



			$systemlinks = '';



			



		}



		$savedlinks = idx_platinum_get_savedlinks();



		if( is_wp_error($savedlinks) ) {



			$api_error = $savedlinks->get_error_message();



			$savedlinks = '';



		}



	}



	?>



		<div id="idxPluginWrap" class="wrap">



			<a href="http://www.idxbroker.com" target="_blank">



				<div id="ptlogo"></div>



				<div id="logo"></div>



			</a>



			<div style="display: table; width: 87%;">



				<h2 class="flft">IDX Broker Platinum&reg; Plugin Settings</h2>



				<br clear="all"/>



			</div>	



			



			<h3 class="hndle">



				<label>Step 1: Get an API Key</label>



				<a href="http://kb.idxbroker.com/index.php?/Knowledgebase/Article/View/98/16/idx-broker-platinum-wordpress-plugin" class="helpIcon" target="_blank"></a>



			</h3>



			<form method="post" action="options.php" id="idx_broker_options">



				<?php wp_nonce_field('update-options'); ?>



				<div id="blogUrl" style="display: none;" ajax="<?php bloginfo('wpurl'); ?>"></div>



				<ul id="genSettings">



					<li>



						<label for="idx_broker_apikey">Enter Your API Key: </label>



						<input name="idx_broker_apikey" type="text" id="idx_broker_apikey" value="<?php echo get_option('idx_broker_apikey'); ?>" />



                       <input type="button" name="api_update" id="api_update" value="Refresh Plugin Options" class="button-primary" style="width:153px;" />



						<span class="refresh_status"></span> 	



                        <li class="error" id="idx_broker_apikey_error">



						Please enter your API key to continue. 



						<br />



						If you do not have an IDX Broker Platinum account, please contact the IDX Broker team at 800-421-9668.</label>



					



					<?php 



						if($api_error) { 



							echo '<li class="error" style="display:block;">'.$api_error.'</li>';



						}



					?>



					</li>



					</li>



				



				</ul>



				<h3>



					<label>Step 2: Add IDX Widgets</label>



					



				</h3><ul id="widgSettings">
				  <li>



				IDX Widgets give you a way to promote your Featured Listings, Agents, and any Custom Links. In addition, you have access to Quick Search forms, a Lead Login widget, and several more powerful Widgets. If you have created additional Widgets in IDX Broker Platinum, simply click the "Refresh Plugin Information" button in Step 1 and then visit your <a href="widgets.php">Widgets Tab</a> in WordPress to drag-and-drop IDX Widgets into your sidebar. <br /><br />Take me to my <a href="widgets.php">Widgets Tab</a> now.</li></ul>



				<h3>



					<label>Step 3: Add IDX System Navigation Links</label>



					



				</h3>



				<p>Most IDX Broker Platinum subscribers add Basic Search, Map Search, Advanced Search, Featured Listings, and a Roster Page to their site navigation. Note that the IDX Broker Platinum plugin will add these pages automatically when you check the corresponding box below. You may then create a <a href="nav-menus.php">Custom Menu</a> using these pages, or reorder the display of these pages using your <a href="edit.php?post_type=page">Pages Tab</a> in WordPress.



				<br class="clear" />



				<?php 



					



					if (empty($systemlinks)) {



						$display_class = 'dispNone';



  				?>



					<div>



						You do not have any system links because you may have entered an incorrect API key. Please review Step 1.</p>



					</div>



				<?php 



					} else { 



						$check_sys_option = (get_option('idx_systemlink_group') == 1)?'checked="checked"':'';



				?>



				



			<br />	Check the box next to the page link you wish to add to your navigation. To remove an IDX page, simply uncheck the box next to the page you wish to remove and click the "Update System Links" button.</p>



				<ul class="linkList">	



				<?php 



				$my_system_links = get_my_system_links();



					foreach($systemlinks as $system_link) {



						if($system_link->systemresults != '1') {						



						$std_check_options = (in_array($system_link->uid,$my_system_links))? 'checked = "checked"': '';



				?>	



						<li>



							<input type="checkbox" name="idx_platinum_system_<?php echo $system_link->uid;?>" id="idx_platinum_system_<?php echo $system_link->uid;?>" <?php echo $std_check_options; ?> class="systemLink idx_platinum_sl" onclick="systemlink_check();" />



							<input type="hidden" name="idx_platinum_system_<?php echo $system_link->uid;?>_url" value="<?php echo $system_link->url;?>" />



							<input type="hidden" name="idx_platinum_system_<?php echo $system_link->uid;?>_name" value="<?php echo $system_link->name;?>" />



							<label for="idx_platinum_system_<?php echo $system_link->uid;?>" class="linkLabel">- <?php echo str_replace($search_item, ' ', $system_link->name); ?></label>



						</li>



				<?php 	



						}



					}



				}



				?>



				



                <br class="clear" />



                <?php if(count($systemlinks) > 0):?>



                <span>



						<input type="checkbox" name="idx_systemlink_group" id="idx_systemlink_group" <?php echo $check_sys_option;?> />



						<label for="idx_systemlink_group" class="link-label">- Add/Remove All Pages</label>



					</span>



				<?php endif;?>	



                </ul>



                



              



                <div class="linkHeader <?php echo $display_class; ?>">



					



					<!-- Removed as per UI clean up - LW span style="margin-left:20px;">



						<input type="submit" value="<? //php _e('Update System Links') ?>" name="update_systemlink" id="save_systemlinks" class="button-primary update_idxlinks" ajax="<? //php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php" />



					</span -->



					<span class="system_status"></span>



				</div>



                



				  <h3>



					<label>Step 4: Add IDX Neighborhood and Other Special Navigation Links</label>



                



              </h3>



                



                



                



				



				<?php 



					if (empty($savedlinks)) { 



						$display_class = 'dispNone';



					} else {



						$display_class = '';



					}



					



					$check_saved_option = (get_option('idx_savedlink_group') == 1)?'checked="checked"':'';



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



  				if (empty($savedlinks)) {



  				?>



				<div>



					<p>You may create and save an unlimited number of Saved Links (e.g., neighborhood results, short sale results, etc). <br />



					<br />



					To create your saved links, login to IDX Broker Platinum and go to <a href="http://middleware.idxbroker.com/mgmt/savedlinks.php" target="_blank">Saved Links.</a> Once you have built and saved your saved links, revisit this page and hit the refresh button. Your new links will automatically appear below. Simply choose the custom links that you wish to display in your theme header navigation and IDX Broker Platinum will add those pages and link to the corresponding IDX results.</p>



				</div>



			<?php 



			} else {



    	?>



		<p>Add custom neighborhood, subdivision, and other special links to your website. To create or edit saved links, login to IDX Broker and view the <a href="http://middleware.idxbroker.com/mgmt/savedlinks.php" target="_blank">Saved Links</a> page. Once you've created links, open your IDX Broker Platinum Plugin settings page and click the Refresh Plugin Options to add your saved links to this list. Your new links will appear below. Click to add or remove any page links that you don't want to add.</p>



		<ul class="savedLinklist">



		<?php



			$my_saved_links = get_my_saved_links();



			foreach ($savedlinks as $saved_link) {



				//$checkOption = (get_option("idx_platinum_saved_".$link_name) == 'on')?'checked="checked"':'';



				$checkOption = (in_array($saved_link->uid,$my_saved_links))? 'checked = "checked"': '';



		?>



			<li>



							<input type="checkbox" name="idx_platinum_saved_<?php echo $saved_link->uid;?>" id="idx_platinum_saved_<?php echo $saved_link->uid;?>" <?php echo $checkOption; ?> class="savedLink idx_platinum_sdl" onclick="savedlink_check();" />



							<input type="hidden" name="idx_platinum_saved_<?php echo $saved_link->uid;?>_url" value="<?php echo $saved_link->url;?>" />



							<input type="hidden" name="idx_platinum_saved_<?php echo $saved_link->uid;?>_name" value="<?php echo $saved_link->linkName;?>" />



							<label for="idx_platinum_saved_<?php echo $saved_link->uid;?>" style="padding-left: 2px;" class="linkLabel">- <?php echo str_replace($search_item, ' ', $saved_link->linkName); ?></label>



			</li>  



		<?php	



			}	



		}



		?>



			<br class="clear" />



            <?php if(count($savedlinks) > 0):?>



            <span>



						<input type="checkbox" name="idx_savedlink_group" id="idx_savedlink_group" <?php echo $check_saved_option;?> />



						<label for="idx_savedlink_group" class="linkLabel">- Add/Remove All Pages</label>



					</span>



			<?php endif;?>		



					<div class="linkHeader <?php echo $display_class; ?>" style="border-bottom: none;">



					



					<!-- span style="margin-left:15px;">



						<input type="submit" value="<?//php _e('Update Saved Links') ?>" name="update_savedlink" id="save_savedlinks" class="button-primary update_idxlinks" ajax="<?//php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php" />



					</span -->



					<span class="saved_status" style="border-bottom: none;"></span>



				</div>



				



        



        <br clear="all" />



		<div class="saveFooter">



			<input type="submit" value="<?php _e('Save Changes') ?>" id="save_changes" class="button-primary update_idxlinks" ajax="<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php" />



			<span class="status"></span>



			<br class="clear" />



			<input type="hidden" name="action_mode" id="action_mode" value="" />



		</div>



		<?php settings_fields( 'idx-platinum-settings-group' ); ?>



		</form>



	</div>



	<?php   



}



/**



 * Function to delete existing cache. So API response in cache will be deleted



 * 



 * @param void



 * @return void



 * 



 */



function idx_refreshapi() {







	if(get_transient('idx_savedlink_cache')) {



		delete_transient('idx_savedlink_cache');



	}



	if(get_transient('idx_widget_cache')) {



		delete_transient('idx_widget_cache');	



	}	



	if(get_transient('idx_systemlinks_cache')) {



		delete_transient('idx_systemlinks_cache');



	}



	setcookie("api_refresh", 1, time()+20);



	die();



}



/**



 * 



 * Function to update the links from IDX API



 * Based upon button click the respective sections of links saved to database and create pages



 * 



 * @param void



 * @return void



 */



function idx_update_links() {



	



	if(isset($_REQUEST['idx_savedlink_group']) && $_REQUEST['idx_savedlink_group'] == 'on') {



		update_option('idx_savedlink_group', 1);



	} else {



		update_option('idx_savedlink_group', 0);



	}



	if(isset($_REQUEST['idx_systemlink_group']) && $_REQUEST['idx_systemlink_group'] == 'on') {



		update_option('idx_systemlink_group', 1);



	} else {



		update_option('idx_systemlink_group', 0);



	}



	



	update_systemlinks();



	update_savedlinks();



	die();



}







/**



 * This function will allow users to create page using saved links and 



 * display in their main navigation.



 *  



 *  @params void



 * 	@return void



 */



function idx_update_systemlinks() {



	update_systemlinks();



	die();



}



/**



 * 



 * Function to update System links from IDX API



 * Based upon click, the links saved to database and create pages



 * 



 * @param void



 * @return void



 */



function update_systemlinks() {



	global $wpdb;



	



	if($_REQUEST['save_action'] == 'systemlinks') {



		$filter_condition = '';



		if(isset($_REQUEST['idx_systemlink_group']) && $_REQUEST['idx_systemlink_group'] == 'on') {



			update_option('idx_systemlink_group', 1);



		} else {



			update_option('idx_systemlink_group', 0);



		}



	}



	



	if (!isset($wpdb->posts_idx)) {



		$wpdb->posts_idx = $wpdb->prefix . 'posts_idx';



	}



	$my_links = get_my_system_links();



	$new_links = array();



	unset($_REQUEST['idx_systemlink_group']);



	unset($_REQUEST['idx_savedlink_group']);



	foreach ($_REQUEST as $submitted_link_name => $submitted_link)



	{



		//Checkbox is checked



		if($submitted_link == 'on')



		{



			if (check_system_link($submitted_link_name))



			{



				$uid = str_replace('idx_platinum_system_', '', $submitted_link_name);



				$post_title = str_replace('_', ' ', $_REQUEST[$submitted_link_name.'_name']);



				$post_name = str_replace('', '_', $_REQUEST[$submitted_link_name.'_name']);



				$new_links[] = $uid;



				if($row = $wpdb->get_row("SELECT id,post_id FROM ".$wpdb->prefix."posts_idx WHERE uid = '$uid' ", ARRAY_A) ) {



						



					$wpdb->update(



							$wpdb->posts,



							array(



									'post_title' => $post_title,



									'post_type' => 'page',



									'post_name' => $post_name



							),



							array(



									'ID' => $row['post_id']



							),



							array(



									'%s',



									'%s',



									'%s'



							),



							array(



									'%d'



							)



					);



	



					$wpdb->update(



							$wpdb->postmeta,



							array(



									'meta_key' => '_links_to',



									'meta_value' => $_REQUEST[$submitted_link_name.'_url'],



							),



							array(



									'post_id' => $row['post_id']



							),



							array(



									'%s',



									'%s'



							),



							array(



									'%d'



							)



					);



				}



				else {



					// Insert into post table



					$wpdb->insert(



							$wpdb->posts,



							array(



									'post_title' => $post_title,



									'post_type' => 'page',



									'post_name' => $post_name



							),



							array(



									'%s',



									'%s',



									'%s'



							)



					);



					$post_id = $wpdb->insert_id;



					// Insert into post meta



					$wpdb->insert(



							$wpdb->postmeta,



							array(



									'meta_key' => '_links_to',



									'meta_value' => $_REQUEST[$submitted_link_name.'_url'],



									'post_id' => $wpdb->insert_id



							),



							array(



									'%s',



									'%s',



									'%d'



							)



					);



					//Insert into mapping table



					$wpdb->insert(



							$wpdb->posts_idx,



							array(



									'post_id' => $post_id,



									'uid' => $uid,



									'link_type' => 0



							),



							array(



									'%d',



									'%s',



									'%d'



							)



					);



				}



			}



		}



	}



	$uids_to_delete = array_diff($my_links, $new_links);



	



	if($uids_to_delete > 0)	{



		delete_pages_byuid($uids_to_delete);



	}



}



/**



 * FUnction to check if a link is system link or not



 * @param link name $link_name



 */



function check_system_link($link_name) {



	



	if(strpos($link_name, 'idx_platinum_system') !== false) {



		return true;



	} else {



		return false;	



	}



}







/**



 * FUnction to get current system links



 */



function get_my_system_links() {



	global $wpdb;



	return $wpdb->get_col("SELECT uid from ".$wpdb->prefix."posts_idx where link_type = 0");



}







/**



 * FUnction to delete pages by passing uid(from API).



 *  



 * @param string $uids



 * @param int $link_type type of link 0 for system and 1 for saved



 */



function delete_pages_byuid($uids,$link_type = 0)



{



	global $wpdb;



	$uid_string = "";



	



	if(count($uids) > 0)



	{



		foreach($uids as $uid) {



			$uid_string .= "'$uid',";



		}



		$uid_string = rtrim($uid_string,',');



		$pages_to_delete = $wpdb->get_col("SELECT post_id from ".$wpdb->prefix."posts_idx where uid IN ($uid_string) AND link_type = $link_type");







		if($wpdb->query("DELETE from ".$wpdb->prefix."posts_idx where uid IN ($uid_string) AND link_type = $link_type") !== false) {



			foreach($pages_to_delete as $page) {



				wp_delete_post($page,true);



				$wpdb->query("DELETE from ".$wpdb->prefix."postmeta where post_id = $page");



			}



		}



		



		return true;



	}



	



	return false;



}



/**



 * 



 * Function to update Saved links from IDX API



 * Based upon click, the links saved to database and create pages



 * 



 * @param void



 * @return void



 */



function idx_update_savedlinks() {



	update_savedlinks();



	die();



}



/**



 * 



 * Function to update System links from IDX API



 * Based upon click, the links saved to database and create pages



 * 



 * @param void



 * @return void



 */



function update_savedlinks() {



	global $wpdb;



	if($_REQUEST['save_action'] == 'savedlinks') {



		if(isset($_REQUEST['idx_savedlink_group']) && $_REQUEST['idx_savedlink_group'] == 'on') {



			update_option('idx_savedlink_group', 1);



		} else {



			update_option('idx_savedlink_group', 0);



		}



	}



	if (!isset($wpdb->posts_idx)) {



		$wpdb->posts_idx = $wpdb->prefix . 'posts_idx';



	}



	$my_links = get_my_saved_links();



	$new_links = array();



	unset($_REQUEST['idx_savedlink_group']);



	unset($_REQUEST['idx_systemlink_group']);



	



	foreach ($_REQUEST as $submitted_link_name => $submitted_link) {



		//Checkbox is checked



		if($submitted_link == 'on')	{



			if (check_system_link($submitted_link_name) === false) {



				$uid = str_replace('idx_platinum_saved_', '', $submitted_link_name);



				$post_title = str_replace('_', ' ', $_REQUEST[$submitted_link_name.'_name']);



				$post_name = str_replace('', '_', $_REQUEST[$submitted_link_name.'_name']);



				$new_links[] = $uid;



				if($row = $wpdb->get_row("SELECT id,post_id FROM ".$wpdb->prefix."posts_idx WHERE uid = '$uid' ", ARRAY_A) )



				{



	



					$wpdb->update(



							$wpdb->posts,



							array(



									'post_title' => $post_title,



									'post_type' => 'page',



									'post_name' => $post_name



							),



							array(



									'ID' => $row['post_id']



							),



							array(



									'%s',



									'%s',



									'%s'



							),



							array(



									'%d'



							)



					);



	



					$wpdb->update(



							$wpdb->postmeta,



							array(



									'meta_key' => '_links_to',



									'meta_value' => $_REQUEST[$submitted_link_name.'_url'],



							),



							array(



									'post_id' => $row['post_id']



							),



							array(



									'%s',



									'%s'



							),



							array(



									'%d'



							)



					);



				} else {



					// Insert into post table



					$wpdb->insert(



							$wpdb->posts,



							array(



									'post_title' => $post_title,



									'post_type' => 'page',



									'post_name' => $post_name



							),



							array(



									'%s',



									'%s',



									'%s'



							)



					);



					$post_id = $wpdb->insert_id;



					// Insert into post meta



					$wpdb->insert(



							$wpdb->postmeta,



							array(



									'meta_key' => '_links_to',



									'meta_value' => $_REQUEST[$submitted_link_name.'_url'],



									'post_id' => $wpdb->insert_id



							),



							array(



									'%s',



									'%s',



									'%d'



							)



					);



					//Insert into mapping table



					$wpdb->insert(



							$wpdb->posts_idx,



							array(



									'post_id' => $post_id,



									'uid' => $uid,



									'link_type' => 1



							),



							array(



									'%d',



									'%s',



									'%d'



							)



					);



				}



			}



		}



	}



	$uids_to_delete = array_diff($my_links, $new_links);



	



	if($uids_to_delete > 0)	{



		delete_pages_byuid($uids_to_delete, 1);



	}



}











/**



 * FUnction to get current saved links



 */



function get_my_saved_links() {



	global $wpdb;



	return $wpdb->get_col("SELECT uid from ".$wpdb->prefix."posts_idx where link_type = 1");



}











// Compat functions for WP < 2.8



if ( !function_exists( 'esc_attr' ) ) {



	function esc_attr( $attr ) {



		return attribute_escape( $attr );



	}







	function esc_url( $url ) {



		return clean_url( $url );



	}



}







/**



 * Function to get meta data of created pages uisng IDX settings page



 *  



 * @params void



 * @return String Page/Post URL 



 */







function idxplatinum_get_page_links_to_meta () {



	global $wpdb, $page_links_to_cache, $blog_id;







	if ( !isset( $page_links_to_cache[$blog_id] ) )



		$links_to = idxplatinum_get_post_meta_by_key( '_links_to' );



	else



		return $page_links_to_cache[$blog_id];







	if ( !$links_to ) {



		$page_links_to_cache[$blog_id] = false;



		return false;



	}







	foreach ( (array) $links_to as $link )



		$page_links_to_cache[$blog_id][$link->post_id] = $link->meta_value;







	return $page_links_to_cache[$blog_id];



}







/**



 * Function to override permalink tab in post/page section of Wordpress



 *  



 * @params string $link



 * @params object post details



 * @return string Page/Post URL 



 */







function idxplatinum_filter_links_to_pages ($link, $post) {



	$page_links_to_cache = idxplatinum_get_page_links_to_meta();







	// Really strange, but page_link gives us an ID and post_link gives us a post object



	$id = isset( $post->ID ) ? $post->ID : $post;







	if ( isset($page_links_to_cache[$id]) )



		$link = esc_url( $page_links_to_cache[$id] );







	return $link;



}







/**



 * Function to redirect the page based upon _links_to_ attribute



 * 



 * @param void



 * @return void	



 */







function idxplatinum_redirect_links_to_pages() {



	if ( !is_single() && !is_page() )



		return;







	global $wp_query;







	$link = get_post_meta( $wp_query->post->ID, '_links_to', true );







	if ( !$link )



		return;







	$redirect_type = get_post_meta( $wp_query->post->ID, '_links_to_type', true );



	$redirect_type = ( $redirect_type = '302' ) ? '302' : '301';



	wp_redirect( $link, $redirect_type );



	exit;



}







/**



 * Function to highlight the page links



 * 



 * @param array $pages



 * @return array $pages	



 */







function idxplatinum_page_links_to_highlight_tabs( $pages ) {



	$page_links_to_cache = idxplatinum_get_page_links_to_meta();



	$page_links_to_target_cache = idxplatinum_get_page_links_to_targets();







	if ( !$page_links_to_cache && !$page_links_to_target_cache )



		return $pages;







	$this_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];



	$targets = array();







	foreach ( (array) $page_links_to_cache as $id => $page ) {



		if ( isset( $page_links_to_target_cache[$id] ) )



			$targets[$page] = $page_links_to_target_cache[$id];







		if ( str_replace( 'http://www.', 'http://', $this_url ) == str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) == str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {



			$highlight = true;



			$current_page = esc_url( $page );



		}



	}







	if ( count( $targets ) ) {



		foreach ( $targets as  $p => $t ) {



			$p = esc_url( $p );



			$t = esc_attr( $t );



			$pages = str_replace( '<a href="' . $p . '" ', '<a href="' . $p . '" target="' . $t . '" ', $pages );



		}



	}



    global $highlight;



	if ( $highlight ) {



		$pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting



		$pages = preg_replace( '|<li class="([^"]+)"><a href="' . $current_page . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );



	}







	return $pages;



}







/**



 * Function to get page _link _to_ targets



 * 



 * @param void



 * @return string page meta value



 */







function idxplatinum_get_page_links_to_targets () {



	global $wpdb, $page_links_to_target_cache, $blog_id;







	if ( !isset( $page_links_to_target_cache[$blog_id] ) )



		$links_to = idxplatinum_get_post_meta_by_key( '_links_to_target' );



	else



		return $page_links_to_target_cache[$blog_id];







	if ( !$links_to ) {



		$page_links_to_target_cache[$blog_id] = false;



		return false;



	}







	foreach ( (array) $links_to as $link )



		$page_links_to_target_cache[$blog_id][$link->post_id] = $link->meta_value;







	return $page_links_to_target_cache[$blog_id];



}







/**



 * Functiom to get post meta by key



 * 



 * @param string $key



 * @return string meta value



 */







function idxplatinum_get_post_meta_by_key( $key ) {



	global $wpdb;



	return $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key ) );



}







/**



 * Function to delete saved IDX page IDs from option table



 * 



 * @param integer page_id



 * @return void



 * 



 */







function idxplatinum_update_pages($post_ID) {



	global $wpdb;



	$wpdb->query("DELETE from ".$wpdb->prefix."posts_idx where post_id = $post_ID");		



	delete_post_meta( $post_ID, '_links_to' );



	delete_post_meta( $post_ID, '_links_to_target' );



	delete_post_meta( $post_ID, '_links_to_type' );







}







/**



 * Function to delete meta table if post/page is deleted by user



 * 



 * @param integer $post_ID



 * @return integer $post_ID



 */







function idxplatinum_plt_save_meta_box( $post_ID ) {



	



	if ( wp_verify_nonce( isset($_REQUEST['_idx_pl2_nonce']), 'idxplatinum_plt' ) ) {



		if ( isset( $_POST['idx_links_to'] ) && strlen( $_POST['idx_links_to'] ) > 0 && $_POST['idx_links_to'] !== 'http://' ) {



			$link = stripslashes( $_POST['idx_links_to'] );



			if ( 0 === strpos( $link, 'www.' ) )



				$link = 'http://' . $link; // Starts with www., so add http://



			update_post_meta( $post_ID, '_links_to', $link );



			if ( isset( $_POST['idx_links_to_new_window'] ) )



				update_post_meta( $post_ID, '_links_to_target', '_blank' );



			else



				delete_post_meta( $post_ID, '_links_to_target' );



			if ( isset( $_POST['idx_links_to_302'] ) )



				update_post_meta( $post_ID, '_links_to_type', '302' );



			else



				delete_post_meta( $post_ID, '_links_to_type' );



		} else {



			delete_post_meta( $post_ID, '_links_to' );



			delete_post_meta( $post_ID, '_links_to_target' );



			delete_post_meta( $post_ID, '_links_to_type' );



		}



	}



	



	return $post_ID;



}







/**



 * Function to display warning message in permalink page



 * 



 * @param void



 * @return void



 * 



 */







function idxplatinum_notice() {



	global $current_screen;



	echo '<div id="message" class="error"><p><strong>Note that your IDX Broker page links are not governed by WordPress Permalinks. To apply changes to your IDX Broker URLS, you must login to your IDX Broker Control Panel.</strong></p></div>';



}







/**



 * Function to generate permalink warning message



 * 



 * @param void



 * @return void



 */







function permalink_update_warning () {



	if(isset($_POST['permalink_structure']) || isset($_POST['category_base'])) {



		add_action('admin_notices', 'idxplatinum_notice');



	}



}







/**



 * Function to show a idx link with shortcode of type:



 * [idx-platinum-link title="title here"]



 * 



 * @param array $atts



 * @return html code for showing the link/ bool false



 */



function show_link($atts) {



	extract( shortcode_atts( array(



			'title' => NULL



	), $atts ) );



	



	if(!is_null($title)) {



		$page = get_page_by_title($title);



		$permalink = get_permalink($page->ID);



		



		return '<a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a>';



	} else {



		return false;



	}



}







/**



 * FUnction to show a idx system link with shortcode of type:



 * [idx-platinum-system-link title="title here"]



 * 



 * @param array $atts



 * @return string|boolean



 */



function show_system_link($atts) {



	extract( shortcode_atts( array(



			'id' => NULL,



			'title' => NULL,



	), $atts ) );	



	



	if(!is_null($id)) {



		$link = idx_get_link_by_uid($id, 0);



		if(is_object($link)) {



			if(!is_null($title)) {



				$link->name = $title;



			}



			return '<a href="'.$link->url.'">'.$link->name.'</a>';



		}



	} else {



		return false;



	}	



}







/**



 * FUnction to show a idx ssaved link with shortcode of type:



 * [idx-platinum-saved-link title="title here"]



 *



 * @param array $atts



 * @return string|boolean



 */



function show_saved_link($atts) {



	extract( shortcode_atts( array(



			'id' => NULL,



			'title' => NULL



	), $atts ) );







	if(!is_null($id)) {



		$link = idx_get_link_by_uid($id, 1);



		if(is_object($link)) {



			if(!is_null($title)) {



				$link->name = $title;



			}



			return '<a href="'.$link->url.'">'.$link->name.'</a>';



		}



	} else {



		return false;



	}	



}







/**



 * Function to get the widget code by title



 *



 * @param string $title



 * @return html code for showing the widget



 */



function idx_get_link_by_uid($uid, $type = 0) {



	if($type == 0) {



		$idx_links = get_transient('idx_systemlinks_cache');



	} elseif ($type == 1) {



		$idx_links = get_transient('idx_savedlink_cache');



	}



	$selected_link = '';



	if($idx_links) {



		foreach($idx_links as $link) {



			if(strcmp($link->uid, $uid) == 0) {



				$selected_link = $link;



			}



		}



	} 



	



	return $selected_link;



}











/**



 * Function to show a idx link with shortcode of type:



 * [idx-platinum-link title="widget title here"]



 * 



 * @param array $atts



 * @return html code for showing the widget/ bool false



 */



function show_widget($atts) {



	extract( shortcode_atts( array(



			'id' => NULL



	), $atts ) );	



	



	if(!is_null($id)) {



		return get_widget_by_uid($id);



	} else {



		return false;



	}	



}







/**



 * Function to get the widget code by title



 * 



 * @param string $title



 * @return html code for showing the widget



 */



function get_widget_by_uid($uid) {



	$idx_widgets = get_transient('idx_widget_cache');



	$idx_widget_code = null;



	



	if($idx_widgets) {



		foreach($idx_widgets as $widget) {



			if(strcmp($widget->uid, $uid) == 0) {



				$idx_widget_link = $widget->url;



				$idx_widget_code =  '<script src="'.$idx_widget_link.'"></script>';



				return $idx_widget_code;



			}



		}



	} else {



		return $idx_widget_code;



	}



}







/**



 * Function to print the system/saved link shortcodes.



 *



 * @param int $link_type 0 for system link and 1 for saved link



 */



function show_link_short_codes($link_type = 0)



{



	$available_shortcodes = '';



	if($link_type === 0) {



		$short_code = SHORTCODE_SYSTEM_LINK;



		$idx_links = get_transient('idx_systemlinks_cache');



	} elseif($link_type == 1) {



		$short_code = SHORTCODE_SAVED_LINK;



		$idx_links = get_transient('idx_savedlink_cache');



	} else {



		return false;



	}







	if(count($idx_links) > 0 AND is_array($idx_links)) {



		foreach ($idx_links as $idx_link) {



			if ($link_type === 0) {



				$available_shortcodes .= get_system_link_html($idx_link);



			}



			



			if($link_type == 1) {



				$available_shortcodes .= get_saved_link_html($idx_link);



			}



		}



	} else {



		$available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';



	}



	



	echo $available_shortcodes;



}







/**



 * Function to return the HTM for displaying each system link



 * @param object $idx_link



 * @return string



 */



function get_system_link_html($idx_link) {



	$available_shortcodes = "";



	if ($idx_link->systemresults != 1) {



		$link_short_code = '['.SHORTCODE_SYSTEM_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->name.'"]';



		$available_shortcodes .= '<div class="each_shortcode_row">';



		$available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';



		$available_shortcodes .= '<span>'.$idx_link->name.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>



		&nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->name).'&short_code='.urlencode($link_short_code).'">change title</a>



		</span>';



		$available_shortcodes .= '</div>';



	}



	return $available_shortcodes;



}







/**



 * Function to return the HTM for displaying each saved link



 * @param object $idx_link



 * @return string



 */



function get_saved_link_html($idx_link) {



	$available_shortcodes = "";



	$link_short_code = '['.SHORTCODE_SAVED_LINK.' id ="'.$idx_link->uid.'" title ="'.$idx_link->linkName.'"]';



	$available_shortcodes .= '<div class="each_shortcode_row">';



	$available_shortcodes .= '<input type="hidden" id=\''.$idx_link->uid.'\' value=\''.$link_short_code.'\'>';



	$available_shortcodes .= '<span>'.$idx_link->linkName.'&nbsp;<a name="'.$idx_link->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$idx_link->uid.'\')" class="shortcode_link">insert</a>



	&nbsp;<a href="?uid='.urlencode($idx_link->uid).'&current_title='.urlencode($idx_link->linkName).'&short_code='.urlencode($link_short_code).'">change title</a>



	</span>';



	$available_shortcodes .= '</div>';







	return $available_shortcodes;



}







/**



 * Function to print the shortcodes of all the widgets



 */



function show_widget_shortcodes() {



	$idx_widgets = get_transient('idx_widget_cache');



	$available_shortcodes = '';



	if($idx_widgets) {



		foreach($idx_widgets as $widget) {



			$widget_shortcode = '['.SHORTCODE_WIDGET.' id ="'.$widget->uid.'"]';



			$available_shortcodes .= '<div class="each_shortcode_row">';



			$available_shortcodes .= '<input type="hidden" id=\''.$widget->uid.'\' value=\''.$widget_shortcode.'\'>';



			$available_shortcodes .= '<span>'.$widget->name.'&nbsp;<a name="'.$widget->uid.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$widget->uid.'\')">insert</a></span>';



			$available_shortcodes .= '</div>';



		}



	} else {



		$available_shortcodes .= '<div class="each_shortcode_row">No shortcodes available.</div>';



	}







	echo $available_shortcodes;



}







add_filter( 'wp_list_pages',     'idxplatinum_page_links_to_highlight_tabs', 9     );



add_action( 'template_redirect', 'idxplatinum_redirect_links_to_pages'             );



add_filter( 'page_link',         'idxplatinum_filter_links_to_pages',        20, 2 );



add_filter( 'post_link',         'idxplatinum_filter_links_to_pages',        20, 2 );



add_action( 'save_post',         'idxplatinum_plt_save_meta_box'                   );



add_action( 'before_delete_post',       'idxplatinum_update_pages'                   );	



add_action( 'init',              'permalink_update_warning'                            );



register_activation_hook( __FILE__, 'idx_activate' );



?>