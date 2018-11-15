<?php
namespace IDX;

/**
 * Help class.
 */
class Help {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'load-post.php', array( $this, 'add_pages_help_tabs' ), 20 );
		add_action( 'load-post-new.php', array( $this, 'add_pages_help_tabs' ), 20 );
		add_action( 'load-edit.php', array( $this, 'add_wrappers_help' ), 20 );
		add_action( 'current_screen', array( $this, 'settings_help' ) );
		// Glow Help Button
		add_action( 'admin_enqueue_scripts', array( $this, 'glow' ) );
		add_action( 'wp_ajax_idx_disable_glow', array( $this, 'disable_glow' ) );
	}

	/**
	 * settings_help function.
	 *
	 * @access public
	 * @return void
	 */
	public function settings_help() {
		// Display Help on Settings Pages
		if ( ! empty( $_GET['page'] ) && ( $_GET['page'] === 'idx-broker' || $_GET['page'] === 'idx-omnibar-settings' ) ) {
			$this->add_settings_help_tabs();
		}
	}

	/**
	 * add_wrappers_help function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_wrappers_help() {
		// Display Help on Post Type UIs
		if ( ! empty( $_GET['post_type'] ) && ( $_GET['post_type'] === 'idx-wrapper' || $_GET['post_type'] === 'idx_page' ) ) {
			$this->add_settings_help_tabs();
		}
	}

	/**
	 * tabs
	 *
	 * @var mixed
	 * @access public
	 */
	public $tabs = array(
		// The assoc key represents the ID
		// It is NOT allowed to contain spaces
		'idx_walkthrough_video' => array(
			'title'   => 'Walkthrough',
			'content' => '
                <iframe width="560" height="315" src="https://www.youtube.com/embed/TEtqmnthsBE?list=PLjUedMdGsXxWVYxKIpJ8P5veq0_8JdZrK" frameborder="0" allowfullscreen></iframe>
            ',
		),
		'idx_api_key'           => array(
			'title'   => 'API Key',
			'content' => '
                <strong>API Key</strong>
                <br>&bull; The API key can be found in your <a href="https://middleware.idxbroker.com/mgmt/apikey.php" target="_blank">IDX Control Panel</a> under Home > API Control.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1911631-api-key-control?b_id=10433" target="_blank">this article</a>.
                ',
		),
		'idx_create_wrapper'    => array(
			'title'   => 'Creating the Wrapper',
			'content' => '
                <strong>Creating the Wrapper</strong> - Wrappers set the overall styling of your IDX Broker pages.
                <br>&bull; Create new page wrappers by entering a unique page name and selecting Update.
                <br>&bull; These pages are added to your Wrappers menu, not your WordPress pages.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper" target="_blank">this article</a>.
                ',
		),
		'idx_pages'             => array(
			'title'   => 'Integrating IDX Pages',
			'content' => '
                <strong>Integrating IDX Pages</strong> - Integrating IDX Pages into your website.
                <br>&bull; We recommend linking to IDX pages from your navigation by adding IDX Pages to your menus.
                <br>&bull; You can add pages under the IDX Pages category under Appearance > Menus or Appearance > Customize > Menus.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin" target="_blank">this article</a>.
                ',
		),
		'idx_widgets'           => array(
			'title'   => 'Adding Widgets',
			'content' => '
                <strong>Adding Widgets</strong> - Adding Widgets into your website.
                <br>&bull; You can add Widgets to widget areas of your website using the Customizer under Appearance > Customize > Widgets.
                <br>&bull; You can also add them under Appearance > Widgets.
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin" target="_blank">this article</a>.
                ',
		),
		'idx_apply_wrapper'     => array(
			'title'   => 'Applying Additional Wrappers',
			'content' => '
                <strong>Applying Additional Wrappers</strong> - You may create many wrappers and use different ones for each category or page.
                <br>&bull; To apply a new wrapper within WordPress, edit the Wrapper page from the IDX Broker/Wrappers menu.
                <br>&bull; In edit mode select where to apply the wrapper in the upper right of the screen.
                <br>
                <br>Additionally, you can make any post or page a Wrapper by adding the shortcode <strong>[idx-wrapper-tags]</strong> to the post/page.
                <br>
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper" target="_blank">this article</a>.
                ',
		),
		'idx_shortcodes'        => array(
			'title'   => 'IDX Shortcodes',
			'content' => '
                <strong>IDX Shortcodes</strong> - Insert IDX Broker content in any page or post.
                <br>&bull; Select the Insert IDX Shortcode button
                <br>&bull; System and Saved Links add an external link to IDX Broker pages
                <br>&bull; Widgets add widget content into your page.
                <br>&bull; Omnibar adds a property listing search bar to any of your pages
                <br>&bull; For more information, see <a href="http://support.idxbroker.com/customer/en/portal/articles/1917460-wordpress-plugin" target="_blank">this article</a>.
                ',
		),
	);

	/**
	 * add_pages_help_tabs function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_pages_help_tabs() {
		$id     = 'idx_shortcodes';
		$data   = $this->tabs['idx_shortcodes'];
		$screen = get_current_screen();
		$screen->add_help_tab(
			array(
				'id'    => $id,
				'title' => __( $data['title'], 'idxbroker' ),
				// Use the content only if you want to add something
					// static on every help tab. Example: Another title inside the tab
			'callback'  => array( $this, 'prepare' ),
			)
		);
	}

	/**
	 * add_settings_help_tabs function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_settings_help_tabs() {
		$tabs = $this->tabs;
		foreach ( $tabs as $id => $data ) {
			$screen = get_current_screen();
			$screen->add_help_tab(
				array(
					'id'    => $id,
					'title' => __( $data['title'], 'idxbroker' ),
					// Use the content only if you want to add something
						// static on every help tab. Example: Another title inside the tab
				'callback'  => array( $this, 'prepare' ),
				)
			);
			$screen->set_help_sidebar(
				'<p><a href="https://middleware.idxbroker.com/mgmt/login.php" target="_blank">IDX Control Panel</a></p>' .
				'<p><a href="http://support.idxbroker.com/customer/en/portal/topics/784215-wordpress/articles" target="_blank">IDX Plugin Knowledgebase</a></p>' .
				'<p><a href="http://support.idxbroker.com" target="_blank">IDX Support</a></p>'
			);
		}
	}

	/**
	 * prepare function.
	 *
	 * @access public
	 * @param mixed $screen
	 * @param mixed $tab
	 * @return void
	 */
	public function prepare( $screen, $tab ) {
		printf(
			'<p>%s</p>',
			__(
				$tab['callback'][0]->tabs[ $tab['id'] ]['content'],
				'idxbroker'
			)
		);
	}

	/**
	 * impress_settings_page function.
	 *
	 * @access public
	 * @return void
	 */
	public function impress_settings_page() {
		if ( ( ! empty( $_GET['page'] ) &&
			( $_GET['page'] === 'idx-broker' ||
				$_GET['page'] === 'idx-omnibar-settings' )
		) ||
			( ! empty( $_GET['post_type'] ) &&
				( $_GET['post_type'] === 'idx-wrapper' ||
					$_GET['post_type'] === 'idx_page'
				) ) ) {
			return true;
		}
	}

	/**
	 * glow function.
	 *
	 * @access public
	 * @return void
	 */
	public function glow() {
		// Make Help Button Glow for IMPress pages
		if ( ! get_option( 'idx_disable_glow' ) && $this->impress_settings_page() ) {

			wp_enqueue_script( 'idxhelpglow', plugins_url( '/assets/js/idx-help-menu.min.js', dirname( __FILE__ ) ), 'jquery' );
		}
	}

	// Disable the help button glowing via AJAX
	public function disable_glow() {
		update_option( 'idx_disable_glow', '1', false );
		wp_die();
	}
}
