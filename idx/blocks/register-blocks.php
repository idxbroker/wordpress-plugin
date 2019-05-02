<?php
namespace IDX\Blocks;

/**
 * Register_Blocks class.
 */
class Register_Blocks {

	public $lead_login_shortcode;
	public $lead_signup_shortcode;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->lead_login_shortcode = new \IDX\Shortcodes\Impress_Lead_Login_Shortcode();
		$this->lead_signup_shortcode = new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode();

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
		register_block_type(
			'idx-broker-platinum/impress-lead-signup-block', 
			array(
				'attributes' => array(
					'phone' => array(
						'type' => 'int',
					),
					'styles' => array(
						'type' => 'int',
					),
					'new_window' => array(
						'type' => 'int',
					),
					'agent_id' => array(
						'type' => 'string',
					),
					'password_field' => array(
						'type' => 'string',
					),
					'button_text' => array(
						'type' => 'string',
					),
				),
				'editor_script'   => 'impress-lead-signup-block', // The script name we gave in the wp_register_script() call.
				'render_callback' => array( $this, 'impress_lead_signup_block_render' ),
			)
		);

	}

	function impress_lead_signup_block_render( $attributes ) {
		return $this->lead_signup_shortcode->shortcode_output( $attributes );
	}

	function impress_lead_login_block_init() {
		// Register our block editor script.
		wp_register_script(
			'impress-lead-login-block',
			plugins_url( '/impress-lead-login/script.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
		);
		// Register our block, and explicitly define the attributes we accept.
		register_block_type(
			'idx-broker-platinum/impress-lead-login-block',
			array(
				'attributes'      => array(
					'styles' => array(
						'type' => 'int',
					),
					'new_window' => array(
						'type' => 'int',
					),
					'password_field' => array(
						'type' => 'bool',
					),
				),
				'editor_script'   => 'impress-lead-login-block', // The script name we gave in the wp_register_script() call.
				'render_callback' => array( $this, 'impress_lead_login_block_render' ),
			) 
		);
	}

	function impress_lead_login_block_render( $attributes ) {
		return $this->lead_login_shortcode->lead_login_shortcode( $attributes );
	}
}
