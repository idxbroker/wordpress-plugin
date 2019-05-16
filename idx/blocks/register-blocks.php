<?php
namespace IDX\Blocks;

/**
 * Register_Blocks class.
 */
class Register_Blocks {

	/**
	 * Idx_api
	 *
	 * @var mixed
	 * @access public
	 */
	public $idx_api;

	/**
	 * Lead_login_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $lead_login_shortcode;

	/**
	 * Lead_signup_shortcode
	 *
	 * @var mixed
	 * @access public
	 */
	public $lead_signup_shortcode;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->idx_api = new \IDX\Idx_Api();
		if ( $this->idx_api->platinum_account_type() ) {
			$this->lead_signup_shortcode = new \IDX\Shortcodes\Impress_Lead_Signup_Shortcode();
			add_action( 'init', [ $this, 'impress_lead_signup_block_init' ] );
		}
		$this->lead_login_shortcode = new \IDX\Shortcodes\Impress_Lead_Login_Shortcode();
		add_action( 'init', [ $this, 'impress_lead_login_block_init' ] );
	}

	/**
	 * Impress_lead_signup_block_init function.
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_signup_block_init() {
		// Register block script.
		wp_register_script(
			'impress-lead-signup-block',
			plugins_url( '/impress-lead-signup/script.js', __FILE__ ),
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ],
			'1.0',
			false
		);
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-lead-signup-block',
			[
				'attributes' => [
					'phone' => [
						'type' => 'int',
					],
					'styles' => [
						'type' => 'int',
					],
					'new_window' => [
						'type' => 'int',
					],
					'agent_id' => [
						'type' => 'string',
					],
					'password_field' => [
						'type' => 'bool',
					],
					'button_text' => [
						'type' => 'string',
					],
				],
				'editor_script'   => 'impress-lead-signup-block',
				'render_callback' => [ $this, 'impress_lead_signup_block_render' ],
			]
		);

		$available_agents = [ 'agents_list' => $this->lead_signup_shortcode->get_agents_select_list() ];
		wp_localize_script( 'impress-lead-signup-block', 'lead_signup_agent_list', $available_agents );
		wp_enqueue_script( 'impress-lead-signup-block' );

	}

	/**
	 * Impress_lead_signup_block_render function.
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_lead_signup_block_render( $attributes ) {
		return $this->lead_signup_shortcode->shortcode_output( $attributes );
	}

	/**
	 * Impress_lead_login_block_init function.
	 *
	 * @access public
	 * @return void
	 */
	public function impress_lead_login_block_init() {
		// Register block script.
		wp_register_script(
			'impress-lead-login-block',
			plugins_url( '/impress-lead-login/script.js', __FILE__ ),
			[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ],
			'1.0',
			false
		);
		// Register block and attributes.
		register_block_type(
			'idx-broker-platinum/impress-lead-login-block',
			[
				'attributes' => [
					'styles' => [
						'type' => 'int',
					],
					'new_window' => [
						'type' => 'int',
					],
					'password_field' => [
						'type' => 'bool',
					],
				],
				'editor_script'   => 'impress-lead-login-block',
				'render_callback' => [ $this, 'impress_lead_login_block_render' ],
			]
		);
	}

	/**
	 * Impress_lead_login_block_render function.
	 *
	 * @access public
	 * @param mixed $attributes - Widget attributes.
	 * @return string
	 */
	public function impress_lead_login_block_render( $attributes ) {
		return $this->lead_login_shortcode->lead_login_shortcode( $attributes );
	}
}
