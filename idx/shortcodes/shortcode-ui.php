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
	 * Shortcodes_for_ui.
	 *
	 * @var mixed
	 * @access public
	 */
	public $shortcodes_for_ui;

	/**
	 * Add_idx_media_button function.
	 *
	 * @access public
	 * @param mixed $editor_id - Editor ID.
	 * @return void
	 */
	public function add_idx_media_button( $editor_id ) {
		if ( 'content' !== $editor_id ) {
			return;
		}

		$this->modal();
		printf( '<button id="idx-shortcode" class="button thickbox" data-editor="%s">Add IDX Shortcode</button>', esc_attr( $editor_id ) );
	}

	/**
	 * Enqueue_shortcode_js function.
	 *
	 * @access public
	 * @param mixed $hook - Hook.
	 * @return void
	 */
	public function enqueue_shortcode_js( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'idx-shortcode', plugins_url( '../assets/js/idx-shortcode.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', false );
		wp_enqueue_style( 'idx-shortcode', plugins_url( '../assets/css/idx-shortcode.min.css', dirname( __FILE__ ) ), [], '1.0' );
		wp_enqueue_style( 'font-awesome-5.8.2' );
		// scripts and styles for map search widget preview.
		wp_enqueue_script( 'custom-scriptLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/javascript/leaflet.js', array(), '1.0', false );
		wp_enqueue_script( 'custom-scriptLeafDraw', '//d1qfrurkpai25r.cloudfront.net/graphical/frontend/javascript/maps/plugins/leaflet.draw.js', array( 'custom-scriptLeaf' ), '1.0', false );
		wp_enqueue_style( 'cssLeaf', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet-1.000.css', [], '1.0' );
		wp_enqueue_style( 'cssLeafLabel', '//d1qfrurkpai25r.cloudfront.net/graphical/css/leaflet.label.css', [], '1.0' );
	}

	/**
	 * Modal function.
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
	 * Modal_overview function.
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
			echo '<div class="idx-shortcode-type" data-short-name="' . esc_attr( $shortcode['short_name'] ) . '">';
			echo '<div class="idx-shortcode-type-icon"><i class="' . esc_attr( $shortcode['icon'] ) . '"></i></div>';
			echo '<div class="idx-shortcode-name">' . esc_html( $shortcode['name'] ) . '</div>';
			echo '</div>';
		}
		echo '</div><div class="idx-modal-shortcode-edit"></div><div class="idx-modal-shortcode-preview"></div>';
		echo '</div>';
		echo '<div class="idx-toolbar-primary"><div class="separator"></div><button class="button button-primary">Insert Shortcode</button></div>';
	}

	/**
	 * Get_shortcodes_for_ui function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_shortcodes_for_ui() {
		$other_shortcodes = do_action( 'idx-register-shortcode-ui' );
		if ( empty( $other_shortcodes ) ) {
			$other_shortcodes = array();
		}
		return array_merge( $shortcodes_for_ui->default_shortcodes(), $other_shortcodes );

	}
}
