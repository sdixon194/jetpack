<?php
/**
 * REST API endpoint for the Jetpack AI blocks.
 *
 * @package automattic/jetpack
 * @since 11.8
 */

use Automattic\Jetpack\AI_Helper;

/**
 * Class WPCOM_REST_API_V2_Endpoint_AI
 */
class WPCOM_REST_API_V2_Endpoint_AI extends WP_REST_Controller {
	/**
	 * Namespace prefix.
	 *
	 * @var string
	 */
	public $namespace = 'wpcom/v2';

	/**
	 * Endpoint base route.
	 *
	 * @var string
	 */
	public $rest_base = 'jetpack-ai';

	/**
	 * WPCOM_REST_API_V2_Endpoint_AI constructor.
	 */
	public function __construct() {
		$this->is_wpcom                     = true;
		$this->wpcom_is_wpcom_only_endpoint = true;

		if ( ! AI_Helper::is_enabled() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/completions',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'request_gpt_completion' ),
					'permission_callback' => array( 'AI_Helper', 'get_status_permission_check' ),
				),
				'args' => array(
					'content' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'post_id' => array(
						'required' => false,
						'type'     => 'integer',
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/images/generations',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'request_dalle_generation' ),
					'permission_callback' => array( 'AI_Helper', 'get_status_permission_check' ),
				),
				'args' => array(
					'prompt'  => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'post_id' => array(
						'required' => false,
						'type'     => 'integer',
					),
				),
			)
		);
	}

	/**
	 * Get completions for a given text.
	 *
	 * @param  WP_REST_Request $request The request.
	 */
	public function request_gpt_completion( $request ) {
		return AI_Helper::get_gpt_completion( $request['content'], $request['post_id'] );
	}

	/**
	 * Get image generations for a given prompt.
	 *
	 * @param  WP_REST_Request $request The request.
	 */
	public function request_dalle_generation( $request ) {
		return AI_Helper::get_dalle_generation( $request['prompt'], $request['post_id'] );
	}
}

wpcom_rest_api_v2_load_plugin( 'WPCOM_REST_API_V2_Endpoint_AI' );
