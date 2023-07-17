<?php
/**
 * Primary class file for the Jetpack Chat plugin.
 *
 * @package automattic/jetpack-chat-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\Jetpack\Admin_UI\Admin_Menu;
use Automattic\Jetpack\Assets;
use Automattic\Jetpack\Connection\Initial_State as Connection_Initial_State;
use Automattic\Jetpack\Connection\Manager as Connection_Manager;
use Automattic\Jetpack\Connection\Rest_Authentication as Connection_Rest_Authentication;
use Automattic\Jetpack\My_Jetpack\Initializer as My_Jetpack_Initializer;
use Automattic\Jetpack\Sync\Data_Settings;

/**
 * Class Jetpack_Chat
 */
class Jetpack_Chat {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set up the REST authentication hooks.
		Connection_Rest_Authentication::init();

		$page_suffix = Admin_Menu::add_menu(
			__( 'Jetpack Chat', 'jetpack-chat' ),
			_x( 'Chat', 'The Jetpack Chat product name, without the Jetpack prefix', 'jetpack-chat' ),
			'manage_options',
			'jetpack-chat',
			array( $this, 'plugin_settings_page' ),
			99
		);
		add_action( 'load-' . $page_suffix, array( $this, 'admin_init' ) );

		// Init Jetpack packages
		add_action(
			'plugins_loaded',
			function () {
				$config = new Automattic\Jetpack\Config();
				// Connection package.
				$config->ensure(
					'connection',
					array(
						'slug'     => JETPACK_CHAT_SLUG,
						'name'     => JETPACK_CHAT_NAME,
						'url_info' => JETPACK_CHAT_URI,
					)
				);
				// Sync package.
				$config->ensure( 'sync', Data_Settings::MUST_SYNC_DATA_SETTINGS );

				// Identity crisis package.
				$config->ensure( 'identity_crisis' );
			},
			1
		);

		My_Jetpack_Initializer::init();

		// Enqueue XMPP scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_xmpp_scripts' ) );
	}

	/**
	 * Initialize the admin resources.
	 */
	public function admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	public function enqueue_xmpp_scripts() {
		// XMPP stuff
		Assets::register_script(
			'xmpp-js',
			'build/xmpp.js',
			JETPACK_CHAT_ROOT_FILE,
			array(
				'in_footer'  => true,
				'textdomain' => 'xmpp-js',
			)
		);
		Assets::enqueue_script( 'xmpp-js' );
	}

	/**
	 * Enqueue plugin admin scripts and styles.
	 */
	public function enqueue_admin_scripts() {

		Assets::register_script(
			'jetpack-chat',
			'build/index.js',
			JETPACK_CHAT_ROOT_FILE,
			array(
				'in_footer'  => true,
				'textdomain' => 'jetpack-chat',
			)
		);
		Assets::enqueue_script( 'jetpack-chat' );
		// Initial JS state including JP Connection data.
		wp_add_inline_script( 'jetpack-chat', Connection_Initial_State::render(), 'before' );
		wp_add_inline_script( 'jetpack-chat', $this->render_initial_state(), 'before' );
	}

	/**
	 * Render the initial state into a JavaScript variable.
	 *
	 * @return string
	 */
	public function render_initial_state() {
		return 'var jetpackChatInitialState=JSON.parse(decodeURIComponent("' . rawurlencode( wp_json_encode( $this->initial_state() ) ) . '"));';
	}

	/**
	 * Get the initial state data for hydrating the React UI.
	 *
	 * @return array
	 */
	public function initial_state() {
		return array(
			'apiRoot'           => esc_url_raw( rest_url() ),
			'apiNonce'          => wp_create_nonce( 'wp_rest' ),
			'registrationNonce' => wp_create_nonce( 'jetpack-registration-nonce' ),
		);
	}

	/**
	 * Main plugin settings page.
	 */
	public function plugin_settings_page() {
		?>
			<div id="jetpack-chat-root"></div>
		<?php
	}

	/**
	 * Removes plugin from the connection manager
	 * If it's the last plugin using the connection, the site will be disconnected.
	 *
	 * @access public
	 * @static
	 */
	public static function plugin_deactivation() {
		$manager = new Connection_Manager( 'jetpack-chat' );
		$manager->remove_connection();
	}
}
