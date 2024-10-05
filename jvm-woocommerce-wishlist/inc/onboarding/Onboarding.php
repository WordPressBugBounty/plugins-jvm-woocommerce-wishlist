<?php //phpcs:ignore WordPress.NamingConventions

namespace CIXW_WISHLIST\My_Feature;

/**
 * Boilerplate plugin for The Web Solver WordPress Admin Onboarding Wizard.
 */
final class Onboarding {
	/**
	 * Onboarding wizard prefix.
	 *
	 * @var string
	 * @todo Prefix for onboarding wizard. DO NOT CHANGE IT ONCE SET.\
	 *       It will be used for WordPress Hooks, Options, Transients, etc.\
	 *       MUST BE A UNIQUE PREFIX FOR YOUR PLUGIN.
	 */
	public $prefix = 'cixww_onboarding';

	/**
	 * Onboarding Wizard Config.
	 *
	 * @var Config
	 */
	public $config;

	/**
	 * Starts Onboarding.
	 *
	 * @return Onboarding
	 */
	public static function start() {
		static $onboarding;
		if ( ! is_a( $onboarding, self::class) ) {
			$onboarding = new self();
		}
		return $onboarding;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		$this->config();
		register_activation_hook( CIXWW_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( CIXWW_PLUGIN_FILE, array( $this, 'deactivate' ) );

		/**
		 * If all onboarding steps are not completed, show admin notice.
		 *
		 * At last step of onboarding, $status => 'complete'.
		 *
		 * @var string
		 *
		 * @todo Need to perform additional checks before showing notice
		 *       Such as show notice on plugins, themes and dashboard pages only.
		 */
		$status = get_option( $this->prefix . '_onboarding_steps_status' );
		if ( 'pending' === $status ) {
			add_action( 'admin_notices', array( $this, 'onboarding_notice' ) );
		}
	}

	/**
	 * Instantiates onboarding config.
	 */
	private function config() {
		// Onboarding config file path.
		include_once __DIR__ . '/Config.php';
		$config = array( '\\' . __NAMESPACE__ . '\\Config', 'get' );

		// Only call config if it is on the same namespace.
		if ( is_callable( $config ) ) {
			$this->config = call_user_func( $config, $this->prefix );
		}
	}

	/**
	 * Sets onboarding notice if not completed.
	 */
	public function onboarding_notice() {
		if ( ! \PAnD::is_admin_notice_active( 'notice-one-forever' ) ) {
			return;
		}

		$msg = sprintf(
			'<p><strong>%1$s</strong>%2$s</p><p><a href="%3$s" class="button-primary">%4$s</a></p>',
			__( 'It looks like the WooCommerce Wishlist setup is pending!', 'tws-onboarding' ),
			__( '<br>No worries, let\'s run the setup wizard together to quickly configure the basic settings. This will ensure that everything runs smoothly and you get the most out of the application.', 'tws-onboarding' ),
			admin_url( 'admin.php?page=' . $this->config->get_page() ),
			__( 'Run the Wizard Now', 'tws-onboarding' )
		);

		echo '<div data-dismissible="cixww-onboarding-notice-forever" class="updated notice notice-success">' . wp_kses_post( $msg ) . '</div>';
	}


	/**
	 * Performs task on plugin activation.
	 *
	 * @todo Configured with example codes. Make changes as needed.
	 */
	public function activate() {

		// Check if plugin is already installed.
		$old_install = get_option( $this->prefix . '_install_version', false );

		if ( ! $old_install ) {
			// if new install => enable onboarding.
			$check[] = 'true';

			// Set the plugin install version to "1.0".
			update_option( $this->prefix . '_install_version', CIXWW_PLUGIN_VER );
		} else {
			// There is now installed version "1.0" => disable onboarding.
			$check[] = 'false';
		}

		// If PHP version less than or equal to "7.2" => disable onboarding.
		if ( version_compare( phpversion(), '7.2', '<=' ) ) {
			$check[] = 'false';
		}

		// Now onboarding will run on the basis of check parameter passed.
		// If this is first activation or PHP > 7.0 => redirect to onboarding page.
		// Lets also verify if config has been instantiated.
		if ( is_object( $this->config ) ) {
			$this->config->enable_onboarding( $check );
		}
	}

	/**
	 * Performs task on plugin deactivation.
	 *
	 * @todo Configured to delete onboarding options on plugin deactivation.\
	 *       Cane be safely deleted for production.
	 */
	public function deactivate() {
		// Onboarding options.
		// delete_option( $this->prefix . '_onboarding_steps_status' );
		delete_option( $this->prefix . '_onboarding_dependency_status' );
		delete_option( $this->prefix . '_onboarding_dependency_name' );
		// delete_option( $this->prefix . '_install_version' );

		// Onboarding transitents.
		// delete_transient( $this->prefix . '_onboarding_redirect' );
	}
}
Onboarding::start();
