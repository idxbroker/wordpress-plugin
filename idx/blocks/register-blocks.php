<?php
namespace IDX\Blocks;

/**
 * Register_Blocks class.
 */
class Register_Blocks {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'impress_lead_signup_block_init') );
		add_action( 'init', array( $this, 'impress_lead_login_block_init') );
	}

	function impress_lead_signup_block_init() {
		// Register our block editor script.
		wp_register_script(
			'impress-lead-signup-block',
			plugins_url( '/impress-lead-signup/script.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
		);
		// Register our block, and explicitly define the attributes we accept.
		register_block_type( 'idx-broker-platinum/impress-lead-signup-block', array(
			'attributes'      => array(
				'foo' => array(
					'type' => 'string',
				),
			),
			'editor_script'   => 'impress-lead-signup-block', // The script name we gave in the wp_register_script() call.
			'render_callback' => array($this, 'impress_lead_signup_block_render'),
		) );
		// Define our shortcode, too, using the same render function as the block.
		//add_shortcode( 'php_block', array($this, 'impress_lead_signup_block_render') );
	}

	function impress_lead_signup_block_render( $attributes ) {
		return (new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode())->shortcode_output($attributes);//'<p>Gheedora</p>';
	}

	function impress_lead_login_block_init() {
		// Register our block editor script.
		wp_register_script(
			'impress-lead-login-block',
			plugins_url( '/impress-lead-login/script.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
		);
		// Register our block, and explicitly define the attributes we accept.
		register_block_type( 'idx-broker-platinum/impress-lead-login-block', array(
			'attributes'      => array(
				'foo' => array(
					'type' => 'string',
				),
			),
			'editor_script'   => 'impress-lead-login-block', // The script name we gave in the wp_register_script() call.
			'render_callback' => array($this, 'impress_lead_login_block_render'),
		) );
		// Define our shortcode, too, using the same render function as the block.
		//add_shortcode( 'php_block', array($this, 'impress_lead_signup_block_render') );
	}

	function impress_lead_login_block_render( $attributes ) {
		return (new \IDX\Shortcodes\Impress_Lead_Login_Shortcode())->lead_login_shortcode($attributes);//'<p>Gheedora</p>';
	}
}