<?php
namespace IDX\Shortcodes;

/**
 * Shortcode_Ui class.
 */
class Shortcode_Ui {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'media_buttons', array( $this, 'add_idx_media_button' ), 15, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_shortcode_js' ) );
		$this->shortcodes_for_ui = new \IDX\Shortcodes\Register_Shortcode_For_Ui();
	}

	/**
	 * shortcodes_for_ui
	 *
	 * @var mixed
	 * @access public
	 */
	public $shortcodes_for_ui;

	/**
	 * add_idx_media_button function.
	 *
	 * @access public
	 * @param mixed $editor_id
	 * @return void
	 */
	public function add_idx_media_button( $editor_id ) {
		if ( $editor_id !== 'content' ) {
			return;
		}

		echo $this->modal();
		printf( '<button id="idx-shortcode" class="button thickbox" data-editor="%s">Add IDX Shortcode</button>', esc_attr( $editor_id ) );
	}

	/**
	 * enqueue_shortcode_js function.
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */
	public function enqueue_shortcode_js( $hook ) {
		if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
			return;
		}

		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), '4.0.5', 'all'  );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array( 'jquery' ), '4.0.5', true );
		wp_enqueue_script( 'idx-shortcode', plugins_url( '../assets/js/idx-shortcode.min.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		wp_enqueue_style( 'idx-shortcode', plugins_url( '../assets/css/idx-shortcode.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'font-awesome-5.8.2', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css', array(), '5.8.2' );
		wp_enqueue_style( 'font-awesome-v4-shim', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/v4-shims.min.css', array(), 'fa-v4-shim' );
		// scripts and styles for map search widget preview
		wp_enqueue_script( 'custom-scriptLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/javascript/leaflet.js', array() );
		wp_enqueue_script( 'custom-scriptLeafDraw', '//d1qfrurkpai25r.cloudfront.net/graphical/frontend/javascript/maps/plugins/leaflet.draw.js', array( 'custom-scriptLeaf' ) );
		wp_enqueue_script( 'custom-scriptMQ', '//www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=Gmjtd%7Cluub2h0rn0%2Crx%3Do5-lz1nh', array( 'custom-scriptLeaf', 'custom-scriptLeafDraw' ) );
		wp_enqueue_style( 'cssLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet-1.000.css' );
		wp_enqueue_style( 'cssLeafLabel', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.label.css' );
	}

	/**
	 * modal function.
	 *
	 * @access public
	 * @return void
	 */
	public function modal() {
		echo '<div id="idx-shortcode-modal" style="display:none;"><div class="idx-modal-content">';
		echo '<button type="button" class="button-link media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>';
		$this->modal_overview();
		echo '</div></div>';
		echo '<div id="idx-overlay" style="display: none;"></div>';

	}

	/**
	 * modal_overview function.
	 *
	 * @access public
	 * @return void
	 */
	public function modal_overview() {
		echo '<h1>Insert IDX Shortcode</h1>';
		echo '<div class="separator"></div>';
		echo '<div class="idx-back-button"><a href="#">← Back to Overview</a></div>';
		echo '<div class="idx-modal-inner-content"><div class="idx-modal-tabs-router"><div class="idx-modal-tabs"><a class="idx-active-tab" href="#">Edit</a><a href="#">Preview</a></div></div>';
		echo '<div class="idx-modal-inner-overview">';

		$shortcodes = $this->shortcodes_for_ui->get_shortcodes_for_ui();
		foreach ( $shortcodes as $shortcode ) {
			echo '<div class="idx-shortcode-type" data-short-name="' . $shortcode['short_name'] . '">';
			echo '<div class="idx-shortcode-type-icon"><i class="' . $shortcode['icon'] . '"></i></div>';
			echo '<div class="idx-shortcode-name">' . $shortcode['name'] . '</div>';
			echo '</div>';
		}
		echo '</div><div class="idx-modal-shortcode-edit"></div><div class="idx-modal-shortcode-preview"></div>';
		echo '</div>';
		echo '<div class="idx-toolbar-primary"><div class="separator"></div><button class="button button-primary">Insert Shortcode</button></div>';
	}

	/**
	 * get_shortcodes_for_ui function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_shortcodes_for_ui() {
		$other_shortcodes = do_action( 'idx-register-shortcode-ui' );
		if ( empty( $other_shortcodes ) ) {
			$other_shortcodes = array();
		}
		return array_merge( $shortcodes_for_ui->default_shortcodes(), $other_shortcodes );

	}
}
