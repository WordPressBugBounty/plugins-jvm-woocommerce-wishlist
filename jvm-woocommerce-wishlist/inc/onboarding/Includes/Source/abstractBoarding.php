<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Abstract Class.
 * Handles installation of dependency plugin at introduction page.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Abstract
 *
 * -----------------------------------
 * DEVELOPED-MAINTAINED-SUPPPORTED BY
 * -----------------------------------
 * ███║     ███╗   ████████████████
 * ███║     ███║   ═════════██████╗
 * ███║     ███║        ╔══█████═╝
 *  ████████████║      ╚═█████
 * ███║═════███║      █████╗
 * ███║     ███║    █████═╝
 * ███║     ███║   ████████████████╗
 * ╚═╝      ╚═╝    ═══════════════╝
 */

namespace TheWebSolver\Core\Admin\Onboarding;

use TheWebSolver;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\\Wizard' ) ) {
	/**
	 * Setup wizard class.
	 *
	 * Handles installation of dependency plugin at introduction page.
	 *
	 * @class TheWebSolver\Core\Admin\Onboarding\Wizard
	 */
	abstract class Wizard {
		/**
		 * Onboarding prefixer.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $prefix;

		/**
		 * The current onboarding wizard configuration instance.
		 *
		 * @var object
		 *
		 * @since 1.1
		 */
		protected $config;

		/**
		 * HTML head title.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $title;
		protected $intro_desc;
		protected $intro_title;

		/**
		 * All steps.
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		protected $steps = array();

		/**
		 * Current step.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $step = '';

		/**
		 * Actions to be executed after the HTTP response has completed.
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		private $deferred_actions = array();

		/**
		 * Dependency plugin already installed or not.
		 *
		 * @var bool True to ignore dependency.
		 *
		 * @since 1.0
		 */
		protected $is_installed = true;

		/**
		 * Dependency plugin already active or not.
		 *
		 * @var bool True to ignore dependency.
		 *
		 * @since 1.0
		 */
		protected $is_active = true;

		/**
		 * Dependency plugin slug/directory name.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $slug = '';

		/**
		 * Dependency plugin name.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		private $name = '';

		/**
		 * Dependency plugin filename.
		 *
		 * No need to set it if same as slug { @see @property Wizard::$slug }.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $filename = '';

		/**
		 * Dependency plugin version to install.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $version = 'latest';

		/**
		 * The user capability who can onboard.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		public $capability = 'manage_options';

		/**
		 * Recommended plugins.
		 *
		 * Recommended step is where the recommended
		 * plugins will be installed and activated.
		 *
		 * @var array
		 *
		 * @since 1.0
		 */
		protected $recommended = array();

		/**
		 * The registered page hook suffix.
		 *
		 * @var string|false
		 *
		 * @since 1.0
		 */
		public $hook_suffix;

		/**
		 * Plugin Logo.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		protected $logo;

		/**
		 * Reset onboarding wizard options.
		 *
		 * All keys for options reset are:
		 * * *dependency_name*
		 * * *dependency_status*
		 * * *recommended_status*
		 * * *recommended_checked_status*
		 *
		 * @var bool[]
		 *
		 * @since 1.0
		 */
		protected $reset = array();

		/**
		 * Onboarding constructor.
		 *
		 * Everything happens at `init`. Always call it after initializing class!!!
		 *
		 * @see {@method `Wizard::init()`}
		 * @since 1.0
		 */
		public function __construct() {
			// See init().
		}

		/**
		 * Sets current onboarding wizard configuration instance.
		 *
		 * @param object $instance The current config instance in given namespace.
		 *
		 * @since 1.1
		 */
		public function set_config( $instance ) {
			$this->config = $instance;
		}

		/**
		 * Sets dependency plugin args.
		 *
		 * If any plugin is required i.e. if this plugin is dependent on any other plugin,
		 * or this plugin is like an add-on/extension of the dependent plugin,
		 * Then it can be installed from onboarding first step (introduction).
		 * Pass the required `param` and everthing will be handled automatically.
		 *
		 * The dependency plugin status will be saved with Options API
		 * with key {`$this->prefix . '_onboarding_dependency_status'`}.
		 * So it is not possible to change it once set after plugin activation.
		 * To change dependency `param`, you need to reset key by:
		 * * deleting above option key with `delete_option()` at plugin deactivation/uninstall hook & deactivating/reinstalling plugin.
		 * * setting `$this->reset['dependency_name'] = true` & `$this->reset['dependency_status'] = true` and visiting `ready` step. To know more: {@see @method `Wizard::reset()`}.
		 * * manually deleting above key by any other means (maybe directly from database).
		 *
		 * Following properties are to be set in this method.
		 * * @property Wizard::$slug     - The plugin's slug on WordPress repository (aka directory name).
		 * * @property Wizard::$filename - The plugin's main file name. Only needed if different than `$slug`.
		 *                                 Don't include the extension `.php`.
		 * * @property Wizard::$version  - The plugin's version to install. Useful if PHP and/or WordPress
		 *                                 not compatible with plugin's latest version. Defaults to `latest`.
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * namespace My_Plugin\My_Feature;
		 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
		 *
		 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
		 * class Onboarding_Wizard extends Wizard {
		 *  protected function set_dependency() {
		 *   $this->slug     = 'woocommerce';
		 *   $this->filename = 'woocommerce'; // Not needed as it is same as slug. Included as an example.
		 *   $this->version  = '4.5.0'; // Not needed if latest to install. Can be: '4.5.0', '4.0.0' etc.
		 *  }
		 * }
		 * ```
		 */
		protected function set_dependency() {}

		/**
		 * Sets onboarding HTML head title.
		 *
		 * Override this to set own head title.
		 *
		 * @since 1.0
		 */
		protected function set_title() {
			$this->title = __( 'Wishlist for WooCoomerce &rsaquo; Onboarding', 'tws-onboarding' );
		}

		/**
		 * Sets onboarding logo.
		 *
		 * Set logo args in an array as below:
		 * * `string` `href` The logo destination URL.
		 * * `string` `alt` The logo alt text.
		 * * `string` `width` The logo width.
		 * * `string` `height` The logo height.
		 * * `string` `src` The logo image source.
		 *
		 * @since 1.0
		 */
		abstract protected function set_logo();

		/**
		 * Sets onboarding steps.
		 *
		 * `introduction`, `recommended` and `ready` steps are created by default.
		 * So, all steps display order will be:
		 * * _Introduction/First step_
		 * * _All other steps added by this method_
		 * * _Recommended step_
		 * * _Ready/Final step_
		 *
		 * Each step should have ***key*** as step id and ***value*** with below args:
		 * * @type `string`   `name` `required` The step name/title.
		 * * @type `string`   `desc` `optional` The step description. Will be shown below name.
		 * * @type `callable` `view` `required` The callable function/method to display step contents.
		 * * @type `callable` `save` `required` The callable function/method to save step contents.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		abstract protected function set_steps();

		/**
		 * Sets the recommended plugins.
		 *
		 * The plugins data in an array.
		 * * `string` `slug`  - The plugin slug (dirname).
		 * * `string` `file`  - The plugin's main file name (excluding `.php`)
		 * * `string` `title` - The plugin title/name.
		 * * `string` `desc`  - The plugin description.
		 * * `string` `logo`  - The plugin logo URL.
		 * * `string` `alt`   - The plugin logo alt text.
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * namespace My_Plugin\My_Feature;
		 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
		 *
		 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
		 * class Onboarding_Wizard extends Wizard {
		 *  protected function set_recommended_plugins() {
		 *   $this->recommended = array(
		 *    array(
		 *     'slug'  => 'show-hooks',
		 *     'file'  => 'show-hooks',
		 *     'title' => __( 'Show Hooks', 'tws-onboarding' ),
		 *     'desc'  => __( 'A sequential and visual representation of WordPess action and filter hooks.', 'tws-onboarding' ),
		 *     'logo'  => 'https://ps.w.org/show-hooks/assets/icon-256x256.png?rev=2327503',
		 *     'alt'   => __( 'Show Hooks Logo', 'tws-onboarding' ),
		 *    ),
		 *   // Another recommended plugin array args here.
		 *   );
		 *  }
		 * }
		 * ```
		 */
		protected function set_recommended_plugins() {}

		/**
		 * Resets (deletes) options added during onboarding.
		 * ------------------------------------------------------------------------------
		 * It will not delete options that are saved on child-class onboarding steps.\
		 * It will only delete options saved for onboarding wizard purpose.
		 * ------------------------------------------------------------------------------
		 *
		 * By default, it is set to an empty array. i.e. onboarding options will not be deleted by default.\
		 * If `$this->reset` array values are passed as an exmaple below, then following options will be deleted.
		 * * ***$this->prefix . '_onboarding_dependency_status'***
		 * * ***$this->prefix . '_onboarding_dependency_name'***
		 * * ***$this->prefix . '_get_onboarding_recommended_plugins_status'***
		 * * ***$this->prefix . '_get_onboarding_recommended_plugins_checked_status'***.
		 *
		 * @since 1.0
		 * @example usage
		 * ```
		 * namespace My_Plugin\My_Feature;
		 * use TheWebSolver\Core\Admin\Onboarding\Wizard;
		 *
		 * // Lets assume our child-class is `Onboarding_Wizard` in above namespace.
		 * class Onboarding_Wizard extends Wizard {
		 *  protected function reset() {
		 *   // Lets keep some options and delete some options. Just pass true/false for following.
		 *   // true will delete option, false will not.
		 *   $this->reset = array(
		 *    'dependency_name'            => true,
		 *    'dependency_status'          => true,
		 *    'recommended_status'         => false,
		 *    'recommended_checked_status' => true,
		 *   );
		 *  }
		 * }
		 * ```
		 */
		protected function reset() {}

		/**
		 * Initialize onboarding wizard.
		 *
		 * Always call this method after instantiating child class.
		 * It will call all abstract methods and set respective properties.
		 *
		 * @since 1.0
		 */
		public function init() {
			$this->prefix = $this->config->get_prefix();

			$this->set_title();
			$this->set_logo();
			$this->set_dependency();
			$this->set_recommended_plugins();

			if ( 0 < strlen( $this->slug ) ) {
				// Get dependency plugin status.
				$filename           = '' !== $this->filename ? $this->filename : $this->slug;
				$basename           = $this->slug . '/' . $filename . '.php';
				$this->is_installed = TheWebSolver::maybe_plugin_is_installed( $basename );
				$this->is_active    = TheWebSolver::maybe_plugin_is_active( $basename );
			}

			// Exclude dependency plugin from recommended, if included. Not a good idea to include same in both.
			$filtered          = array_filter( $this->recommended, array( $this, 'exclude_dependency_from_recommended' ) );
			$this->recommended = $filtered;

			// Prepare admin user to have the given capability.
			add_filter( 'user_has_cap', array( $this, 'add_user_capability' ) );

			// Bail if user has no permission.
			if ( ! current_user_can( $this->config->get_capability() ) ) {
				return;
			}

			// Run admin hooks after user capability has been set.
			add_action( 'admin_menu', array( $this, 'add_page' ) );
			add_action( 'admin_init', array( $this, 'start' ), 99 );

			// Run dependency plugin installation via Ajax.
			add_action( "wp_ajax_{$this->prefix}_silent_plugin_install", array( $this, 'install_dependency' ) );
		}

		/**
		 * Prepares dependency plugin data.
		 *
		 * @since 1.0
		 */
		protected function prepare_dependency() {
			// Get dependency plugin status.
			$filename           = '' !== $this->filename ? $this->filename : $this->slug;
			$basename           = $this->slug . '/' . $filename . '.php';
			$this->is_installed = TheWebSolver::maybe_plugin_is_installed( $basename );
			$this->is_active    = TheWebSolver::maybe_plugin_is_active( $basename );

			// Set dependency plugin's name and status on clean install.
			// If $this->reset['dependency_status] => true in last step, then status => false.
			if ( false === get_option( $this->prefix . '_onboarding_dependency_status' ) ) {
				if ( $this->is_installed ) {
					// Get plugin info from plugin data.
					$info = TheWebSolver::get_plugin_data( $this->slug . '/' . $this->filename . '.php' );

					if ( is_array( $info ) && ! empty( $info ) ) {
						$name = isset( $info['Name'] ) ? $info['Name'] : '';
						update_option( $this->prefix . '_onboarding_dependency_name', $name );
					}
					update_option( $this->prefix . '_onboarding_dependency_status', 'installed' );
				} else {
					$name = TheWebSolver::get_plugin_info_from_wp_api( $this->slug, 'name' );
					update_option( $this->prefix . '_onboarding_dependency_name', $name );

					$info = TheWebSolver::get_plugin_info_from_wp_api( $this->slug, 'version', $this->version );

					/**
					 * WPHOOK: Filter -> Info about the dependency plugin.
					 *
					 * @param mixed  $info   The dependency plugin info.
					 * @param string $prefix The onboarding prefix.
					 * @param string $slug   The dependency plugin slug.
					 * @var mixed
					 * @since 1.0
					 */
					$info = apply_filters( 'hzfex_onboarding_dependency_plugin_info', $info, $this->prefix, $this->slug );

					// If latest version of dependency plugin not compatible with WP or PHP, $info => WP_Error.
					$status = is_wp_error( $info ) ? $info->get_error_message() : 'pending';

					update_option( $this->prefix . '_onboarding_dependency_status', $status );
				}
			}

			$this->name = get_option( $this->prefix . '_onboarding_dependency_name', '' );
		}

		/**
		 * Filters out dependency plugin from recommended plugin.
		 *
		 * This is to prevent showing dependency plugin in list of recommended plugins too.
		 *
		 * @param array $plugin The current recommended plugin.
		 *
		 * @since 1.0
		 */
		public function exclude_dependency_from_recommended( $plugin ) {
			return isset( $plugin['slug'] ) && $plugin['slug'] !== $this->slug;
		}

		/**
		 * Gives admin the capability needed.
		 *
		 * @param array $capabilities The current user capabilities.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function add_user_capability( $capabilities ) {
			// Bail early if given cap is of admin.
			if ( 'manage_options' === $this->config->get_capability() ) {
				return $capabilities;
			}

			if (
				! empty( $capabilities['manage_options'] ) &&
				( ! isset( $capabilities[ $this->config->get_capability() ] ) || true !== $capabilities[ $this->config->get_capability() ] )
				) {
				$capabilities[ $this->config->get_capability() ] = true;
			}

			return $capabilities;
		}

		/**
		 * Add menu for plugin onboarding.
		 *
		 * @since 1.0
		 */
		public function add_page() {
			$this->hook_suffix = add_dashboard_page( '', '', $this->config->get_capability(), $this->config->get_page(), '' );
		}

		/**
		 * Starts onboarding.
		 *
		 * @since 1.0
		 * @since 1.1 Set step ID for the form handler.
		 */
		public function start() {
			// Bail early if not on setup page.
			if ( ! isset( $_GET['page'] ) || $this->config->get_page() !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			if ( 0 < strlen( $this->slug ) ) {
				$this->prepare_dependency();
			}

			$this->get_all_steps();

			// Remove recommended step if no data or user has no permission.
			if ( 0 === count( $this->recommended ) || ! current_user_can( 'install_plugins' ) ) {
				unset( $this->steps['recommended'] );
			}

			// Get current step from all steps added to query arg.
			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Prepare form for this step.
			$this->config->form->set_step( $this->step );

			$this->register_scripts();

			// Save data of current step set with callback function on "save" key of that step.
			if ( isset( $_POST['save_step'] ) && 'save_step' === $_POST['save_step'] && isset( $this->steps[ $this->step ]['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				call_user_func_array( $this->steps[ $this->step ]['save'], array( $this ) );
			}

			ob_start();
			$this->set_step_header();
			$this->set_step_progress();
			$this->set_step_content();
			$this->set_step_footer();
			exit;
		}

		/**
		 * Gets all steps of onboarding wizard.
		 *
		 * @since 1.0
		 */
		protected function get_all_steps() {
			// Let's set the default intro page.
			$step['introduction'] = array(
				'name'  => __( 'Introduction', 'tws-onboarding' ),
				'image' => array( $this, 'introduction_image' ),
				'view'  => array( $this, 'introduction' ),
			);

			$all_steps = array_merge( $step, $this->set_steps() );

			// And this too.
			$all_steps['recommended'] = array(
				'name' => __( 'Recommended', 'tws-onboarding' ),
				'view' => array( $this, 'recommended_view' ),
				'desc' => __( 'Downlonad, install & activate recommended plugins.', 'tws-onboarding' ),
				'save' => array( $this, 'recommended_save' ),
			);

			// And this final step.
			$all_steps['ready'] = array(
				'name' => __( 'Ready!', 'tws-onboarding' ),
				'desc' => __( 'Everything is set. Let\'s start.', 'tws-onboarding' ),
				'view' => array( $this, 'final_step' ),
				'save' => '',
			);

			/**
			 * WPHOOK: Filter -> Plugin onboarding steps.
			 *
			 * Useful to change step name, desc, set step hero image.
			 *
			 * @param array  $steps  Onboarding steps.
			 * @param string $prefix The onboarding prefix.
			 * @var array
			 * @since 1.0
			 */
			$this->steps = apply_filters( 'hzfex_set_onboarding_steps', $all_steps, $this->prefix );
		}

		/**
		 * Sets steps header HTML.
		 *
		 * @since 1.0
		 */
		protected function set_step_header() {
			set_current_screen( $this->hook_suffix );
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width" />
				<title><?php echo esc_html( $this->title ); ?></title>
				<?php
				wp_print_scripts( 'onboarding_script' );
				wp_print_styles( 'onboarding_style' );
				

				/**
				 * WPHOOK: Filter -> Body classes.
				 *
				 * @param string[] $classes Additional body classes in an array.
				 * @param string   $prefix  The Onboarding prefix.
				 * @var string[]
				 * @since 1.0
				 */
				$classes = apply_filters( 'hzfex_onboarding_body_classes', array(), $this->prefix );
				$classes = ! empty( $classes ) ? implode( ' ', $classes ) : '';
				?>
			</head>
			<body class="onboarding admin-onboarding wp-core-ui <?php echo $this->is_installed ? ' tws-onboarding' : ' no-dependency tws-onboarding-no'; ?>-<?php echo esc_attr( $this->slug ); ?><?php echo esc_attr( $classes ); ?>">
			<?php
				$steps = array_keys( $this->steps );
				$first = array_shift( $steps );
			if ( $first === $this->step ) :
				?>
				<!-- onboarding_header -->
				<header style="display:none;" id="onboarding_header" class="hz_flx row center">
				<?php if ( $this->logo['src'] ) : ?>
					<h1>
						<a
							id="hz_onboarding_logo"
							href="<?php echo esc_url( $this->logo['href'] ); ?>"
						>
						
							<img
								src="<?php echo esc_url( $this->logo['src'] ); ?>"
								alt="<?php echo esc_attr( $this->logo['alt'] ); ?>"
								width="<?php echo esc_attr( $this->logo['width'] ); ?>"
								height="<?php echo esc_attr( $this->logo['height'] ); ?>"
							/>
						
						
						</a>
					</h1>
				<?php endif; ?>
				
					
				
				</header>
				<!-- #onboarding_header -->
				<?php endif; ?>
				<!-- main -->
				<main id="main">
			<?php
		}

		/**
		 * Sets steps progress HTML.
		 *
		 * @since 1.0
		 */
		protected function set_step_progress() {
			?>
			<!-- onboarding_steps -->
			<aside class="onboarding_steps">
				
				<div class="onboarding_steps_wrapper">

					

					<ol class="steps_wrapper hz_flx column">
						<?php
						$current_step = 0;
						foreach ( $this->steps as $key => $step ) :
							++$current_step;
							?>
							<li
							id="hz_oBstep__<?php echo esc_attr( $current_step ); ?>"
							class="step
								<?php
								if ( $key === $this->step ) :
									echo 'active';
								elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $key, array_keys( $this->steps ), true ) ) :
									echo 'done';
								else :
									echo 'next';
								endif;
								?>
								onboarding-step <?php echo esc_attr( $key ); ?>"
								>
							<span class="onboarding_step_counter"><?php echo esc_html( $current_step ); ?></span>
							<span class="onboarding_step_name only_desktop"><?php echo esc_html( $step['name'] ); ?></span>

							<?php if ( isset( $step['desc'] ) ) : ?>
								<span class="onboarding_step_desc only_desktop"><?php echo wp_kses_post( $step['desc'] ); ?></span>
							<?php endif; ?>
							</li>
						<?php endforeach; ?>
						
							<a href="<?php echo esc_url_raw( add_query_arg( 'onboarding', 'introduction', admin_url() ) ); ?>" class="button button-large hz_dyn_btn onboarding_dashboard_btn">← <?php esc_html_e( 'Return to Dashboard', 'tws-onboarding' ); ?></a>
						
					</ol>
		
				</div>
				
			</aside>
			<!-- .onboarding_steps -->
			<?php
		}

		/**
		 * Sets the current step content HTML.
		 *
		 * @since 1.0
		 */
		protected function set_step_content() {
			// Redirect to admin with added query arg if no views in the step.
			if ( empty( $this->steps[ $this->step ]['view'] ) ) {
				wp_safe_redirect( esc_url_raw( add_query_arg( 'step', 'introduction' ) ) );
				exit;
			}
			?>
			<!-- onboarding_content -->
			<section class="onboarding_content content_step__<?php echo esc_attr( $this->step ); ?>">
				<div class="onboarding_step_image">
				<?php
				if ( isset( $this->steps[ $this->step ]['image'] ) ) :
					call_user_func( $this->steps[ $this->step ]['image'] );
				endif;
				?>
				</div>
				<?php call_user_func( $this->steps[ $this->step ]['view'] ); ?>
			</section>
			<!-- .onboarding_content -->
			<?php
		}

		/**
		 * Sets steps footer HTML.
		 *
		 * @since 1.0
		 */
		protected function set_step_footer() {
			$steps = array_keys( $this->steps );
			$last  = array_pop( $steps );
			if ( $last === $this->step ) :
				?>
				<!-- footer -->
				<footer id="footer">
					<a
					class="onboarding-return onboarding_dashboard_btn button"
					href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=cixwishlist_settings' ) ); ?>">
						← <?php esc_html_e( 'Return to Dashboard', 'tws-onboarding' ); ?>
					</a>
				</footer>
				<!-- #footer -->
			<?php endif; ?>
			</main>
			<!-- #main -->
			</body>
			</html>
			<?php
		}

		/**
		 * Sets the Welcome/Introduction page contents.
		 *
		 * @since 1.0
		 */
		protected function introduction() {
			$title = $this->intro_title;

			$description = $this->intro_desc;
			$start       = __( 'Let\'s Start', 'tws-onboarding' );
			$skip        = __( 'Skip & Continue', 'tws-onboarding' );
			$button_text = $this->is_installed ? $start : $skip;
			$status      = get_option( $this->prefix . '_onboarding_dependency_status' );
			$dy_title    = sprintf(
				'%1$s <b>%2$s</b>. %3$s',
				__( 'For this plugin to work, it needs', 'tws-onboarding' ),
				$this->name,
				__( 'You can easily install it from here.', 'tws-onboarding' )
			);

			$show = ! $this->is_installed;
			$msg  = '';
			if ( is_wp_error( $status ) ) {
				$code = $status->get_error_code();
				$show = 'force_install_execution' === $code ? false : $show;
				$msg  = $status->get_error_message();
			} elseif ( is_string( $status ) ) {
				$msg = $status;
			}

			/**
			 * WPHOOK: Filter -> Default intro information.
			 *
			 * @param array  $args      The content title and desccription.
			 * @param string $prefix    The onboarding prefix.
			 * @param bool   $installed Whether dependency plugin installed or not.
			 * @var array
			 * @since 1.0
			 */
			$intro_args = apply_filters(
				'hzfex_onboarding_intro_default_content',
				array(
					'title'  => $title,
					'desc'   => $description,
					'button' => $button_text,
				),
				$this->prefix,
				$this->is_installed
			);

			$dependency_args = array(
				'slug'        => $this->slug,
				'name'        => $this->name,
				'status'      => $msg, // Can be "installed", "pending" or "WP_Error message".
				'next_step'   => $this->get_next_step_link(),
				'button_text' => $intro_args['button'],
				'show'        => $show,
			);

			?>
			<!-- Introduction -->
			<h2><?php echo wp_kses_post( $intro_args['title'] ); ?></h2>
			<p><?php echo wp_kses_post( $intro_args['desc'] ); ?></p>
			<!-- #Introduction -->

			<?php
			if ( ! $this->is_installed ) :
				if ( 0 < strlen( $this->slug ) ) :
					?>
					<?php if ( 'pending' === $status ) : ?>
						<p class="onboarding-dy-title"><?php echo wp_kses_post( $dy_title ); ?></p>
					<?php endif; ?>
					<?php TheWebSolver::get_template( 'dependency.php', $dependency_args, '', $this->config->get_path() . 'templates/' ); ?>
					<?php
				endif;
			else :
				?>
				<!-- Action Buttons -->
				<p id="hz_dyn_btnWrapper" class="hz_dyn_btnWrapper hz_step_actions step onboarding-actions">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-next hz_dyn_btn main_btn hz_btn__prim"><?php echo esc_html( $intro_args['button'] ); ?> →</a>
				</p>
				<!-- #Action Buttons -->
				<?php
			endif;
		}

		/**
		 * Handles Dependency plugin installation via Ajax.
		 *
		 * @since 1.0
		 * @static
		 */
		public function install_dependency() {
			$msg         = __( 'You messed up the code! Please contact the developer :)', 'tws-onboarding' );
			$noparam     = __( 'The plugin could not be installed due to an invalid slug, filename, or version. Manual installation required.', 'tws-onboarding' );
			$isinstalled = __( 'The plugin is already installed. You are trying to bypass the security. Do not force me to come get you!!!', 'tws-onboarding' );

			// Bail if ajax errors.
			if ( false === check_ajax_referer( $this->prefix . '_install_dep_action', $this->prefix . '_install_dep_key' ) ) {
				exit( wp_kses_post( $msg ) );
			}

			$post = wp_unslash( $_POST );

			$slug      = isset( $post['slug'] ) && ! empty( $post['slug'] ) ? $post['slug'] : 'false';
			$file      = isset( $post['file'] ) && ! empty( $post['file'] ) ? $post['file'] : 'false';
			$version   = isset( $post['version'] ) && ! empty( $post['version'] ) ? $post['version'] : 'false';
			$name      = isset( $post['name'] ) && ! empty( $post['name'] ) ? $post['name'] : $this->name;
			$prefix    = isset( $post['prefix'] ) && ! empty( $post['prefix'] ) ? $post['prefix'] : $this->prefix;
			$installed = isset( $post['installed'] ) && is_bool( $post['installed'] ) ? $post['installed'] : $this->is_installed;

			$validate = array( $slug, $file, $version );

			// If invalid slug, file or version, $validate => false.
			if ( in_array( 'false', $validate, true ) ) {
				$error = new \WP_Error( 'invalid_plugin', $noparam );
				update_option( $prefix . '_onboarding_dependency_status', $error );
				wp_send_json_error( $error, 404 );
				exit( wp_kses_post( $noparam ) );
			}

			// If trying to install it again, stop execution.
			if ( true === $installed ) {
				$error = new \WP_Error( 'force_install_execution', $isinstalled );
				update_option( $prefix . '_onboarding_dependency_status', $error );
				wp_send_json_error( $error, 404 );
				exit( wp_kses_post( $isinstalled ) );
			}

			// Start installation. Suppress feedback.
			ob_start();

			// NOTE: Sometime installation may throw "An unexpected error occurred" WordPress warning. Error messge: (WordPress could not establish a secure connection to WordPress.org. Please contact your server administrator.)
			// Also, plugin activation triggers Ajax in an infinite loop without activation. So, $activate => false.
			$response = TheWebSolver::maybe_install_plugin( $slug, $file, $version, false );

			// Discard feedback.
			ob_end_clean();

			// Update option to reflect installed status if we get dependency plugin name as response.
			if ( is_string( $response ) && $response === $name ) {
				update_option( $prefix . '_onboarding_dependency_status', 'installed' );
			}

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response );
			} else {
				wp_send_json_success( $response );
			}

			// Terminate and stop further execution.
			die();
		}

		/**
		 * Gets the recommended plugin step's view.
		 *
		 * @since 1.0
		 */
		protected function recommended_view() {
			$title = __( 'Recommended Plugins', 'tws-onboarding' );
			$desc  = __( 'Get the recommended plugins', 'tws-onboarding' );

			/**
			 * WPHOOK: Filter -> default recommended plugin contents.
			 *
			 * @param array  $content `title` and `desc` content.
			 * @param string $prefix   The onboarding prefix.
			 * @var array
			 * @since 1.0
			 */
			$recommended = apply_filters(
				'hzfex_onboarding_recommended_default_content',
				array(
					'title' => $title,
					'desc'  => $desc,
				),
				$this->prefix
			);

			// Get the recommended plugins status.
			$text = __( 'Continue', 'tws-onboarding' );
			?>
			<form method="POST">
				<h2><?php echo wp_kses_post( $recommended['title'] ); ?></h2>
				<div><?php echo wp_kses_post( $recommended['desc'] ); ?></div>
				<fieldset id="onboarding-recommended-plugins">

					<?php
					// Get all recommended plugins active status.
					$plugins_status = get_option( $this->prefix . '_get_onboarding_recommended_plugins_status', array() );

					// Get all recommended plugins checked status.
					$plugins_checked = get_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', array() );

					foreach ( $this->recommended as $plugin ) :
						$slug = $plugin['slug'];
						$file = isset( $plugin['file'] ) ? $plugin['file'] : $slug;
						$file = $file . '.php';
						$base = $slug . '/' . $file;

						// Get current installed status (maybe deleted outside the scope of onboarding).
						$exists = TheWebSolver::maybe_plugin_is_installed( $base );

						// Get current activated status (maybe activated/deactivated outside the scope of onboarding).
						$is_active = $this->get_active_status( $base );

						// Previous state of the current plugin.
						$plugins_status[ $slug ]  = isset( $plugins_status[ $slug ] ) ? $plugins_status[ $slug ] : 'false';
						$plugins_checked[ $slug ] = isset( $plugins_checked[ $slug ] ) ? $plugins_checked[ $slug ] : 'yes';

						// Set current plugin's current active status if any difference in it's status.
						if ( $plugins_status[ $slug ] !== $is_active ) {
							$plugins_status[ $slug ] = $is_active;
						}

						// Recommended plugin deleted/not-installed, force set "checked" => "yes".
						if ( ! $exists ) {
							$plugins_checked[ $slug ] = 'yes';
						}

						// Set actual active status of current plugin.
						$plugin['status'] = $plugins_status[ $slug ];

						// Set actual checked status of current plugin.
						$plugin['checked'] = $plugins_checked[ $slug ];
						?>

						<div class="hz_control_field">
							<?php $this->display_recommended_plugin( $plugin ); ?>
						</div>
						<?php
					endforeach;
					update_option( $this->prefix . '_get_onboarding_recommended_plugins_status', $plugins_status );
					update_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', $plugins_checked );
					?>

				</fieldset>

				<?php
				// Set the button text accordingly.
				if ( in_array( 'false', $plugins_status, true ) ) :
					$text = __( 'Save & Continue', 'tws-onboarding' );
					?>
					<!-- onboarding-recommended-info contents will be added from "onboarding.js" -->
					<div class="onboarding-recommended-info hz_flx column center">
						<p class="label"><span class="count"></span><span class="suffix"></span></p>
						<p id="onboarding-recommended-names"></p>
					</div>
					<!-- .onboarding-recommended-info -->
					<?php
				endif;
					$this->get_step_buttons( true, true, true, $text );
				?>

			</form>
			<?php
		}

		/**
		 * Installs and activates recommended plugins.
		 *
		 * @since 1.0
		 */
		protected function recommended_save() {
			$this->validate_save();

			// Get all recommended plugins checked status.
			$plugins_checked = get_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', array() );

			foreach ( $this->recommended as $plugin ) {
				$slug = $plugin['slug'];
				$file = isset( $plugin['file'] ) ? $plugin['file'] : $slug;

				if ( ! isset( $_POST[ 'onboarding-' . $slug ] ) || 'yes' !== $_POST[ 'onboarding-' . $slug ] ) { // phpcs:ignore WordPress.Security.NonceVerification
					// Set checkbox as not checked for current plugin.
					$plugins_checked[ $slug ] = 'no';
					continue;
				}

				// Set checkbox as checked for current plugin.
				$plugins_checked[ $slug ] = 'yes';

				$this->install_plugin(
					$slug,
					array(
						'name' => $plugin['title'],
						'slug' => $slug,
						'file' => $file . '.php',
					),
					true
				);
			}

			// Finally, update the recommended plugins checked status from checkbox (toggle btn) checked state.
			update_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status', $plugins_checked );

			wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		/**
		 * Recommended plugins display.
		 *
		 * @param array $data The plugin data in an array.
		 * * `string` `slug`    - The plugin slug.
		 * * `string` `title`   - The plugin title/name.
		 * * `string` `desc`    - The plugin description.
		 * * `string` `logo`    - The plugin logo URL.
		 * * `string` `alt`     - The plugin logo alt text.
		 * * `string` `status`  - The plugin active status.
		 * * `string` `checked` - The plugin checked state.
		 *
		 * @since 1.0
		 */
		protected function display_recommended_plugin( $data ) {
			$slug        = $data['slug'];
			$title       = $data['title'];
			$description = $data['desc'];
			$logo        = $data['logo'];
			$logo_alt    = $data['alt'];
			$status      = $data['status'];
			$checked     = $data['checked'];

			// Set args for data attribute.
			$args = array(
				'slug' => $slug,
				'name' => $title,
			);
			?>

			<div class="recommended-plugin hz_switcher_control <?php echo esc_attr( 'true' === $status ? 'disabled' : 'enabled' ); ?> hz_onboarding_image_icon">
				<label for="<?php echo esc_attr( 'onboarding_recommended_' . $slug ); ?>">
					<input
					id="<?php echo esc_attr( 'onboarding_recommended_' . $slug ); ?>"
					type="checkbox"
					name="<?php echo esc_attr( 'onboarding-' . $slug ); ?>"
					value="yes"
					data-plugin="<?php echo esc_attr( wp_json_encode( $args ) ); ?>"
					data-active="<?php echo esc_attr( $status ); ?>"
					data-control="switch"
					<?php
					if ( 'true' === $status ) :
						echo 'disabled="disabled"';
					else :
						// It will always be "no" if saved more than once after activation.
						echo 'yes' === $checked ? 'checked="checked"' : '';
					endif;
					?>
					/>
					<span class="hz_switcher"></span>
					<figure><img src="<?php echo esc_url( $logo ); ?>" class="<?php echo esc_attr( 'recommended-plugin-icon-' . $slug ); ?> recommended-plugin-icon" alt="<?php echo esc_attr( $logo_alt ); ?>" /></figure>
					<div class="recommended-plugin-desc">
						<p><?php echo esc_html( $title ); ?></p>
						<p class="desc"><?php echo wp_kses_post( $description ); ?></p>
					</div>
				</label>
				<?php if ( 'true' === $status ) : ?>
					<div class="hz_recommended_active_notice hz_flx row center"><span><b><?php echo esc_html( $title ); ?></b> <?php esc_html_e( 'is already active', 'tws-onboarding' ); ?></span></div>
				<?php endif; ?>
			</div>

			<?php
		}

		/**
		 * Sets onboarding final step.
		 *
		 * @since 1.0
		 */
		protected function final_step() {
			$this->reset();

			$dep_option = $this->prefix . '_onboarding_dependency_status';
			$dep_status = get_option( $dep_option );
			if ( isset( $this->reset['dependency_status'] ) && $this->reset['dependency_status'] ) {
				delete_option( $dep_option );
			} elseif ( is_wp_error( $dep_status ) ) {
					$code    = $dep_status->get_error_message();
					$message = 'force_install_execution' === $code ? 'installed' : $dep_status->get_error_message();
					update_option( $dep_option, $message );
			}

			if ( isset( $this->reset['dependency_name'] ) && $this->reset['dependency_name'] ) {
				delete_option( $this->prefix . '_onboarding_dependency_name' );
			}

			if ( isset( $this->reset['recommended_status'] ) && $this->reset['recommended_status'] ) {
				delete_option( $this->prefix . '_get_onboarding_recommended_plugins_status' );
			}

			if ( isset( $this->reset['recommended_checked_status'] ) && $this->reset['recommended_checked_status'] ) {
				delete_option( $this->prefix . '_get_onboarding_recommended_plugins_checked_status' );
			}

			/**
			 * Update onboarding steps status option set during plugin activation to `complete`.
			 *
			 * @see {@method `Config::enable_onboarding()`}
			 */
			update_option( $this->prefix . '_onboarding_steps_status', 'complete' );

			/**
			 * WPHOOK: Action -> Fires before final step contents.
			 *
			 * This hook can be used for additional tasks at final step
			 * such as activation of dependency plugin, updating/deleting options.
			 *
			 * @param string $prefix The onboarding prefix.
			 * @since 1.0
			 */
			do_action( 'hzfex_onboarding_before_final_step_contents', $this->prefix );

			$title = __( 'Onboarding Wizard Completed Successfully!', 'tws-onboarding' );
			$desc  = __( 'Onboarding wizard is complete. Your plugin is now ready!', 'tws-onboarding' );

			/**
			 * WPHOOK: Filter -> Onboarding ready step contents.
			 *
			 * @param array  $content The onboarding final step contents.
			 * @param string $prefix  The onboarding prefix.
			 * @var array    $content Onboarding ready step content.
			 * @since 1.0
			 */
			$content = apply_filters(
				'hzfex_onboarding_wizard_ready',
				array(
					'title' => $title,
					'desc'  => $desc,
				),
				$this->prefix
			);
			?>

			<div class="onboarding_complete">
				<div style="max-width:400px;margin-bottom:40px">
					<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="100%" viewBox="0 0 727 652" enable-background="new 0 0 727 652" xml:space="preserve">
<path fill="#FFFFFF" opacity="1.000000" stroke="none" 
	d="
M728.000000,228.000000 
	C728.000000,370.000000 728.000000,511.500000 728.000000,653.000000 
	C485.666687,653.000000 243.333374,653.000000 1.000051,653.000000 
	C1.000034,435.666718 1.000034,218.333405 1.000017,1.000081 
	C243.333298,1.000054 485.666595,1.000054 727.999939,1.000027 
	C728.000000,76.500000 728.000000,152.000000 728.000000,228.000000 
M535.071411,576.915222 
	C539.473022,581.285522 543.874573,585.655823 548.565552,590.912537 
	C548.565552,597.303040 548.565552,603.693542 548.565552,609.893921 
	C571.059265,609.893921 592.553284,609.893921 614.520813,609.893921 
	C614.520813,606.634521 614.520813,603.822205 615.259338,601.007629 
	C617.021179,600.566406 618.836243,600.273254 620.536804,599.661621 
	C641.812073,592.009827 658.674255,578.254150 672.428406,560.752991 
	C687.878052,541.094482 700.441467,519.648071 709.383667,496.194855 
	C714.488403,482.806244 717.979736,469.003815 718.930115,454.709198 
	C720.322510,433.767883 715.817932,414.106781 704.592651,396.235321 
	C691.139343,374.816650 672.197998,362.532959 646.172180,361.189911 
	C645.095215,350.761230 638.433899,345.637482 628.676086,342.628174 
	C627.792480,342.697937 626.908875,342.767731 625.263916,342.916473 
	C624.499939,342.890717 623.735962,342.864960 622.619446,342.418610 
	C621.692871,342.671661 620.766235,342.924713 619.152832,343.476440 
	C618.669495,343.598602 618.186157,343.720764 617.102417,343.889557 
	C616.059692,344.412415 615.016907,344.935303 613.395569,345.884460 
	C606.938416,349.169342 603.768616,355.296753 599.281128,361.049042 
	C595.185852,360.713074 591.054626,360.609070 587.004333,359.982208 
	C581.985840,359.205444 577.034485,357.995361 572.005432,356.039673 
	C572.023926,289.035889 572.042419,222.032089 572.801575,154.849640 
	C573.866028,148.613586 575.400757,142.414520 575.894653,136.133621 
	C576.849854,123.985374 574.861267,112.052528 572.897644,100.096863 
	C575.869263,100.126175 578.840942,100.155495 581.883850,101.096497 
	C581.883850,148.902481 581.883850,196.708466 581.883850,244.514450 
	C582.480896,244.523849 583.077942,244.533249 583.674988,244.542648 
	C583.797058,243.295547 584.025269,242.048492 584.025757,240.801346 
	C584.044067,194.868362 584.050171,148.935364 583.996582,103.002434 
	C583.994995,101.681702 583.329041,100.361740 582.079346,98.812500 
	C578.390381,98.722008 574.701416,98.631523 570.901733,97.874001 
	C567.264465,88.086372 562.090698,79.055077 554.367371,72.133987 
	C542.044922,61.091446 527.129944,56.148338 510.567566,55.906971 
	C507.794128,55.866554 505.059174,54.459663 502.265900,53.879250 
	C501.141357,53.645573 498.956390,53.687111 498.867157,54.026932 
	C497.637878,58.708862 493.366882,57.685730 490.302460,58.715332 
	C484.342865,60.717659 478.313904,62.518402 472.287842,64.316071 
	C468.935425,65.316139 465.530945,66.141731 462.149933,67.045959 
	C462.331268,67.158333 462.512573,67.270714 462.693909,67.383087 
	C455.787781,72.130127 448.881653,76.877159 442.751648,81.090714 
	C440.517151,79.918007 439.012848,79.128532 437.508575,78.339058 
	C437.518616,79.811607 437.746368,81.327461 437.464935,82.742020 
	C437.265472,83.744713 436.599243,85.018036 435.762360,85.472351 
	C429.591644,88.822205 423.325562,91.996468 417.873749,94.819824 
	C415.618042,93.062401 414.165527,91.930733 412.712982,90.799072 
	C412.238800,92.343735 411.804413,93.902161 411.276733,95.428314 
	C410.914001,96.477432 410.421753,97.481773 409.098694,98.700714 
	C406.070251,98.635857 403.041840,98.570999 399.996979,97.786064 
	C398.536194,96.975861 397.163574,95.755020 395.600861,95.419487 
	C381.300385,92.348953 371.506683,78.218620 377.754639,62.192059 
	C381.771851,51.887600 385.828552,41.659458 385.125458,30.060001 
	C384.027405,11.945635 373.160461,4.169240 356.139069,7.527463 
	C337.859222,11.133965 324.994080,22.600927 315.647430,37.978172 
	C311.519257,44.769958 309.144104,52.627266 305.448792,60.265850 
	C305.253296,60.277527 305.057800,60.289200 304.333008,59.873882 
	C275.991608,31.186783 230.669113,25.937529 196.440948,47.627617 
	C181.015320,57.402718 169.286728,70.500259 161.902878,88.008469 
	C139.577576,92.359467 117.251572,96.706894 94.927162,101.062508 
	C74.235519,105.099571 53.545589,109.145393 32.085579,113.182961 
	C26.110357,114.539093 20.103319,115.775436 14.185367,117.347198 
	C12.923829,117.682259 10.970472,119.342979 11.061302,120.212769 
	C11.740138,126.713425 12.852734,133.168793 13.895715,140.077438 
	C22.223911,138.348511 29.647429,136.807388 37.821018,135.335770 
	C44.797344,134.108353 51.773670,132.880936 59.200455,132.251389 
	C64.488266,135.011887 69.915939,137.542664 74.999863,140.638031 
	C77.254761,142.010925 79.543831,144.209320 80.567505,146.574051 
	C85.695625,158.420319 90.336555,170.476257 95.296577,182.396973 
	C98.330757,189.689240 101.631493,196.870605 104.873016,204.828262 
	C108.899879,211.470367 112.926743,218.112473 116.361893,225.084396 
	C117.297409,226.773026 118.203812,228.478577 119.172958,230.147690 
	C129.100555,247.245300 139.038330,264.337006 148.953445,281.997070 
	C149.248795,282.650909 149.544144,283.304779 149.922012,284.644836 
	C149.926849,285.104767 149.931686,285.564697 149.542145,286.287323 
	C149.764832,286.890472 149.987503,287.493622 150.565613,288.910614 
	C152.400497,302.276031 154.235382,315.641449 155.328354,329.073120 
	C148.697571,332.138550 141.951202,334.978638 135.456635,338.309631 
	C111.405891,350.644958 91.226326,367.575714 76.048378,390.040649 
	C62.316250,410.365601 55.490021,432.715149 55.803078,457.535919 
	C56.052246,477.291077 60.407261,495.835358 70.076668,512.711670 
	C87.783585,543.616150 115.566139,558.723938 150.725739,559.777344 
	C162.868866,560.141052 175.089096,557.930969 187.692093,557.525818 
	C191.575638,575.347351 195.459167,593.168884 199.169586,611.677307 
	C199.472305,612.786316 199.775024,613.895325 200.097626,615.770691 
	C200.530212,617.511353 200.962784,619.252075 201.286026,621.582458 
	C201.319580,621.779480 201.353119,621.976501 200.825104,622.638794 
	C198.587662,623.595276 196.350220,624.551758 193.685349,624.765015 
	C191.749130,624.528259 189.790375,623.975159 187.880753,624.111267 
	C180.788879,624.616699 173.709213,625.308533 166.632370,626.005615 
	C156.760284,626.978088 146.840027,627.661926 137.048050,629.168762 
	C131.560745,630.013184 125.458702,630.097656 120.413170,635.593140 
	C123.261375,636.892273 125.468140,638.441650 127.883293,638.905151 
	C137.615768,640.772827 147.355453,643.050659 157.197632,643.804932 
	C177.195938,645.337463 197.254929,646.201416 217.304443,646.885071 
	C225.907364,647.178406 234.557495,646.479858 243.171463,645.979004 
	C264.670898,644.728882 286.174866,643.504761 307.647461,641.879944 
	C313.198029,641.459961 318.686859,639.772644 324.100311,638.288940 
	C325.565216,637.887329 326.650635,636.101135 327.910217,634.950500 
	C326.619720,633.819519 325.373779,632.630432 324.015076,631.588379 
	C323.526245,631.213501 322.777344,631.108765 322.128235,631.021912 
	C309.521576,629.335266 296.923645,627.575195 284.298676,626.037170 
	C277.407654,625.197754 270.479431,624.417969 263.550323,624.215149 
	C258.034882,624.053833 252.492661,624.808167 246.258698,625.062805 
	C244.150360,624.071411 242.042038,623.080017 240.034042,621.255371 
	C240.696091,615.838440 241.358139,610.421509 242.449844,604.335571 
	C243.709427,594.891052 244.968994,585.446594 247.123703,576.027954 
	C273.018677,576.027954 298.913635,576.027954 324.808624,576.027954 
	C324.806244,575.324402 324.803864,574.620850 324.801483,573.917297 
	C298.825134,573.917297 272.848785,573.917297 246.800293,573.043335 
	C247.219833,569.363342 247.639374,565.683289 248.988449,561.971863 
	C339.921448,561.963989 430.854431,561.956848 521.787415,561.945251 
	C524.283508,561.944946 526.779541,561.897705 529.703369,562.368652 
	C535.889221,564.931335 542.075073,567.493958 548.531555,570.767029 
	C548.441101,571.507141 548.350647,572.247253 547.871704,573.059509 
	C547.573059,573.307739 547.274475,573.556030 546.044189,573.873108 
	C514.864319,573.873108 483.684479,573.873108 452.504639,573.873108 
	C452.504517,574.596313 452.504395,575.319458 452.504272,576.042664 
	C454.213379,576.042664 455.922455,576.038574 457.631561,576.043335 
	C483.411011,576.115540 509.190460,576.188965 535.071411,576.915222 
M650.101685,274.730591 
	C652.120239,273.578613 654.247620,272.581268 656.139282,271.249115 
	C666.927551,263.651764 671.164673,248.588318 665.995422,236.518982 
	C660.479126,223.639191 647.137268,216.433792 633.512146,218.975998 
	C620.138977,221.471207 610.226868,233.157974 609.953125,246.752899 
	C609.527344,267.902466 628.203491,281.286713 650.101685,274.730591 
M589.444763,316.089722 
	C586.766724,319.949249 585.827148,323.970001 588.755798,328.085907 
	C591.242004,331.580078 594.703491,333.560181 599.043518,332.779938 
	C603.706970,331.941498 606.816895,329.010651 607.693298,324.268829 
	C608.451904,320.164154 607.004944,316.725922 603.521912,314.320923 
	C598.709778,310.998352 595.924438,311.281647 589.444763,316.089722 
M97.879723,346.614166 
	C97.506096,345.340485 97.294258,343.989716 96.730324,342.806732 
	C94.502312,338.133057 88.573227,335.710083 83.583206,337.327698 
	C79.308517,338.713379 76.187820,344.397278 77.152443,349.040314 
	C78.289589,354.513702 82.197891,357.382324 88.048607,357.037872 
	C92.950134,356.749359 95.978340,353.774750 97.879723,346.614166 
z"/>
<path fill="#C7C8E7" opacity="1.000000" stroke="none" 
	d="
M572.060974,155.028290 
	C572.042419,222.032089 572.023926,289.035889 571.999817,356.977051 
	C571.991333,373.573059 571.988403,389.231659 571.971069,405.166931 
	C571.985291,405.626068 572.013855,405.808533 571.980957,406.456909 
	C572.013916,424.239105 572.108398,441.555328 571.911926,459.080627 
	C571.646362,460.781921 571.671753,462.274170 571.763062,464.245087 
	C571.882874,491.062714 571.936768,517.401733 572.027039,543.831177 
	C572.063354,543.921692 572.081238,544.320557 571.629517,544.462036 
	C563.768921,544.603577 556.360046,544.603577 549.022156,544.603577 
	C548.718689,550.884521 548.462891,556.180176 547.795532,561.423218 
	C541.347900,561.537842 535.311768,561.705139 529.275635,561.872375 
	C526.779541,561.897705 524.283508,561.944946 521.787415,561.945251 
	C430.854431,561.956848 339.921448,561.963989 248.500519,561.567261 
	C248.671478,556.574951 249.382721,551.994019 249.979767,547.398254 
	C252.091919,531.139771 254.382553,514.900452 256.196350,498.608337 
	C258.332550,479.420563 263.126038,460.544128 261.256531,440.880096 
	C259.891815,426.525757 260.354065,412.002350 259.839233,397.560120 
	C259.656616,392.436340 258.993683,387.329712 258.623718,381.822144 
	C258.608459,377.240753 258.519562,373.052399 258.546814,368.465912 
	C258.348419,362.603760 258.044220,357.139160 257.717468,351.675903 
	C256.468994,330.799652 255.213257,309.923798 253.970215,288.671692 
	C253.980423,287.542480 253.980652,286.789398 254.250946,285.808533 
	C261.096710,268.254852 267.734467,250.952057 274.202637,233.586105 
	C275.739899,229.458832 276.709381,225.120056 278.109558,220.605347 
	C279.150757,219.157227 280.022339,217.982361 281.368652,216.845078 
	C368.394073,216.882660 454.944794,216.882660 541.651489,216.882660 
	C541.651489,204.011978 541.651489,191.811539 541.651489,179.137375 
	C459.508026,179.137375 377.761047,179.137375 296.041107,178.762451 
	C306.419067,155.558670 316.769989,132.729813 327.510529,109.927261 
	C330.794952,113.838791 335.012360,112.992622 338.957916,112.993301 
	C414.346222,113.006248 489.734558,113.002647 565.122864,113.003204 
	C571.970398,113.003250 571.961121,113.005539 571.974426,120.082443 
	C571.996277,131.731079 572.031433,143.379669 572.060974,155.028290 
M524.984253,451.650940 
	C518.431519,441.422638 509.286072,436.240204 496.945282,436.958740 
	C485.779510,437.608887 477.758850,443.187225 472.704468,452.805878 
	C467.760651,462.214081 467.383301,472.102844 473.012238,481.336639 
	C482.895630,497.549469 501.731873,501.407593 517.403015,490.703552 
	C526.815369,484.274536 532.625671,466.914490 527.367004,457.394379 
	C532.374207,453.570709 537.528259,449.918884 542.310425,445.831909 
	C544.028503,444.363647 546.234436,440.392578 545.863037,440.039276 
	C542.888672,437.209961 540.856140,440.570129 538.781311,442.024628 
	C534.176331,445.252716 529.820435,448.836151 524.984253,451.650940 
M525.030151,269.803497 
	C524.614136,269.282867 524.170776,268.781555 523.786194,268.238678 
	C516.392944,257.803711 502.860138,253.010086 490.859314,256.583099 
	C478.540680,260.250763 469.383606,272.026093 469.034729,284.647980 
	C468.664673,298.035950 477.674652,310.179565 491.196075,314.516937 
	C502.683594,318.201874 516.733582,313.050385 523.946350,301.956604 
	C529.002136,294.180450 531.497375,285.772644 527.242004,276.199188 
	C533.144104,271.617310 539.104187,267.106262 544.884644,262.375671 
	C545.732300,261.681976 545.725586,259.944275 546.114136,258.689636 
	C544.712585,258.616608 543.217346,258.209320 541.936707,258.568024 
	C540.742188,258.902618 539.782959,260.061371 538.707581,260.840057 
	C534.296326,264.034241 529.880249,267.221863 525.030151,269.803497 
M524.994080,361.352722 
	C519.930420,351.636353 511.473785,346.784882 500.972321,346.174469 
	C489.628448,345.515137 480.362976,349.912842 474.010315,359.854523 
	C465.998657,372.392578 468.384766,389.771332 479.635071,399.031708 
	C491.477081,408.779144 508.596802,408.221008 519.627380,397.918610 
	C528.895203,389.262634 530.449646,378.663208 528.267334,366.315125 
	C533.572144,362.044647 538.969421,357.880798 544.108704,353.419678 
	C545.225891,352.449829 545.418518,350.414795 546.036987,348.870361 
	C544.296387,349.179016 542.176453,348.959045 540.881348,349.888428 
	C535.489136,353.758026 530.345337,357.973724 524.994080,361.352722 
M440.498474,483.941437 
	C445.328186,483.938599 450.162354,484.042267 454.983887,483.842316 
	C455.938354,483.802704 456.844421,482.596436 457.772827,481.928192 
	C456.805573,481.299133 455.900726,480.512970 454.849487,480.095367 
	C454.129517,479.809296 453.205688,480.034332 452.372986,480.034332 
	C394.416107,480.033997 336.459198,480.033844 278.502319,480.035034 
	C277.503204,480.035065 276.406433,479.796631 275.532227,480.131500 
	C274.646423,480.470825 273.980560,481.384399 273.218353,482.046417 
	C274.019379,482.670990 274.739899,483.616394 275.640930,483.842590 
	C276.895874,484.157623 278.285950,483.940582 279.618195,483.940582 
	C332.911896,483.941559 386.205566,483.941315 440.498474,483.941437 
M400.500000,304.918671 
	C418.146606,304.918732 435.793457,304.954071 453.439453,304.850311 
	C454.813934,304.842194 456.182739,303.860199 457.554169,303.330841 
	C456.145599,302.662140 454.755402,301.949127 453.317139,301.351929 
	C452.891052,301.175018 452.329559,301.324127 451.830109,301.324127 
	C394.228912,301.324005 336.627747,301.323883 279.026550,301.323883 
	C278.027679,301.323883 276.968597,301.116791 276.046692,301.383484 
	C275.097473,301.658081 274.282013,302.395050 273.408051,302.929718 
	C274.318787,303.591888 275.145203,304.522797 276.163452,304.842133 
	C277.224701,305.174896 278.470917,304.918549 279.636261,304.918579 
	C319.590851,304.918701 359.545410,304.918671 400.500000,304.918671 
M436.499268,391.339081 
	C442.496002,391.339355 448.494720,391.416809 454.487427,391.262238 
	C455.462067,391.237091 456.410217,390.184784 457.370636,389.607269 
	C456.370514,389.087952 455.406219,388.470428 454.356262,388.087830 
	C453.767914,387.873413 453.037689,388.047668 452.371399,388.047668 
	C394.403137,388.047394 336.434845,388.047211 278.466583,388.047333 
	C277.467133,388.047333 276.402008,387.838837 275.488953,388.120789 
	C274.755341,388.347351 274.205902,389.170258 273.574707,389.728546 
	C274.245453,390.263641 274.847473,391.084381 275.603180,391.266174 
	C276.705841,391.531403 277.918976,391.338928 279.084991,391.338928 
	C331.223114,391.339081 383.361267,391.338959 436.499268,391.339081 
M362.500000,283.946899 
	C332.860077,283.991974 303.220184,284.034027 273.580353,284.114868 
	C273.186493,284.115936 272.779480,284.590179 272.409760,284.882874 
	C272.302216,284.968018 272.275787,285.187439 272.261597,285.349670 
	C272.247986,285.505219 272.298492,285.666351 272.320831,285.825043 
	C276.973145,287.909760 455.971985,287.478912 458.745544,283.946747 
	C426.997009,283.946747 395.248505,283.946747 362.500000,283.946899 
M403.500000,373.964630 
	C419.982117,373.964966 436.464355,373.994080 452.946136,373.919281 
	C454.517303,373.912140 456.085541,373.255524 457.655121,372.900421 
	C457.536652,372.360504 457.418152,371.820618 457.299683,371.280701 
	C396.097412,371.280701 334.895142,371.280701 273.692871,371.280701 
	C273.583496,371.873993 273.474121,372.467316 273.364746,373.060608 
	C275.121307,373.361969 276.877533,373.924286 278.634399,373.926392 
	C319.922913,373.975708 361.211456,373.964569 403.500000,373.964630 
M274.423889,463.652588 
	C274.006134,467.487976 277.041382,465.928314 278.493561,465.940308 
	C294.476257,466.072235 310.460449,466.023376 326.444214,466.023376 
	C368.068604,466.023376 409.692993,466.032928 451.317352,465.992035 
	C453.195343,465.990204 455.072876,465.531555 456.950623,465.285706 
	C456.929352,464.697784 456.908112,464.109894 456.886841,463.522003 
	C454.906464,463.333466 452.926178,462.981293 450.945709,462.980255 
	C394.503021,462.950714 338.060364,462.954285 281.617676,462.964630 
	C279.457947,462.965027 277.298187,463.086060 274.423889,463.652588 
z"/>
<path fill="#ECF1F7" opacity="1.000000" stroke="none" 
	d="
M187.274109,556.896545 
	C175.089096,557.930969 162.868866,560.141052 150.725739,559.777344 
	C115.566139,558.723938 87.783585,543.616150 70.076668,512.711670 
	C60.407261,495.835358 56.052246,477.291077 55.803078,457.535919 
	C55.490021,432.715149 62.316250,410.365601 76.048378,390.040649 
	C91.226326,367.575714 111.405891,350.644958 135.456635,338.309631 
	C141.951202,334.978638 148.697571,332.138550 155.901550,329.384827 
	C159.109131,347.175018 161.743530,364.653473 164.330444,382.589844 
	C164.318558,397.693146 164.354172,412.338531 164.297104,427.443848 
	C164.303375,434.542114 163.471542,441.354614 164.675217,447.786194 
	C168.334946,467.341339 172.833389,486.738373 176.904785,506.217957 
	C180.432419,523.095947 183.822968,540.002563 187.274109,556.896545 
M143.945007,495.473206 
	C143.945007,484.736908 143.945007,474.000610 143.945007,463.299438 
	C129.185196,463.299438 115.276611,463.299438 101.204208,463.299438 
	C101.204208,477.651489 101.204208,491.693665 101.204208,505.773895 
	C115.546967,505.773895 129.566574,505.773895 143.944977,505.773895 
	C143.944977,502.402802 143.944977,499.431732 143.945007,495.473206 
z"/>
<path fill="#02C7DE" opacity="1.000000" stroke="none" 
	d="
M164.377945,382.131927 
	C161.743530,364.653473 159.109131,347.175018 156.272491,329.351715 
	C154.235382,315.641449 152.400497,302.276031 150.495728,288.242401 
	C150.262726,287.057678 150.099625,286.541138 149.936523,286.024628 
	C149.931686,285.564697 149.926849,285.104767 150.115570,284.060425 
	C156.793121,275.341919 163.277115,267.207794 169.761093,259.073700 
	C169.270279,258.691132 168.779480,258.308594 168.288681,257.926025 
	C166.655762,259.667023 165.022827,261.407990 163.133240,262.901733 
	C154.647186,255.711655 146.470566,248.704895 138.172165,241.845490 
	C131.172684,236.059738 124.031868,230.445007 116.953606,224.754578 
	C112.926743,218.112473 108.899879,211.470367 105.184952,204.341202 
	C110.987633,200.476120 116.478371,197.098083 122.282646,193.875702 
	C140.055222,208.418060 157.279282,223.105209 175.092712,237.038864 
	C181.909225,242.370712 190.073364,245.979706 197.796204,250.559952 
	C198.206894,250.716354 198.444794,250.686600 198.682693,250.656830 
	C198.517075,250.378647 198.351456,250.100464 198.128235,249.484955 
	C196.064926,246.256058 194.059250,243.364487 192.480011,240.384155 
	C199.939377,239.097488 206.972290,237.899612 213.933472,237.075668 
	C212.596359,240.181244 211.330994,242.912857 210.065628,245.644485 
	C210.484375,245.911865 210.903122,246.179230 211.321854,246.446609 
	C215.106415,243.146194 218.838150,239.781967 222.704453,236.580261 
	C224.041077,235.473389 225.791168,234.880081 227.211716,233.856857 
	C229.238449,232.396942 231.135666,230.757202 233.374954,228.982758 
	C234.364624,227.754333 235.011688,226.692932 235.779999,225.728058 
	C239.440842,221.130600 243.134628,216.559387 247.129852,211.845703 
	C255.636902,201.791946 263.830780,191.871323 272.339294,182.095734 
	C278.098785,185.293060 283.543610,188.345352 289.109741,191.773544 
	C288.838654,193.721725 288.567200,195.336472 288.032990,196.859024 
	C285.694885,203.523102 283.280579,210.160431 280.893921,216.807495 
	C280.022339,217.982361 279.150757,219.157227 277.812927,220.768616 
	C266.384552,235.414505 255.422394,249.623871 244.284912,263.488342 
	C242.239151,258.867584 240.368729,254.591690 238.498306,250.315796 
	C238.066803,250.467331 237.635300,250.618866 237.203796,250.770401 
	C237.372910,251.756073 237.365463,252.814713 237.738037,253.716339 
	C242.233963,264.596344 246.750015,275.468536 251.378906,286.292297 
	C251.844025,287.379883 253.081299,288.137207 253.960220,289.047821 
	C255.213257,309.923798 256.468994,330.799652 257.717468,351.675903 
	C258.044220,357.139160 258.348419,362.603760 258.152649,368.485565 
	C244.600464,370.875641 231.558578,372.847900 218.238556,374.830719 
	C217.408752,374.897766 216.857071,374.954224 215.848114,374.956329 
	C198.386536,377.311951 181.382248,379.721954 164.377945,382.131927 
M208.001831,321.434448 
	C206.240829,300.494263 204.479828,279.554108 202.718826,258.613922 
	C202.025925,258.678345 201.333038,258.742767 200.640152,258.807190 
	C203.433289,291.676361 206.226440,324.545532 209.019577,357.414734 
	C209.644943,357.410828 210.270294,357.406921 210.895660,357.403015 
	C210.895660,355.079407 211.081192,352.738220 210.863129,350.435272 
	C209.976852,341.074799 208.966766,331.726044 208.001831,321.434448 
z"/>
<path fill="#EBF0F5" opacity="1.000000" stroke="none" 
	d="
M646.407349,361.975830 
	C672.197998,362.532959 691.139343,374.816650 704.592651,396.235321 
	C715.817932,414.106781 720.322510,433.767883 718.930115,454.709198 
	C717.979736,469.003815 714.488403,482.806244 709.383667,496.194855 
	C700.441467,519.648071 687.878052,541.094482 672.428406,560.752991 
	C658.674255,578.254150 641.812073,592.009827 620.536804,599.661621 
	C618.836243,600.273254 617.021179,600.566406 614.817139,600.545410 
	C614.478760,587.427979 614.582458,574.772644 614.962036,562.002808 
	C616.769897,560.849060 618.301819,559.809814 620.169678,558.692139 
	C636.822998,549.494629 650.582703,537.504700 662.413025,521.842957 
	C659.899658,522.195190 658.442627,522.399414 657.016541,522.247925 
	C657.049316,521.654175 657.051147,521.416138 657.428589,521.082397 
	C662.475281,520.397217 665.639893,518.111572 668.400940,514.031860 
	C685.830811,488.278168 696.452393,459.468781 706.593628,430.421936 
	C707.032715,429.164276 706.936951,427.719910 707.207947,425.321625 
	C705.641418,426.466461 704.903320,426.822205 704.412964,427.391418 
	C695.292480,437.980316 684.117188,446.002533 672.164856,452.993500 
	C668.644287,455.052704 664.757690,456.486023 661.065918,457.846252 
	C662.396179,455.910828 663.699402,454.335388 665.998657,451.555939 
	C658.664062,452.336853 652.860413,453.076508 647.034607,453.534760 
	C642.724976,453.873718 638.386597,453.846436 634.244446,453.623291 
	C643.502136,452.282959 652.558167,451.017914 661.657898,450.430756 
	C666.808838,450.098419 667.720642,447.478455 666.214111,443.634003 
	C664.607239,439.533539 662.637451,435.410583 659.994324,431.928894 
	C657.530090,428.682770 654.086487,426.127502 650.878479,423.507446 
	C634.210327,409.894135 632.533264,402.533722 640.596985,382.445312 
	C643.224609,375.899475 644.508728,368.814331 646.407349,361.975830 
z"/>
<path fill="#EAEFF6" opacity="1.000000" stroke="none" 
	d="
M572.431274,154.938965 
	C572.031433,143.379669 571.996277,131.731079 571.974426,120.082443 
	C571.961121,113.005539 571.970398,113.003250 565.122864,113.003204 
	C489.734558,113.002647 414.346222,113.006248 338.957916,112.993301 
	C335.012360,112.992622 330.794952,113.838791 327.641418,109.607574 
	C328.592468,106.174988 329.802216,103.088417 331.479431,100.023132 
	C380.181335,100.044411 428.415741,100.044411 476.650116,100.044411 
	C476.658234,99.622154 476.666321,99.199905 476.674408,98.777649 
	C474.893646,98.777649 473.112854,98.784363 471.332123,98.776627 
	C450.883881,98.687729 430.435608,98.596634 409.987366,98.506119 
	C410.421753,97.481773 410.914001,96.477432 411.276733,95.428314 
	C411.804413,93.902161 412.238800,92.343735 412.712982,90.799072 
	C414.165527,91.930733 415.618042,93.062401 417.873749,94.819824 
	C423.325562,91.996468 429.591644,88.822205 435.762360,85.472351 
	C436.599243,85.018036 437.265472,83.744713 437.464935,82.742020 
	C437.746368,81.327461 437.518616,79.811607 437.508575,78.339058 
	C439.012848,79.128532 440.517151,79.918007 442.751648,81.090714 
	C448.881653,76.877159 455.787781,72.130127 462.693909,67.383087 
	C462.512573,67.270714 462.331268,67.158333 462.149933,67.045959 
	C465.530945,66.141731 468.935425,65.316139 472.287842,64.316071 
	C478.313904,62.518402 484.342865,60.717659 490.302460,58.715332 
	C493.366882,57.685730 497.637878,58.708862 498.867157,54.026932 
	C498.956390,53.687111 501.141357,53.645573 502.265900,53.879250 
	C505.059174,54.459663 507.794128,55.866554 510.567566,55.906971 
	C527.129944,56.148338 542.044922,61.091446 554.367371,72.133987 
	C562.090698,79.055077 567.264465,88.086372 570.498657,98.313980 
	C553.866028,98.753967 537.636353,98.753967 521.406738,98.753967 
	C521.406616,99.256317 521.406494,99.758675 521.406372,100.261024 
	C538.275635,100.261024 555.144897,100.261024 572.014160,100.261024 
	C574.861267,112.052528 576.849854,123.985374 575.894653,136.133621 
	C575.400757,142.414520 573.866028,148.613586 572.431274,154.938965 
z"/>
<path fill="#FDDA09" opacity="1.000000" stroke="none" 
	d="
M304.862335,60.300880 
	C305.057800,60.289200 305.253296,60.277527 306.122864,60.166241 
	C315.155182,58.571251 323.513428,57.075874 331.923737,55.934631 
	C333.323303,63.483475 334.670776,70.678185 335.998962,77.888107 
	C335.979675,77.903336 335.991699,77.950943 335.597687,78.022263 
	C328.526154,79.475189 321.848663,80.856781 314.837616,82.197891 
	C296.925354,85.472786 279.328033,88.693687 261.774963,92.139709 
	C251.581467,94.140892 241.458969,96.503616 230.926025,98.556534 
	C218.320114,100.709969 206.080139,102.946106 193.868729,105.328796 
	C181.189148,107.802834 168.537216,110.418671 155.469574,112.952255 
	C145.963394,114.658867 136.849792,116.329819 127.760925,118.125954 
	C114.788422,120.689568 101.834045,123.344902 88.462349,125.932213 
	C78.285179,127.820091 68.517586,129.736816 58.749996,131.653534 
	C51.773670,132.880936 44.797344,134.108353 37.388504,134.966782 
	C35.588963,127.460983 34.221935,120.324165 32.854904,113.187355 
	C53.545589,109.145393 74.235519,105.099571 94.927162,101.062508 
	C117.251572,96.706894 139.577576,92.359467 162.708588,87.910004 
	C191.740265,82.376633 219.972931,76.976135 248.189880,71.494781 
	C267.092255,67.822861 285.972290,64.036201 304.862335,60.300880 
M246.307846,81.987267 
	C227.194336,85.650871 208.080582,89.313187 188.967361,92.978333 
	C145.190445,101.373024 101.412827,109.764153 57.641708,118.188995 
	C56.357204,118.436226 55.142269,119.044907 52.692760,119.909241 
	C59.453896,121.170532 312.580200,71.947655 315.499359,68.591049 
	C292.087982,73.141014 269.624176,77.506821 246.307846,81.987267 
z"/>
<path fill="#AE5DFD" opacity="1.000000" stroke="none" 
	d="
M258.550049,382.215240 
	C258.993683,387.329712 259.656616,392.436340 259.839233,397.560120 
	C260.354065,412.002350 259.891815,426.525757 261.256531,440.880096 
	C263.126038,460.544128 258.332550,479.420563 256.196350,498.608337 
	C254.382553,514.900452 252.091919,531.139771 249.979767,547.398254 
	C249.382721,551.994019 248.671478,556.574951 248.035736,561.583008 
	C247.639374,565.683289 247.219833,569.363342 246.644913,573.691772 
	C246.402557,574.894165 246.315567,575.448120 246.228577,576.002075 
	C244.968994,585.446594 243.709427,594.891052 241.859650,604.829102 
	C237.929840,605.545532 234.590210,605.768494 231.174713,605.528564 
	C231.019211,584.382812 230.939590,563.700073 230.932709,542.552490 
	C231.017242,533.797668 231.006348,525.507446 231.045547,517.217468 
	C231.127838,499.816284 231.236465,482.415192 231.612030,465.012329 
	C232.261383,465.006348 232.632965,465.002167 233.344131,465.085266 
	C236.865860,466.703918 240.048019,468.235291 243.230164,469.766663 
	C243.534027,469.200195 243.837891,468.633698 244.141739,468.067230 
	C241.748398,466.760193 239.470978,465.128845 236.934525,464.222229 
	C233.145767,462.868042 231.772766,460.335388 231.217392,456.513428 
	C228.329956,436.643188 225.219650,416.805389 222.530457,396.871460 
	C234.765991,391.929474 246.658020,387.072357 258.550049,382.215240 
z"/>
<path fill="#AE5DFD" opacity="1.000000" stroke="none" 
	d="
M187.483093,557.211182 
	C183.822968,540.002563 180.432419,523.095947 176.904785,506.217957 
	C172.833389,486.738373 168.334946,467.341339 164.675217,447.786194 
	C163.471542,441.354614 164.303375,434.542114 164.627869,427.376770 
	C166.095398,426.384583 167.171066,425.979065 168.178696,425.445160 
	C181.192215,418.549713 194.196091,411.636017 207.200027,405.167725 
	C205.306595,412.805664 203.494843,420.024841 201.502258,427.193726 
	C199.481049,434.465637 199.093781,442.877258 189.762939,446.068207 
	C192.789185,445.480713 195.815414,444.893219 198.835938,444.753235 
	C200.287994,469.231354 201.663422,493.267395 203.239914,517.290222 
	C203.978500,528.544861 205.105042,539.774048 206.006363,551.471191 
	C207.177429,571.283813 208.397888,590.639954 209.281830,610.009033 
	C207.959671,610.964600 206.974030,611.907227 205.244202,613.561646 
	C207.608063,613.790527 208.694901,613.895813 209.866180,614.430969 
	C209.985931,616.903564 210.021225,618.946289 209.826721,621.095337 
	C209.398163,621.468201 209.199402,621.734680 208.574585,622.064941 
	C206.429611,622.077087 204.710693,622.025635 202.866516,621.758057 
	C202.292618,621.358887 201.843994,621.175842 201.395370,620.992737 
	C200.962784,619.252075 200.530212,617.511353 200.449783,615.378052 
	C201.882370,614.101807 202.962784,613.218201 205.165131,611.417053 
	C202.151947,611.196289 200.747330,611.093323 199.342712,610.990417 
	C195.459167,593.168884 191.575638,575.347351 187.483093,557.211182 
z"/>
<path fill="#EAEFF6" opacity="1.000000" stroke="none" 
	d="
M331.871643,55.580498 
	C323.513428,57.075874 315.155182,58.571251 306.385010,60.033737 
	C309.144104,52.627266 311.519257,44.769958 315.647430,37.978172 
	C324.994080,22.600927 337.859222,11.133965 356.139069,7.527463 
	C373.160461,4.169240 384.027405,11.945635 385.125458,30.060001 
	C385.828552,41.659458 381.771851,51.887600 377.754639,62.192059 
	C371.506683,78.218620 381.300385,92.348953 395.600861,95.419487 
	C397.163574,95.755020 398.536194,96.975861 399.549286,98.265396 
	C376.715179,98.701843 354.328735,98.658974 331.957397,98.219254 
	C333.312195,91.198586 334.651947,84.574760 335.991699,77.950943 
	C335.991699,77.950943 335.979675,77.903336 336.323212,77.787491 
	C346.952393,72.156693 357.238068,66.641754 367.523712,61.126816 
	C367.412659,60.789444 367.301575,60.452072 367.190491,60.114700 
	C355.417542,58.603298 343.644592,57.091896 331.871643,55.580498 
z"/>
<path fill="#FFDB01" opacity="1.000000" stroke="none" 
	d="
M614.686157,562.117371 
	C614.582458,574.772644 614.478760,587.427979 614.447876,600.546509 
	C614.520813,603.822205 614.520813,606.634521 614.520813,609.893921 
	C592.553284,609.893921 571.059265,609.893921 548.565552,609.893921 
	C548.565552,603.693542 548.565552,597.303040 548.574768,590.026245 
	C548.497070,584.778198 548.410217,580.416565 548.443481,575.721375 
	C548.454590,574.939209 548.345520,574.490601 548.347046,573.780640 
	C548.391785,573.342041 548.325989,573.164673 548.260193,572.987366 
	C548.350647,572.247253 548.441101,571.507141 548.542297,569.983643 
	C548.437744,566.625488 548.322388,564.050659 548.207031,561.475891 
	C548.462891,556.180176 548.718689,550.884521 549.022156,544.603577 
	C556.360046,544.603577 563.768921,544.603577 571.952576,544.591675 
	C573.412354,544.497192 574.097351,544.414673 575.232300,544.456238 
	C577.773865,544.488831 579.865601,544.397339 582.306152,544.443481 
	C583.152222,544.555542 583.649475,544.530090 584.576050,544.588867 
	C587.329773,544.582458 589.654175,544.491760 592.437439,544.500610 
	C599.927856,544.600098 606.959412,544.600098 614.686157,544.600098 
	C614.686157,550.968872 614.686157,556.543152 614.686157,562.117371 
z"/>
<path fill="#5853EA" opacity="1.000000" stroke="none" 
	d="
M304.597656,60.087379 
	C285.972290,64.036201 267.092255,67.822861 248.189880,71.494781 
	C219.972931,76.976135 191.740265,82.376633 163.084656,87.795700 
	C169.286728,70.500259 181.015320,57.402718 196.440948,47.627617 
	C230.669113,25.937529 275.991608,31.186783 304.597656,60.087379 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M646.289795,361.582886 
	C644.508728,368.814331 643.224609,375.899475 640.596985,382.445312 
	C632.533264,402.533722 634.210327,409.894135 650.878479,423.507446 
	C654.086487,426.127502 657.530090,428.682770 659.994324,431.928894 
	C662.637451,435.410583 664.607239,439.533539 666.214111,443.634003 
	C667.720642,447.478455 666.808838,450.098419 661.657898,450.430756 
	C652.558167,451.017914 643.502136,452.282959 633.788879,453.648376 
	C632.546387,454.106323 631.942871,454.180145 631.167175,454.133148 
	C630.513245,453.731476 630.031433,453.450653 629.193604,453.133911 
	C628.234009,453.378296 627.630493,453.658630 626.565735,453.935028 
	C619.582642,454.450165 613.060791,454.969238 605.833801,455.544434 
	C606.260742,453.289368 606.580688,451.752289 606.839905,450.205048 
	C608.303284,441.468811 609.752075,432.730164 611.570862,423.893250 
	C612.280151,421.500671 612.624512,419.207214 613.010986,416.507965 
	C613.978638,409.214325 614.904236,402.326447 615.995667,395.022278 
	C616.377625,392.536621 616.593750,390.467285 616.692505,387.978943 
	C616.950989,372.987610 617.326904,358.415253 617.702820,343.842926 
	C618.186157,343.720764 618.669495,343.598602 619.806885,343.466492 
	C621.297974,343.250763 622.135010,343.044983 622.971985,342.839203 
	C623.735962,342.864960 624.499939,342.890717 625.843628,343.068726 
	C627.287903,343.191010 628.152588,343.161072 629.017212,343.131134 
	C638.433899,345.637482 645.095215,350.761230 646.289795,361.582886 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M572.202881,458.871582 
	C572.108398,441.555328 572.013916,424.239105 572.184204,406.359467 
	C572.704529,405.616882 572.883667,405.379974 573.432434,405.005981 
	C575.042603,404.910278 576.207092,404.903137 577.371155,404.876434 
	C593.486023,404.507111 598.958191,398.592224 598.909729,382.420105 
	C598.888428,375.317230 599.764038,368.211700 600.232361,361.107361 
	C603.768616,355.296753 606.938416,349.169342 614.034851,345.985901 
	C614.888550,348.049957 615.325562,350.017578 615.281128,351.974335 
	C614.995300,364.581512 614.604248,377.186249 613.919556,390.077393 
	C613.445862,392.746613 613.300354,395.130219 613.200806,397.923096 
	C612.465820,404.572296 611.684753,410.812225 610.635864,417.321808 
	C610.250977,419.411255 610.133850,421.231049 609.973877,423.450653 
	C607.542236,434.994751 605.218018,446.153412 602.740295,457.277893 
	C601.269775,463.880402 599.557373,470.429047 597.826660,477.337463 
	C595.385010,484.447571 593.070312,491.222107 590.358765,497.999969 
	C589.161987,498.051086 588.362183,498.098877 587.378784,497.858704 
	C586.149536,495.264191 585.103821,492.957642 584.007507,490.206757 
	C583.956848,468.638306 583.956848,447.514160 583.956848,426.390015 
	C583.170471,426.394836 582.384033,426.399658 581.597656,426.404449 
	C581.597656,443.238007 581.597656,460.071533 581.152527,476.928528 
	C580.410950,476.971375 580.114441,476.990814 579.663574,476.692352 
	C577.073792,470.540131 574.638367,464.705872 572.202881,458.871582 
z"/>
<path fill="#EBEFF6" opacity="1.000000" stroke="none" 
	d="
M246.962341,625.155945 
	C252.492661,624.808167 258.034882,624.053833 263.550323,624.215149 
	C270.479431,624.417969 277.407654,625.197754 284.298676,626.037170 
	C296.923645,627.575195 309.521576,629.335266 322.128235,631.021912 
	C322.777344,631.108765 323.526245,631.213501 324.015076,631.588379 
	C325.373779,632.630432 326.619720,633.819519 327.910217,634.950500 
	C326.650635,636.101135 325.565216,637.887329 324.100311,638.288940 
	C318.686859,639.772644 313.198029,641.459961 307.647461,641.879944 
	C286.174866,643.504761 264.670898,644.728882 243.171463,645.979004 
	C234.557495,646.479858 225.907364,647.178406 217.304443,646.885071 
	C197.254929,646.201416 177.195938,645.337463 157.197632,643.804932 
	C147.355453,643.050659 137.615768,640.772827 127.883293,638.905151 
	C125.468140,638.441650 123.261375,636.892273 120.413170,635.593140 
	C125.458702,630.097656 131.560745,630.013184 137.048050,629.168762 
	C146.840027,627.661926 156.760284,626.978088 166.632370,626.005615 
	C173.709213,625.308533 180.788879,624.616699 187.880753,624.111267 
	C189.790375,623.975159 191.749130,624.528259 193.622162,625.341248 
	C186.370438,628.634644 179.181915,631.351868 171.993393,634.069092 
	C172.083939,634.378296 172.174484,634.687500 172.265045,634.996704 
	C184.850052,633.848022 197.435074,632.699280 210.033279,631.549377 
	C210.242493,628.638000 210.407379,626.343506 211.076080,624.034668 
	C218.026718,624.010498 224.473526,624.000793 231.026672,624.420776 
	C231.132996,626.957520 231.132996,629.064636 231.132996,631.537231 
	C244.294846,632.726501 256.900238,633.865540 269.505646,635.004578 
	C269.567535,634.762695 269.629425,634.520813 269.691315,634.278931 
	C262.114990,631.237915 254.538681,628.196899 246.962341,625.155945 
z"/>
<path fill="#FEA5FD" opacity="1.000000" stroke="none" 
	d="
M58.975227,131.952454 
	C68.517586,129.736816 78.285179,127.820091 88.488892,126.402176 
	C88.933533,129.561081 89.211586,132.253479 88.897354,134.874863 
	C88.118965,141.368225 90.392792,146.580154 94.087273,151.878006 
	C103.672035,165.622559 112.711807,179.747147 121.969109,193.720047 
	C116.478371,197.098083 110.987633,200.476120 105.154709,203.977966 
	C101.631493,196.870605 98.330757,189.689240 95.296577,182.396973 
	C90.336555,170.476257 85.695625,158.420319 80.567505,146.574051 
	C79.543831,144.209320 77.254761,142.010925 74.999863,140.638031 
	C69.915939,137.542664 64.488266,135.011887 58.975227,131.952454 
z"/>
<path fill="#EBF0F7" opacity="1.000000" stroke="none" 
	d="
M599.756714,361.078186 
	C599.764038,368.211700 598.888428,375.317230 598.909729,382.420105 
	C598.958191,398.592224 593.486023,404.507111 577.371155,404.876434 
	C576.207092,404.903137 575.042603,404.910278 573.187378,404.930664 
	C572.326050,404.919952 572.155762,404.905121 571.985474,404.890289 
	C571.988403,389.231659 571.991333,373.573059 572.024170,357.443481 
	C577.034485,357.995361 581.985840,359.205444 587.004333,359.982208 
	C591.054626,360.609070 595.185852,360.713074 599.756714,361.078186 
z"/>
<path fill="#3C1C5B" opacity="1.000000" stroke="none" 
	d="
M116.657745,224.919495 
	C124.031868,230.445007 131.172684,236.059738 138.172165,241.845490 
	C146.470566,248.704895 154.647186,255.711655 162.995926,263.222473 
	C158.401260,269.670380 153.687256,275.550385 148.973267,281.430389 
	C139.038330,264.337006 129.100555,247.245300 119.172958,230.147690 
	C118.203812,228.478577 117.297409,226.773026 116.657745,224.919495 
z"/>
<path fill="#FDDF1E" opacity="1.000000" stroke="none" 
	d="
M649.737427,274.866882 
	C628.203491,281.286713 609.527344,267.902466 609.953125,246.752899 
	C610.226868,233.157974 620.138977,221.471207 633.512146,218.975998 
	C647.137268,216.433792 660.479126,223.639191 665.995422,236.518982 
	C671.164673,248.588318 666.927551,263.651764 656.139282,271.249115 
	C654.247620,272.581268 652.120239,273.578613 649.737427,274.866882 
M614.937500,238.769836 
	C614.686523,243.992599 613.361816,249.439285 614.395081,254.394241 
	C617.019897,266.981354 629.851807,274.964905 642.878601,273.102234 
	C655.362732,271.317169 665.155273,259.765930 664.820496,247.219604 
	C664.496460,235.076492 656.008606,225.048172 643.920837,222.526672 
	C631.923401,220.024017 620.517578,226.176453 614.937500,238.769836 
z"/>
<path fill="#AF5FFC" opacity="1.000000" stroke="none" 
	d="
M32.470245,113.185158 
	C34.221935,120.324165 35.588963,127.460983 37.013466,134.932037 
	C29.647429,136.807388 22.223911,138.348511 13.895715,140.077438 
	C12.852734,133.168793 11.740138,126.713425 11.061302,120.212769 
	C10.970472,119.342979 12.923829,117.682259 14.185367,117.347198 
	C20.103319,115.775436 26.110357,114.539093 32.470245,113.185158 
z"/>
<path fill="#C6CBE8" opacity="1.000000" stroke="none" 
	d="
M589.712524,315.816040 
	C595.924438,311.281647 598.709778,310.998352 603.521912,314.320923 
	C607.004944,316.725922 608.451904,320.164154 607.693298,324.268829 
	C606.816895,329.010651 603.706970,331.941498 599.043518,332.779938 
	C594.703491,333.560181 591.242004,331.580078 588.755798,328.085907 
	C585.827148,323.970001 586.766724,319.949249 589.712524,315.816040 
z"/>
<path fill="#C8C9E8" opacity="1.000000" stroke="none" 
	d="
M97.879997,346.997253 
	C95.978340,353.774750 92.950134,356.749359 88.048607,357.037872 
	C82.197891,357.382324 78.289589,354.513702 77.152443,349.040314 
	C76.187820,344.397278 79.308517,338.713379 83.583206,337.327698 
	C88.573227,335.710083 94.502312,338.133057 96.730324,342.806732 
	C97.294258,343.989716 97.506096,345.340485 97.879997,346.997253 
z"/>
<path fill="#646464" opacity="1.000000" stroke="none" 
	d="
M582.972900,99.041435 
	C583.329041,100.361740 583.994995,101.681702 583.996582,103.002434 
	C584.050171,148.935364 584.044067,194.868362 584.025757,240.801346 
	C584.025269,242.048492 583.797058,243.295547 583.674988,244.542648 
	C583.077942,244.533249 582.480896,244.523849 581.883850,244.514450 
	C581.883850,196.708466 581.883850,148.902481 582.096863,100.507240 
	C582.530884,99.625801 582.751831,99.333618 582.972900,99.041435 
z"/>
<path fill="#452462" opacity="1.000000" stroke="none" 
	d="
M210.572250,624.049011 
	C210.407379,626.343506 210.242493,628.638000 210.033279,631.549377 
	C197.435074,632.699280 184.850052,633.848022 172.265045,634.996704 
	C172.174484,634.687500 172.083939,634.378296 171.993393,634.069092 
	C179.181915,631.351868 186.370438,628.634644 193.835876,625.712891 
	C196.350220,624.551758 198.587662,623.595276 201.377304,622.449219 
	C202.283600,622.164429 202.637680,622.069275 202.991776,621.974121 
	C204.710693,622.025635 206.429611,622.077087 208.817200,622.145264 
	C209.656250,622.159363 209.826630,622.156799 210.077499,622.389404 
	C210.296082,623.099365 210.434174,623.574158 210.572250,624.049011 
z"/>
<path fill="#452462" opacity="1.000000" stroke="none" 
	d="
M246.610535,625.109375 
	C254.538681,628.196899 262.114990,631.237915 269.691315,634.278931 
	C269.629425,634.520813 269.567535,634.762695 269.505646,635.004578 
	C256.900238,633.865540 244.294846,632.726501 231.132996,631.537231 
	C231.132996,629.064636 231.132996,626.957520 231.134705,624.141602 
	C231.221680,623.043762 231.306946,622.654724 231.814606,622.254883 
	C234.802582,622.192261 237.368149,622.140503 239.933716,622.088745 
	C242.042038,623.080017 244.150360,624.071411 246.610535,625.109375 
z"/>
<path fill="#646464" opacity="1.000000" stroke="none" 
	d="
M548.236511,574.041931 
	C548.345520,574.490601 548.454590,574.939209 548.034119,575.725098 
	C543.326355,576.128967 539.148132,576.195557 534.969910,576.262085 
	C509.190460,576.188965 483.411011,576.115540 457.631561,576.043335 
	C455.922455,576.038574 454.213379,576.042664 452.504272,576.042664 
	C452.504395,575.319458 452.504517,574.596313 452.504639,573.873108 
	C483.684479,573.873108 514.864319,573.873108 546.708801,573.927002 
	C547.660889,574.004395 547.948608,574.024780 548.236511,574.041931 
z"/>
<path fill="#AF62FC" opacity="1.000000" stroke="none" 
	d="
M239.983871,621.672058 
	C237.368149,622.140503 234.802582,622.192261 231.686188,621.781921 
	C231.173767,616.210388 231.212189,611.100891 231.250595,605.991455 
	C234.590210,605.768494 237.929840,605.545532 241.644836,605.163574 
	C241.358139,610.421509 240.696091,615.838440 239.983871,621.672058 
z"/>
<path fill="#646464" opacity="1.000000" stroke="none" 
	d="
M246.676147,576.015015 
	C246.315567,575.448120 246.402557,574.894165 246.680984,574.128784 
	C272.848785,573.917297 298.825134,573.917297 324.801483,573.917297 
	C324.803864,574.620850 324.806244,575.324402 324.808624,576.027954 
	C298.913635,576.027954 273.018677,576.027954 246.676147,576.015015 
z"/>
<path fill="#171819" opacity="1.000000" stroke="none" 
	d="
M331.942322,98.616104 
	C354.328735,98.658974 376.715179,98.701843 399.557495,98.625427 
	C403.041840,98.570999 406.070251,98.635857 409.543030,98.603416 
	C430.435608,98.596634 450.883881,98.687729 471.332123,98.776627 
	C473.112854,98.784363 474.893646,98.777649 476.674408,98.777649 
	C476.666321,99.199905 476.658234,99.622154 476.650116,100.044411 
	C428.415741,100.044411 380.181335,100.044411 331.494324,99.722878 
	C331.341888,99.139595 331.642090,98.877846 331.942322,98.616104 
z"/>
<path fill="#CECCF2" opacity="1.000000" stroke="none" 
	d="
M614.247742,389.791809 
	C614.604248,377.186249 614.995300,364.581512 615.281128,351.974335 
	C615.325562,350.017578 614.888550,348.049957 614.324097,345.772766 
	C615.016907,344.935303 616.059692,344.412415 617.402588,343.866241 
	C617.326904,358.415253 616.950989,372.987610 616.413269,388.076721 
	C615.583557,388.992920 614.915649,389.392365 614.247742,389.791809 
z"/>
<path fill="#5D56EC" opacity="1.000000" stroke="none" 
	d="
M547.795532,561.423218 
	C548.322388,564.050659 548.437744,566.625488 548.406982,569.628418 
	C542.075073,567.493958 535.889221,564.931335 529.489502,562.120483 
	C535.311768,561.705139 541.347900,561.537842 547.795532,561.423218 
z"/>
<path fill="#EBF0F5" opacity="1.000000" stroke="none" 
	d="
M535.020630,576.588623 
	C539.148132,576.195557 543.326355,576.128967 547.913940,576.058594 
	C548.410217,580.416565 548.497070,584.778198 548.430054,589.583008 
	C543.874573,585.655823 539.473022,581.285522 535.020630,576.588623 
z"/>
<path fill="#D1EAEE" opacity="1.000000" stroke="none" 
	d="
M148.963348,281.713745 
	C153.687256,275.550385 158.401260,269.670380 163.252594,263.469666 
	C165.022827,261.407990 166.655762,259.667023 168.288681,257.926025 
	C168.779480,258.308594 169.270279,258.691132 169.761093,259.073700 
	C163.277115,267.207794 156.793121,275.341919 150.074310,283.717346 
	C149.544144,283.304779 149.248795,282.650909 148.963348,281.713745 
z"/>
<path fill="#171819" opacity="1.000000" stroke="none" 
	d="
M582.526123,98.926971 
	C582.751831,99.333618 582.530884,99.625801 582.061218,100.051399 
	C578.840942,100.155495 575.869263,100.126175 572.455933,100.178940 
	C555.144897,100.261024 538.275635,100.261024 521.406372,100.261024 
	C521.406494,99.758675 521.406616,99.256317 521.406738,98.753967 
	C537.636353,98.753967 553.866028,98.753967 570.554077,98.647507 
	C574.701416,98.631523 578.390381,98.722008 582.526123,98.926971 
z"/>
<path fill="#AF5FFC" opacity="1.000000" stroke="none" 
	d="
M199.256149,611.333862 
	C200.747330,611.093323 202.151947,611.196289 205.165131,611.417053 
	C202.962784,613.218201 201.882370,614.101807 200.439850,614.994873 
	C199.775024,613.895325 199.472305,612.786316 199.256149,611.333862 
z"/>
<path fill="#CECCF2" opacity="1.000000" stroke="none" 
	d="
M628.846680,342.879639 
	C628.152588,343.161072 627.287903,343.191010 626.224243,343.029236 
	C626.908875,342.767731 627.792480,342.697937 628.846680,342.879639 
z"/>
<path fill="#CECCF2" opacity="1.000000" stroke="none" 
	d="
M622.795715,342.628906 
	C622.135010,343.044983 621.297974,343.250763 620.150330,343.317139 
	C620.766235,342.924713 621.692871,342.671661 622.795715,342.628906 
z"/>
<path fill="#D1EAEE" opacity="1.000000" stroke="none" 
	d="
M149.739334,286.155975 
	C150.099625,286.541138 150.262726,287.057678 150.318008,287.835480 
	C149.987503,287.493622 149.764832,286.890472 149.739334,286.155975 
z"/>
<path fill="#C7C8E7" opacity="1.000000" stroke="none" 
	d="
M548.347046,573.780640 
	C547.948608,574.024780 547.660889,574.004395 547.174622,573.892578 
	C547.274475,573.556030 547.573059,573.307739 548.065918,573.023438 
	C548.325989,573.164673 548.391785,573.342041 548.347046,573.780640 
z"/>
<path fill="#AF5FFC" opacity="1.000000" stroke="none" 
	d="
M202.866516,621.758057 
	C202.637680,622.069275 202.283600,622.164429 201.658081,622.216553 
	C201.353119,621.976501 201.319580,621.779480 201.340698,621.287598 
	C201.843994,621.175842 202.292618,621.358887 202.866516,621.758057 
z"/>
<path fill="#AE5DFD" opacity="1.000000" stroke="none" 
	d="
M281.368652,216.845062 
	C283.280579,210.160431 285.694885,203.523102 288.032990,196.859024 
	C288.567200,195.336472 288.838654,193.721725 289.438354,191.618637 
	C291.768463,187.104355 293.891266,183.120865 296.014069,179.137375 
	C377.761047,179.137375 459.508026,179.137375 541.651489,179.137375 
	C541.651489,191.811539 541.651489,204.011978 541.651489,216.882660 
	C454.944794,216.882660 368.394073,216.882660 281.368652,216.845062 
z"/>
<path fill="#F7A3F8" opacity="1.000000" stroke="none" 
	d="
M296.041107,178.762451 
	C293.891266,183.120865 291.768463,187.104355 289.317078,191.242752 
	C283.543610,188.345352 278.098785,185.293060 272.477173,181.780701 
	C280.487000,167.111496 288.717560,152.927414 296.843689,138.683762 
	C301.676941,130.211914 306.345917,121.646309 311.376740,112.944427 
	C314.168762,108.602219 316.672791,104.438454 319.459686,100.017380 
	C318.218781,93.919510 316.694977,88.078941 315.171143,82.238373 
	C321.848663,80.856781 328.526154,79.475189 335.597687,78.022263 
	C334.651947,84.574760 333.312195,91.198586 331.957397,98.219254 
	C331.642090,98.877846 331.341888,99.139595 331.026794,99.701599 
	C329.802216,103.088417 328.592468,106.174988 327.251831,109.581253 
	C316.769989,132.729813 306.419067,155.558670 296.041107,178.762451 
z"/>
<path fill="#B471F9" opacity="1.000000" stroke="none" 
	d="
M439.998871,483.941345 
	C386.205566,483.941315 332.911896,483.941559 279.618195,483.940582 
	C278.285950,483.940582 276.895874,484.157623 275.640930,483.842590 
	C274.739899,483.616394 274.019379,482.670990 273.218353,482.046417 
	C273.980560,481.384399 274.646423,480.470825 275.532227,480.131500 
	C276.406433,479.796631 277.503204,480.035065 278.502319,480.035034 
	C336.459198,480.033844 394.416107,480.033997 452.372986,480.034332 
	C453.205688,480.034332 454.129517,479.809296 454.849487,480.095367 
	C455.900726,480.512970 456.805573,481.299133 457.772827,481.928192 
	C456.844421,482.596436 455.938354,483.802704 454.983887,483.842316 
	C450.162354,484.042267 445.328186,483.938599 439.998871,483.941345 
z"/>
<path fill="#2C2D34" opacity="1.000000" stroke="none" 
	d="
M400.000000,304.918671 
	C359.545410,304.918671 319.590851,304.918701 279.636261,304.918579 
	C278.470917,304.918549 277.224701,305.174896 276.163452,304.842133 
	C275.145203,304.522797 274.318787,303.591888 273.408051,302.929718 
	C274.282013,302.395050 275.097473,301.658081 276.046692,301.383484 
	C276.968597,301.116791 278.027679,301.323883 279.026550,301.323883 
	C336.627747,301.323883 394.228912,301.324005 451.830109,301.324127 
	C452.329559,301.324127 452.891052,301.175018 453.317139,301.351929 
	C454.755402,301.949127 456.145599,302.662140 457.554169,303.330841 
	C456.182739,303.860199 454.813934,304.842194 453.439453,304.850311 
	C435.793457,304.954071 418.146606,304.918732 400.000000,304.918671 
z"/>
<path fill="#09090D" opacity="1.000000" stroke="none" 
	d="
M435.999329,391.338989 
	C383.361267,391.338959 331.223114,391.339081 279.084991,391.338928 
	C277.918976,391.338928 276.705841,391.531403 275.603180,391.266174 
	C274.847473,391.084381 274.245453,390.263641 273.574707,389.728546 
	C274.205902,389.170258 274.755341,388.347351 275.488953,388.120789 
	C276.402008,387.838837 277.467133,388.047333 278.466583,388.047333 
	C336.434845,388.047211 394.403137,388.047394 452.371399,388.047668 
	C453.037689,388.047668 453.767914,387.873413 454.356262,388.087830 
	C455.406219,388.470428 456.370514,389.087952 457.370605,389.607239 
	C456.410217,390.184784 455.462067,391.237091 454.487427,391.262238 
	C448.494720,391.416809 442.496002,391.339355 435.999329,391.338989 
z"/>
<path fill="#3B1A5B" opacity="1.000000" stroke="none" 
	d="
M258.623718,381.822144 
	C246.658020,387.072357 234.765991,391.929474 222.418671,396.471710 
	C220.814484,389.044586 219.665588,381.932404 218.516693,374.820190 
	C231.558578,372.847900 244.600464,370.875641 258.036499,368.883728 
	C258.519562,373.052399 258.608459,377.240753 258.623718,381.822144 
z"/>
<path fill="#3B1D5B" opacity="1.000000" stroke="none" 
	d="
M244.460236,263.833221 
	C255.422394,249.623871 266.384552,235.414505 277.643372,221.041855 
	C276.709381,225.120056 275.739899,229.458832 274.202637,233.586105 
	C267.734467,250.952057 261.096710,268.254852 254.020233,285.536743 
	C250.499695,278.272858 247.479950,271.053040 244.460236,263.833221 
z"/>
<path fill="#F8F8FB" opacity="1.000000" stroke="none" 
	d="
M527.981018,366.913940 
	C530.449646,378.663208 528.895203,389.262634 519.627380,397.918610 
	C508.596802,408.221008 491.477081,408.779144 479.635071,399.031708 
	C468.384766,389.771332 465.998657,372.392578 474.010315,359.854523 
	C480.362976,349.912842 489.628448,345.515137 500.972321,346.174469 
	C511.473785,346.784882 519.930420,351.636353 524.867920,361.959808 
	C523.843994,363.035919 522.946228,363.504944 522.021179,363.620728 
	C521.580933,362.364197 521.287109,361.379822 520.736572,360.570007 
	C514.557556,351.481659 501.589478,347.466614 489.973236,351.171021 
	C480.590942,354.163055 473.041656,365.002869 472.755676,375.139008 
	C472.466034,385.405151 478.450378,396.738159 490.567596,401.286316 
	C500.039154,404.841431 511.929230,401.783508 519.334961,393.108978 
	C525.304138,386.117157 527.031738,377.910553 525.070190,368.655701 
	C526.150024,367.889679 527.065552,367.401794 527.981018,366.913940 
z"/>
<path fill="#F9F9FB" opacity="1.000000" stroke="none" 
	d="
M526.851379,276.717468 
	C531.497375,285.772644 529.002136,294.180450 523.946350,301.956604 
	C516.733582,313.050385 502.683594,318.201874 491.196075,314.516937 
	C477.674652,310.179565 468.664673,298.035950 469.034729,284.647980 
	C469.383606,272.026093 478.540680,260.250763 490.859314,256.583099 
	C502.860138,253.010086 516.392944,257.803711 523.786194,268.238678 
	C524.170776,268.781555 524.614136,269.282867 525.105713,270.401733 
	C524.136475,271.732849 523.091797,272.465759 522.018127,272.828552 
	C518.826355,269.201111 516.076355,265.349976 512.417297,262.806610 
	C504.091522,257.019409 492.060425,257.692291 483.898621,263.680603 
	C474.830688,270.333740 470.609711,282.111389 473.635132,292.318420 
	C477.927063,306.798309 491.793945,314.755798 506.240631,310.973572 
	C517.441589,308.041046 529.375427,294.294586 524.879272,278.476562 
	C525.641846,277.723389 526.246643,277.220428 526.851379,276.717468 
z"/>
<path fill="#F9F9FB" opacity="1.000000" stroke="none" 
	d="
M526.980469,457.924622 
	C532.625671,466.914490 526.815369,484.274536 517.403015,490.703552 
	C501.731873,501.407593 482.895630,497.549469 473.012238,481.336639 
	C467.383301,472.102844 467.760651,462.214081 472.704468,452.805878 
	C477.758850,443.187225 485.779510,437.608887 496.945282,436.958740 
	C509.286072,436.240204 518.431519,441.422638 524.963928,452.225098 
	C523.990356,453.324036 523.037109,453.848816 522.046875,453.992798 
	C515.202148,441.848877 501.781860,436.887024 489.023224,442.213745 
	C469.790833,450.243164 468.282501,474.051514 480.577728,485.625153 
	C489.623627,494.140167 502.829041,495.452576 512.392456,489.806396 
	C521.856506,484.218903 528.541809,470.793182 524.957275,459.598572 
	C525.730713,458.869781 526.355591,458.397217 526.980469,457.924622 
z"/>
<path fill="#5953EB" opacity="1.000000" stroke="none" 
	d="
M571.990723,543.740723 
	C571.936768,517.401733 571.882874,491.062714 572.117615,464.431152 
	C575.544312,472.129578 578.682373,480.120605 581.908691,488.560364 
	C581.968750,500.517914 581.940552,512.026672 581.640503,523.784119 
	C578.242676,530.602112 575.116638,537.171387 571.990723,543.740723 
z"/>
<path fill="#FAFAFB" opacity="1.000000" stroke="none" 
	d="
M363.000000,283.946838 
	C395.248505,283.946747 426.997009,283.946747 458.745544,283.946747 
	C455.971985,287.478912 276.973145,287.909760 272.320831,285.825043 
	C272.298492,285.666351 272.247986,285.505219 272.261597,285.349670 
	C272.275787,285.187439 272.302216,284.968018 272.409760,284.882874 
	C272.779480,284.590179 273.186493,284.115936 273.580353,284.114868 
	C303.220184,284.034027 332.860077,283.991974 363.000000,283.946838 
z"/>
<path fill="#5D56EC" opacity="1.000000" stroke="none" 
	d="
M403.000000,373.964600 
	C361.211456,373.964569 319.922913,373.975708 278.634399,373.926392 
	C276.877533,373.924286 275.121307,373.361969 273.364746,373.060608 
	C273.474121,372.467316 273.583496,371.873993 273.692871,371.280701 
	C334.895142,371.280701 396.097412,371.280701 457.299683,371.280701 
	C457.418152,371.820618 457.536652,372.360504 457.655121,372.900421 
	C456.085541,373.255524 454.517303,373.912140 452.946136,373.919281 
	C436.464355,373.994080 419.982117,373.964966 403.000000,373.964600 
z"/>
<path fill="#FCFBFC" opacity="1.000000" stroke="none" 
	d="
M274.781189,463.401733 
	C277.298187,463.086060 279.457947,462.965027 281.617676,462.964630 
	C338.060364,462.954285 394.503021,462.950714 450.945709,462.980255 
	C452.926178,462.981293 454.906464,463.333466 456.886841,463.522003 
	C456.908112,464.109894 456.929352,464.697784 456.950623,465.285706 
	C455.072876,465.531555 453.195343,465.990204 451.317352,465.992035 
	C409.692993,466.032928 368.068604,466.023376 326.444214,466.023376 
	C310.460449,466.023376 294.476257,466.072235 278.493561,465.940308 
	C277.041382,465.928314 274.006134,467.487976 274.781189,463.401733 
z"/>
<path fill="#AF62FC" opacity="1.000000" stroke="none" 
	d="
M522.047119,273.198669 
	C523.091797,272.465759 524.136475,271.732849 525.323608,270.705933 
	C529.880249,267.221863 534.296326,264.034241 538.707581,260.840057 
	C539.782959,260.061371 540.742188,258.902618 541.936707,258.568024 
	C543.217346,258.209320 544.712585,258.616608 546.114136,258.689636 
	C545.725586,259.944275 545.732300,261.681976 544.884644,262.375671 
	C539.104187,267.106262 533.144104,271.617310 527.046692,276.458313 
	C526.246643,277.220428 525.641846,277.723389 524.565552,278.593384 
	C517.281738,284.236267 510.283997,289.293945 503.728210,294.871155 
	C499.894409,298.132690 497.390045,297.739777 494.457245,293.926636 
	C491.734772,290.386902 488.356384,287.351349 485.637268,283.809418 
	C484.597565,282.455109 484.585632,280.311798 484.105347,278.528015 
	C485.922546,279.153168 488.199738,279.292999 489.469147,280.496124 
	C493.023193,283.864624 496.160858,287.672455 499.595398,291.452881 
	C506.815918,285.811035 513.470947,280.633972 520.088318,275.409180 
	C520.848877,274.808685 521.399292,273.941986 522.047119,273.198669 
z"/>
<path fill="#AF63FC" opacity="1.000000" stroke="none" 
	d="
M522.048401,363.973938 
	C522.946228,363.504944 523.843994,363.035919 524.926147,362.314697 
	C530.345337,357.973724 535.489136,353.758026 540.881348,349.888428 
	C542.176453,348.959045 544.296387,349.179016 546.036987,348.870361 
	C545.418518,350.414795 545.225891,352.449829 544.108704,353.419678 
	C538.969421,357.880798 533.572144,362.044647 528.124146,366.614502 
	C527.065552,367.401794 526.150024,367.889679 524.778320,368.814880 
	C517.014038,374.950470 509.669250,380.602600 502.417664,386.371887 
	C499.872253,388.396942 497.805511,388.521637 495.615173,385.881592 
	C492.128510,381.679047 488.371979,377.694763 485.006958,373.400787 
	C484.135406,372.288605 484.295135,370.368225 483.981171,368.819092 
	C485.511078,369.259766 487.492889,369.241272 488.490448,370.222931 
	C492.245239,373.917938 495.679504,377.938660 499.706055,382.355408 
	C507.320343,376.091003 514.684387,370.032471 522.048401,363.973938 
z"/>
<path fill="#AF63FC" opacity="1.000000" stroke="none" 
	d="
M522.083862,454.373535 
	C523.037109,453.848816 523.990356,453.324036 525.153320,452.536987 
	C529.820435,448.836151 534.176331,445.252716 538.781311,442.024628 
	C540.856140,440.570129 542.888672,437.209961 545.863037,440.039276 
	C546.234436,440.392578 544.028503,444.363647 542.310425,445.831909 
	C537.528259,449.918884 532.374207,453.570709 527.173706,457.659485 
	C526.355591,458.397217 525.730713,458.869781 524.666382,459.758728 
	C516.898499,465.851166 509.488007,471.426025 502.288818,477.261597 
	C499.959167,479.149963 498.358826,479.176910 496.413452,476.989899 
	C492.447815,472.531738 488.302490,468.229797 484.447418,463.680267 
	C483.732849,462.836945 484.034271,461.132629 483.866760,459.825714 
	C485.288971,460.075165 487.173401,459.835358 488.049438,460.662964 
	C491.051270,463.498596 493.538116,466.882965 496.566315,469.685242 
	C497.686157,470.721497 500.473999,471.667389 501.290253,471.050995 
	C508.377869,465.699005 515.189880,459.981964 522.083862,454.373535 
z"/>
<path fill="#D1EAEE" opacity="1.000000" stroke="none" 
	d="
M244.284897,263.488342 
	C247.479950,271.053040 250.499695,278.272858 253.750153,285.764465 
	C253.980652,286.789398 253.980423,287.542480 253.970215,288.671692 
	C253.081299,288.137207 251.844025,287.379883 251.378906,286.292297 
	C246.750015,275.468536 242.233963,264.596344 237.738037,253.716339 
	C237.365463,252.814713 237.372910,251.756073 237.203796,250.770401 
	C237.635300,250.618866 238.066803,250.467331 238.498306,250.315796 
	C240.368729,254.591690 242.239151,258.867584 244.284897,263.488342 
z"/>
<path fill="#D3D1F5" opacity="1.000000" stroke="none" 
	d="
M581.820374,488.111633 
	C578.682373,480.120605 575.544312,472.129578 572.051697,463.952515 
	C571.671753,462.274170 571.646362,460.781921 571.911987,459.080627 
	C574.638367,464.705872 577.073792,470.540131 579.690186,477.048828 
	C580.488220,479.252197 581.105286,480.781189 581.803833,482.717102 
	C581.863647,484.786530 581.841980,486.449097 581.820374,488.111633 
z"/>
<path fill="#D3D1F5" opacity="1.000000" stroke="none" 
	d="
M572.026978,543.831177 
	C575.116638,537.171387 578.242676,530.602112 581.739014,524.239258 
	C582.020325,525.650513 581.931335,526.855286 581.605469,528.299683 
	C579.173157,533.803589 576.977783,539.067932 574.782349,544.332214 
	C574.097351,544.414673 573.412354,544.497192 572.404297,544.450134 
	C572.081238,544.320557 572.063354,543.921692 572.026978,543.831177 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M571.971069,405.166931 
	C572.155762,404.905121 572.326050,404.919952 572.741455,405.010071 
	C572.883667,405.379974 572.704529,405.616882 572.245728,405.893524 
	C572.013855,405.808533 571.985291,405.626068 571.971069,405.166931 
z"/>
<path fill="#FFDB04" opacity="1.000000" stroke="none" 
	d="
M143.944992,495.966919 
	C143.944977,499.431732 143.944977,502.402802 143.944977,505.773895 
	C129.566574,505.773895 115.546967,505.773895 101.204208,505.773895 
	C101.204208,491.693665 101.204208,477.651489 101.204208,463.299438 
	C115.276611,463.299438 129.185196,463.299438 143.945007,463.299438 
	C143.945007,474.000610 143.945007,484.736908 143.944992,495.966919 
z"/>
<path fill="#3B1A59" opacity="1.000000" stroke="none" 
	d="
M207.202362,404.726929 
	C194.196091,411.636017 181.192215,418.549713 168.178696,425.445160 
	C167.171066,425.979065 166.095398,426.384583 164.720551,426.916809 
	C164.354172,412.338531 164.318558,397.693146 164.330444,382.589844 
	C181.382248,379.721954 198.386536,377.311951 215.909393,375.389282 
	C216.159698,377.871796 215.891449,379.866974 215.417068,382.164032 
	C214.078369,386.435486 212.945786,390.405090 211.472748,394.548981 
	C209.822296,398.057800 208.512329,401.392365 207.202362,404.726929 
z"/>
<path fill="#FDFEFE" opacity="1.000000" stroke="none" 
	d="
M122.282646,193.875702 
	C112.711807,179.747147 103.672035,165.622559 94.087273,151.878006 
	C90.392792,146.580154 88.118965,141.368225 88.897354,134.874863 
	C89.211586,132.253479 88.933533,129.561081 88.898483,126.431030 
	C101.834045,123.344902 114.788422,120.689568 127.760925,118.125954 
	C136.849792,116.329819 145.963394,114.658867 155.473877,113.422394 
	C155.910507,119.376350 155.475937,124.889503 156.054321,130.294266 
	C157.832535,146.911285 163.293472,162.134552 174.178848,175.365692 
	C172.632019,177.727432 167.942963,177.010666 169.387192,181.445099 
	C170.889114,186.056763 171.135300,190.479584 166.252594,193.318970 
	C170.690048,199.387299 174.779480,204.979691 178.588043,210.846130 
	C178.947266,212.922226 179.286102,214.920410 180.300674,216.478592 
	C181.690521,218.613037 184.226883,220.158844 185.133423,222.414474 
	C186.629639,226.137253 188.563614,227.612717 192.392136,226.479828 
	C192.259186,229.400925 192.117081,231.866028 191.947952,234.763794 
	C191.965149,236.955261 192.009354,238.714096 192.053574,240.472916 
	C194.059250,243.364487 196.064926,246.256058 197.987091,249.622284 
	C197.903580,250.096954 197.623398,250.373795 197.623398,250.373795 
	C190.073364,245.979706 181.909225,242.370712 175.092712,237.038864 
	C157.279282,223.105209 140.055222,208.418060 122.282646,193.875702 
z"/>
<path fill="#C7C8E7" opacity="1.000000" stroke="none" 
	d="
M311.088745,113.122864 
	C306.345917,121.646309 301.676941,130.211914 296.843689,138.683762 
	C288.717560,152.927414 280.487000,167.111496 272.162506,181.635681 
	C263.830780,191.871323 255.636902,201.791946 246.887405,211.580475 
	C246.187805,210.499863 246.043839,209.551376 245.899414,208.165070 
	C245.898987,176.229126 245.898987,144.730988 245.898987,113.122864 
	C268.229401,113.122864 289.659058,113.122864 311.088745,113.122864 
z"/>
<path fill="#FAA0FC" opacity="1.000000" stroke="none" 
	d="
M192.382965,226.023819 
	C188.563614,227.612717 186.629639,226.137253 185.133423,222.414474 
	C184.226883,220.158844 181.690521,218.613037 180.300674,216.478592 
	C179.286102,214.920410 178.947266,212.922226 178.991562,210.968521 
	C180.043350,209.844131 180.832764,208.807556 180.697067,207.910995 
	C180.326340,205.461380 179.280670,203.094727 179.074936,200.647095 
	C178.965683,199.347305 179.667328,197.210358 180.647125,196.708160 
	C189.491272,192.175034 198.478180,187.920456 207.980804,183.313889 
	C208.788956,188.169403 209.517853,192.548828 210.164978,197.287750 
	C210.749191,198.595490 211.467606,199.511902 212.067429,200.500290 
	C212.766037,201.651428 213.360886,202.865540 213.800079,204.375854 
	C213.057739,205.681763 212.196045,206.613159 212.029953,207.655411 
	C211.321548,212.100922 214.868668,217.123550 210.133896,221.107788 
	C209.807983,221.382065 210.547180,222.917633 210.785812,223.867950 
	C211.859970,228.145615 212.932251,232.423752 214.005219,236.701721 
	C206.972290,237.899612 199.939377,239.097488 192.480011,240.384155 
	C192.009354,238.714096 191.965149,236.955261 192.306915,234.716293 
	C197.477402,230.092819 202.261932,225.949509 207.046448,221.806183 
	C206.794434,221.359283 206.542404,220.912399 206.290390,220.465500 
	C201.654572,222.318268 197.018768,224.171051 192.382965,226.023819 
M195.895172,199.713226 
	C195.806396,199.664185 195.717636,199.615158 195.628876,199.566116 
	C195.751007,199.711990 195.873138,199.857864 195.979065,200.892044 
	C196.367569,204.507584 196.756073,208.123123 197.219727,212.438095 
	C199.773239,211.039108 201.291199,210.207458 203.238480,209.140610 
	C200.619720,205.830582 198.311584,202.913162 195.895172,199.713226 
z"/>
<path fill="#FAFBFD" opacity="1.000000" stroke="none" 
	d="
M213.933472,237.075668 
	C212.932251,232.423752 211.859970,228.145615 210.785812,223.867950 
	C210.547180,222.917633 209.807983,221.382065 210.133896,221.107788 
	C214.868668,217.123550 211.321548,212.100922 212.029953,207.655411 
	C212.196045,206.613159 213.057739,205.681763 214.182739,204.388367 
	C220.834824,205.489853 226.903595,206.902359 232.976227,208.763672 
	C233.016129,215.873062 233.052170,222.533661 233.088196,229.194244 
	C231.135666,230.757202 229.238449,232.396942 227.211716,233.856857 
	C225.791168,234.880081 224.041077,235.473389 222.704453,236.580261 
	C218.838150,239.781967 215.106415,243.146194 211.321854,246.446609 
	C210.903122,246.179230 210.484375,245.911865 210.065628,245.644485 
	C211.330994,242.912857 212.596359,240.181244 213.933472,237.075668 
z"/>
<path fill="#F2EDF7" opacity="1.000000" stroke="none" 
	d="
M215.623184,381.862183 
	C215.891449,379.866974 216.159698,377.871796 216.366684,375.443665 
	C216.857071,374.954224 217.408752,374.897766 218.238556,374.830750 
	C219.665588,381.932404 220.814484,389.044586 222.075180,396.556519 
	C225.219650,416.805389 228.329956,436.643188 231.217392,456.513428 
	C231.772766,460.335388 233.145767,462.868042 236.934525,464.222229 
	C239.470978,465.128845 241.748398,466.760193 244.141739,468.067230 
	C243.837891,468.633698 243.534027,469.200195 243.230164,469.766663 
	C240.048019,468.235291 236.865860,466.703918 233.175140,464.864441 
	C232.137833,464.432770 231.609116,464.309265 230.925415,463.818237 
	C230.537598,462.831451 230.191849,462.230347 230.088699,461.590179 
	C228.250427,450.181427 226.444061,438.767548 224.625839,427.355560 
	C222.418442,413.500946 220.255127,399.638794 217.922073,385.805328 
	C217.685333,384.401611 216.414261,383.172394 215.623184,381.862183 
z"/>
<path fill="#01B1D2" opacity="1.000000" stroke="none" 
	d="
M208.002075,321.903717 
	C208.966766,331.726044 209.976852,341.074799 210.863129,350.435272 
	C211.081192,352.738220 210.895660,355.079407 210.895660,357.403015 
	C210.270294,357.406921 209.644943,357.410828 209.019577,357.414734 
	C206.226440,324.545532 203.433289,291.676361 200.640152,258.807190 
	C201.333038,258.742767 202.025925,258.678345 202.718826,258.613922 
	C204.479828,279.554108 206.240829,300.494263 208.002075,321.903717 
z"/>
<path fill="#EBF0F5" opacity="1.000000" stroke="none" 
	d="
M233.374969,228.982758 
	C233.052170,222.533661 233.016129,215.873062 233.405411,208.730957 
	C237.853775,208.367249 241.876816,208.485062 245.899857,208.602890 
	C246.043839,209.551376 246.187805,210.499863 246.574249,211.713593 
	C243.134628,216.559387 239.440842,221.130600 235.779999,225.728058 
	C235.011688,226.692932 234.364624,227.754333 233.374969,228.982758 
z"/>
<path fill="#FDFEFE" opacity="1.000000" stroke="none" 
	d="
M198.044708,249.959610 
	C198.351456,250.100464 198.517075,250.378647 198.682693,250.656830 
	C198.444794,250.686600 198.206894,250.716354 197.796204,250.559952 
	C197.623398,250.373795 197.903580,250.096954 198.044708,249.959610 
z"/>
<path fill="#04C8DE" opacity="1.000000" stroke="none" 
	d="
M661.038818,458.206238 
	C664.757690,456.486023 668.644287,455.052704 672.164856,452.993500 
	C684.117188,446.002533 695.292480,437.980316 704.412964,427.391418 
	C704.903320,426.822205 705.641418,426.466461 707.207947,425.321625 
	C706.936951,427.719910 707.032715,429.164276 706.593628,430.421936 
	C696.452393,459.468781 685.830811,488.278168 668.400940,514.031860 
	C665.639893,518.111572 662.475281,520.397217 657.024292,521.065796 
	C647.872009,523.013367 639.499573,524.881775 631.035767,526.318726 
	C629.962708,521.273499 628.981018,516.659790 628.294922,511.885925 
	C646.542419,495.280060 664.494202,478.834320 682.446045,462.388580 
	C682.026123,461.938110 681.606201,461.487610 681.186279,461.037109 
	C663.207275,477.445831 645.228271,493.854523 627.077881,509.920502 
	C623.419373,500.277252 618.618347,491.282928 620.407837,480.741913 
	C620.899231,480.530304 621.041626,480.293060 621.011597,479.973236 
	C620.980286,479.975494 620.976318,480.038116 621.339417,480.038239 
	C626.651062,477.368469 631.624695,474.743652 636.541748,472.017029 
	C642.409668,468.763092 648.229187,465.421906 654.378174,461.973358 
	C656.804077,460.620880 658.921448,459.413544 661.038818,458.206238 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M620.200073,480.801117 
	C618.618347,491.282928 623.419373,500.277252 627.076355,510.177612 
	C627.497314,511.200317 627.748291,511.623199 627.999329,512.046082 
	C628.981018,516.659790 629.962708,521.273499 630.985718,526.572327 
	C631.053467,527.606445 631.079895,527.955444 631.056580,528.666382 
	C630.917542,529.506958 630.828247,529.985474 630.393555,530.468445 
	C614.827332,534.941956 599.606384,539.411072 584.255981,543.436523 
	C584.134521,535.681030 584.142578,528.369202 584.375977,520.796021 
	C589.526367,506.075775 594.451477,491.616882 599.574219,476.859985 
	C601.151123,471.602661 602.730164,466.686951 603.825684,461.665741 
	C604.375366,459.146423 605.247620,458.004578 607.942383,457.800598 
	C614.397705,457.312042 620.835571,456.552185 627.261597,455.750763 
	C628.661316,455.576172 629.982178,454.769531 631.339355,454.253967 
	C631.942871,454.180145 632.546387,454.106323 633.605469,454.007385 
	C638.386597,453.846436 642.724976,453.873718 647.034607,453.534760 
	C652.860413,453.076508 658.664062,452.336853 665.998657,451.555939 
	C663.699402,454.335388 662.396179,455.910828 661.065918,457.846252 
	C658.921448,459.413544 656.804077,460.620880 653.957031,461.900818 
	C646.054626,463.869354 638.714172,465.300629 631.762756,467.809509 
	C626.290283,469.784607 621.451782,473.222351 620.976318,480.038116 
	C620.976318,480.038116 620.980286,479.975494 620.781555,479.980530 
	C620.343933,480.205170 620.216370,480.477020 620.200073,480.801117 
z"/>
<path fill="#08C7DF" opacity="1.000000" stroke="none" 
	d="
M630.738953,530.464050 
	C630.828247,529.985474 630.917542,529.506958 631.474915,528.617004 
	C640.290527,526.338257 648.638062,524.470947 656.985596,522.603638 
	C658.442627,522.399414 659.899658,522.195190 662.413025,521.842957 
	C650.582703,537.504700 636.822998,549.494629 620.295227,558.378601 
	C626.746033,550.778442 631.105774,542.463501 630.869995,531.925415 
	C630.882202,531.243835 630.810547,530.853943 630.738953,530.464050 
z"/>
<path fill="#5E5AE2" opacity="1.000000" stroke="none" 
	d="
M630.786133,532.217041 
	C631.105774,542.463501 626.746033,550.778442 619.959229,558.457031 
	C618.301819,559.809814 616.769897,560.849060 614.962036,562.002808 
	C614.686157,556.543152 614.686157,550.968872 614.686157,544.600098 
	C606.959412,544.600098 599.927856,544.600098 592.783020,544.426758 
	C605.375183,540.241333 618.080627,536.229187 630.786133,532.217041 
z"/>
<path fill="#BAEDF1" opacity="1.000000" stroke="none" 
	d="
M657.016541,522.247925 
	C648.638062,524.470947 640.290527,526.338257 631.524658,528.255005 
	C631.079895,527.955444 631.053467,527.606445 631.077148,527.003845 
	C639.499573,524.881775 647.872009,523.013367 656.648682,521.161560 
	C657.051147,521.416138 657.049316,521.654175 657.016541,522.247925 
z"/>
<path fill="#5751EA" opacity="1.000000" stroke="none" 
	d="
M245.899414,208.165070 
	C241.876816,208.485062 237.853775,208.367249 233.401550,208.282135 
	C226.903595,206.902359 220.834824,205.489853 214.383392,204.064850 
	C213.360886,202.865540 212.766037,201.651428 212.067429,200.500290 
	C211.467606,199.511902 210.749191,198.595490 210.521591,197.168945 
	C211.630646,192.186707 212.382370,187.691925 212.891800,183.169846 
	C212.976151,182.421051 212.197952,181.225586 211.482468,180.796082 
	C208.198212,178.824539 208.899490,176.280655 210.003983,173.443466 
	C212.024277,168.253754 213.896790,163.006485 216.257812,156.630386 
	C209.435623,159.759369 203.796921,162.449646 198.073410,164.945389 
	C190.189896,168.382996 182.242294,171.673584 174.322144,175.027161 
	C163.293472,162.134552 157.832535,146.911285 156.054321,130.294266 
	C155.475937,124.889503 155.910507,119.376350 155.877487,113.442627 
	C168.537216,110.418671 181.189148,107.802834 193.868729,105.328796 
	C206.080139,102.946106 218.320114,100.709969 231.374725,98.647339 
	C232.806396,98.924736 233.410873,98.964211 233.992050,99.471649 
	C233.968750,112.117439 233.968750,124.295273 233.968750,136.473114 
	C234.428909,136.475189 234.889084,136.477280 235.349243,136.479355 
	C235.349243,124.429390 235.349243,112.379425 235.349243,99.987862 
	C263.675934,99.987862 291.321625,99.987862 319.019958,100.059334 
	C319.072662,100.130806 319.176788,100.274681 319.176788,100.274681 
	C316.672791,104.438454 314.168762,108.602219 311.376740,112.944427 
	C289.659058,113.122864 268.229401,113.122864 245.898987,113.122864 
	C245.898987,144.730988 245.898987,176.229126 245.899414,208.165070 
z"/>
<path fill="#5751DF" opacity="1.000000" stroke="none" 
	d="
M234.015350,99.003685 
	C233.410873,98.964211 232.806396,98.924736 231.753220,98.794449 
	C241.458969,96.503616 251.581467,94.140892 261.774963,92.139709 
	C279.328033,88.693687 296.925354,85.472786 314.837616,82.197891 
	C316.694977,88.078941 318.218781,93.919510 319.459686,100.017380 
	C319.176788,100.274681 319.072662,100.130806 318.852783,99.654190 
	C290.427063,99.119606 262.221191,99.061646 234.015350,99.003685 
z"/>
<path fill="#FCF7D0" opacity="1.000000" stroke="none" 
	d="
M246.734100,81.929947 
	C269.624176,77.506821 292.087982,73.141014 315.499359,68.591049 
	C312.580200,71.947655 59.453896,121.170532 52.692760,119.909241 
	C55.142269,119.044907 56.357204,118.436226 57.641708,118.188995 
	C101.412827,109.764153 145.190445,101.373024 188.967361,92.978333 
	C208.080582,89.313187 227.194336,85.650871 246.734100,81.929947 
z"/>
<path fill="#B061FB" opacity="1.000000" stroke="none" 
	d="
M331.923737,55.934631 
	C343.644592,57.091896 355.417542,58.603298 367.190491,60.114700 
	C367.301575,60.452072 367.412659,60.789444 367.523712,61.126816 
	C357.238068,66.641754 346.952393,72.156693 336.342499,77.772263 
	C334.670776,70.678185 333.323303,63.483475 331.923737,55.934631 
z"/>
<path fill="#ECEFF7" opacity="1.000000" stroke="none" 
	d="
M215.417068,382.164032 
	C216.414261,383.172394 217.685333,384.401611 217.922073,385.805328 
	C220.255127,399.638794 222.418442,413.500946 224.625839,427.355560 
	C226.444061,438.767548 228.250427,450.181427 230.088699,461.590179 
	C230.191849,462.230347 230.537598,462.831451 230.962036,464.033203 
	C231.153625,464.615662 231.334274,465.014069 231.334274,465.014069 
	C231.236465,482.415192 231.127838,499.816284 231.045547,517.217468 
	C231.006348,525.507446 231.017242,533.797668 230.561401,542.556641 
	C229.036285,543.367981 227.950409,543.696167 226.874847,544.055115 
	C219.933899,546.371277 212.995178,548.694092 206.055756,551.014771 
	C205.105042,539.774048 203.978500,528.544861 203.239914,517.290222 
	C201.663422,493.267395 200.287994,469.231354 199.000366,444.379974 
	C201.880035,433.881317 204.717880,424.236877 207.254593,414.513916 
	C208.991257,407.857483 210.308472,401.091644 211.813202,394.374695 
	C212.945786,390.405090 214.078369,386.435486 215.417068,382.164032 
z"/>
<path fill="#FEFEFF" opacity="1.000000" stroke="none" 
	d="
M206.006348,551.471191 
	C212.995178,548.694092 219.933899,546.371277 226.874847,544.055115 
	C227.950409,543.696167 229.036285,543.367981 230.488678,543.021362 
	C230.939590,563.700073 231.019211,584.382812 231.174713,605.528564 
	C231.212189,611.100891 231.173767,616.210388 231.263779,621.792786 
	C231.306946,622.654724 231.221680,623.043762 231.028381,623.711914 
	C224.473526,624.000793 218.026718,624.010498 211.076080,624.034668 
	C210.434174,623.574158 210.296082,623.099365 210.157516,622.100952 
	C210.123520,621.381287 210.090012,621.185120 210.056519,620.989014 
	C210.021225,618.946289 209.985931,616.903564 209.993286,614.142883 
	C209.896744,612.281982 209.757553,611.139099 209.618347,609.996155 
	C208.397888,590.639954 207.177429,571.283813 206.006348,551.471191 
z"/>
<path fill="#AF62FC" opacity="1.000000" stroke="none" 
	d="
M231.612030,465.012329 
	C231.334274,465.014069 231.153625,464.615662 231.117004,464.400696 
	C231.609116,464.309265 232.137833,464.432770 232.835571,464.777161 
	C232.632965,465.002167 232.261383,465.006348 231.612030,465.012329 
z"/>
<path fill="#F2EDF7" opacity="1.000000" stroke="none" 
	d="
M211.472748,394.548981 
	C210.308472,401.091644 208.991257,407.857483 207.254593,414.513916 
	C204.717880,424.236877 201.880035,433.881317 199.006104,443.932465 
	C195.815414,444.893219 192.789185,445.480713 189.762939,446.068207 
	C199.093781,442.877258 199.481049,434.465637 201.502258,427.193726 
	C203.494843,420.024841 205.306595,412.805664 207.200012,405.167725 
	C208.512329,401.392365 209.822296,398.057800 211.472748,394.548981 
z"/>
<path fill="#AF5FFC" opacity="1.000000" stroke="none" 
	d="
M209.281830,610.009033 
	C209.757553,611.139099 209.896744,612.281982 209.908844,613.713013 
	C208.694901,613.895813 207.608063,613.790527 205.244202,613.561646 
	C206.974030,611.907227 207.959671,610.964600 209.281830,610.009033 
z"/>
<path fill="#AF5FFC" opacity="1.000000" stroke="none" 
	d="
M209.826721,621.095337 
	C210.090012,621.185120 210.123520,621.381287 210.077011,621.865845 
	C209.826630,622.156799 209.656250,622.159363 209.243256,622.081543 
	C209.199402,621.734680 209.398163,621.468201 209.826721,621.095337 
z"/>
<path fill="#26235C" opacity="1.000000" stroke="none" 
	d="
M584.150635,521.057434 
	C584.142578,528.369202 584.134521,535.681030 584.174805,543.585083 
	C584.223145,544.177429 584.146667,544.504578 584.146667,544.504578 
	C583.649475,544.530090 583.152222,544.555542 582.309998,544.023438 
	C581.924133,538.330566 581.883240,533.195251 581.842407,528.059998 
	C581.931335,526.855286 582.020325,525.650513 582.010864,523.990601 
	C581.940552,512.026672 581.968750,500.517914 581.908691,488.560364 
	C581.841980,486.449097 581.863647,484.786530 581.883057,482.318176 
	C581.786438,479.976593 581.692017,478.440826 581.597656,476.905060 
	C581.597656,460.071533 581.597656,443.238007 581.597656,426.404449 
	C582.384033,426.399658 583.170471,426.394836 583.956848,426.390015 
	C583.956848,447.514160 583.956848,468.638306 583.701782,490.436829 
	C583.719788,492.957672 583.992798,494.804169 584.184814,497.142761 
	C584.165649,503.544281 584.227539,509.453674 584.204834,515.762329 
	C584.130432,517.793457 584.140564,519.425476 584.150635,521.057434 
z"/>
<path fill="#D0CEE9" opacity="1.000000" stroke="none" 
	d="
M584.576050,544.588867 
	C584.146667,544.504578 584.223145,544.177429 584.304321,544.028809 
	C599.606384,539.411072 614.827332,534.941956 630.393555,530.468445 
	C630.810547,530.853943 630.882202,531.243835 630.869995,531.925354 
	C618.080627,536.229187 605.375183,540.241333 592.324158,544.327271 
	C589.654175,544.491760 587.329773,544.582458 584.576050,544.588867 
z"/>
<path fill="#5D56EC" opacity="1.000000" stroke="none" 
	d="
M581.605469,528.299683 
	C581.883240,533.195251 581.924133,538.330566 581.961121,543.885864 
	C579.865601,544.397339 577.773865,544.488831 575.232300,544.456238 
	C576.977783,539.067932 579.173157,533.803589 581.605469,528.299683 
z"/>
<path fill="#D3D1F5" opacity="1.000000" stroke="none" 
	d="
M631.167175,454.133118 
	C629.982178,454.769531 628.661316,455.576172 627.261597,455.750763 
	C620.835571,456.552185 614.397705,457.312042 607.942383,457.800598 
	C605.247620,458.004578 604.375366,459.146423 603.825684,461.665741 
	C602.730164,466.686951 601.151123,471.602661 599.218872,476.813477 
	C598.428528,477.043976 598.191101,477.022919 597.953674,477.001892 
	C599.557373,470.429047 601.269775,463.880402 602.740295,457.277893 
	C605.218018,446.153412 607.542236,434.994751 610.238770,423.502136 
	C610.766418,423.433350 610.986145,423.712830 611.205933,423.992340 
	C609.752075,432.730164 608.303284,441.468811 606.839905,450.205048 
	C606.580688,451.752289 606.260742,453.289368 605.833801,455.544434 
	C613.060791,454.969238 619.582642,454.450165 626.700012,454.241852 
	C628.046875,454.091675 628.798279,453.630737 629.549683,453.169800 
	C630.031433,453.450653 630.513245,453.731476 631.167175,454.133118 
z"/>
<path fill="#D3D1F5" opacity="1.000000" stroke="none" 
	d="
M610.903687,417.052155 
	C611.684753,410.812225 612.465820,404.572296 613.490723,397.828369 
	C614.432983,396.695770 615.131409,396.067169 615.829834,395.438599 
	C614.904236,402.326447 613.978638,409.214325 612.669556,416.565002 
	C611.825256,417.035950 611.364502,417.044037 610.903687,417.052155 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M615.995667,395.022278 
	C615.131409,396.067169 614.432983,396.695770 613.444702,397.419098 
	C613.300354,395.130219 613.445862,392.746613 613.919556,390.077393 
	C614.915649,389.392365 615.583557,388.992920 616.530640,388.495728 
	C616.593750,390.467285 616.377625,392.536621 615.995667,395.022278 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M610.635925,417.321808 
	C611.364502,417.044037 611.825256,417.035950 612.627441,416.970825 
	C612.624512,419.207214 612.280151,421.500671 611.570862,423.893250 
	C610.986145,423.712830 610.766418,423.433350 610.281677,423.102356 
	C610.133850,421.231049 610.250977,419.411255 610.635925,417.321808 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M629.193604,453.133850 
	C628.798279,453.630737 628.046875,454.091675 627.161255,454.245789 
	C627.630493,453.658630 628.234009,453.378296 629.193604,453.133850 
z"/>
<path fill="#CECCF2" opacity="1.000000" stroke="none" 
	d="
M597.826660,477.337463 
	C598.191101,477.022919 598.428528,477.043976 599.021240,477.111511 
	C594.451477,491.616882 589.526367,506.075775 584.375977,520.796021 
	C584.140564,519.425476 584.130432,517.793457 584.447266,515.461304 
	C588.092590,508.626373 587.886658,502.590576 584.265808,496.650665 
	C583.992798,494.804169 583.719788,492.957672 583.752441,490.881104 
	C585.103821,492.957642 586.149536,495.264191 587.456543,498.204773 
	C588.142883,499.823761 588.567871,500.808746 589.315247,502.540924 
	C589.967651,500.482544 590.361694,499.239563 590.755676,497.996613 
	C593.070312,491.222107 595.385010,484.447571 597.826660,477.337463 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M581.152527,476.928528 
	C581.692017,478.440826 581.786438,479.976593 581.801575,481.911285 
	C581.105286,480.781189 580.488220,479.252197 579.844543,477.366760 
	C580.114441,476.990814 580.410950,476.971375 581.152527,476.928528 
z"/>
<path fill="#5853EB" opacity="1.000000" stroke="none" 
	d="
M590.358765,497.999969 
	C590.361694,499.239563 589.967651,500.482544 589.315247,502.540924 
	C588.567871,500.808746 588.142883,499.823761 587.640137,498.492676 
	C588.362183,498.098877 589.161987,498.051086 590.358765,497.999969 
z"/>
<path fill="#FFFEFC" opacity="1.000000" stroke="none" 
	d="
M615.061646,238.398270 
	C620.517578,226.176453 631.923401,220.024017 643.920837,222.526672 
	C656.008606,225.048172 664.496460,235.076492 664.820496,247.219604 
	C665.155273,259.765930 655.362732,271.317169 642.878601,273.102234 
	C629.851807,274.964905 617.019897,266.981354 614.395081,254.394241 
	C613.361816,249.439285 614.686523,243.992599 615.061646,238.398270 
z"/>
<path fill="#C8C8E8" opacity="1.000000" stroke="none" 
	d="
M522.021179,363.620728 
	C514.684387,370.032471 507.320343,376.091003 499.706055,382.355408 
	C495.679504,377.938660 492.245239,373.917938 488.490448,370.222931 
	C487.492889,369.241272 485.511078,369.259766 483.981171,368.819092 
	C484.295135,370.368225 484.135406,372.288605 485.006958,373.400787 
	C488.371979,377.694763 492.128510,381.679047 495.615173,385.881592 
	C497.805511,388.521637 499.872253,388.396942 502.417664,386.371887 
	C509.669250,380.602600 517.014038,374.950470 524.613953,369.093018 
	C527.031738,377.910553 525.304138,386.117157 519.334961,393.108978 
	C511.929230,401.783508 500.039154,404.841431 490.567596,401.286316 
	C478.450378,396.738159 472.466034,385.405151 472.755676,375.139008 
	C473.041656,365.002869 480.590942,354.163055 489.973236,351.171021 
	C501.589478,347.466614 514.557556,351.481659 520.736572,360.570007 
	C521.287109,361.379822 521.580933,362.364197 522.021179,363.620728 
z"/>
<path fill="#C8C8E8" opacity="1.000000" stroke="none" 
	d="
M522.018127,272.828552 
	C521.399292,273.941986 520.848877,274.808685 520.088318,275.409180 
	C513.470947,280.633972 506.815918,285.811035 499.595398,291.452881 
	C496.160858,287.672455 493.023193,283.864624 489.469147,280.496124 
	C488.199738,279.292999 485.922546,279.153168 484.105347,278.528015 
	C484.585632,280.311798 484.597565,282.455109 485.637268,283.809418 
	C488.356384,287.351349 491.734772,290.386902 494.457245,293.926636 
	C497.390045,297.739777 499.894409,298.132690 503.728210,294.871155 
	C510.283997,289.293945 517.281738,284.236267 524.407715,278.843567 
	C529.375427,294.294586 517.441589,308.041046 506.240631,310.973572 
	C491.793945,314.755798 477.927063,306.798309 473.635132,292.318420 
	C470.609711,282.111389 474.830688,270.333740 483.898621,263.680603 
	C492.060425,257.692291 504.091522,257.019409 512.417297,262.806610 
	C516.076355,265.349976 518.826355,269.201111 522.018127,272.828552 
z"/>
<path fill="#C8C8E8" opacity="1.000000" stroke="none" 
	d="
M522.046875,453.992798 
	C515.189880,459.981964 508.377869,465.699005 501.290253,471.050995 
	C500.473999,471.667389 497.686157,470.721497 496.566315,469.685242 
	C493.538116,466.882965 491.051270,463.498596 488.049438,460.662964 
	C487.173401,459.835358 485.288971,460.075165 483.866760,459.825714 
	C484.034271,461.132629 483.732849,462.836945 484.447418,463.680267 
	C488.302490,468.229797 492.447815,472.531738 496.413452,476.989899 
	C498.358826,479.176910 499.959167,479.149963 502.288818,477.261597 
	C509.488007,471.426025 516.898499,465.851166 524.517822,460.014893 
	C528.541809,470.793182 521.856506,484.218903 512.392456,489.806396 
	C502.829041,495.452576 489.623627,494.140167 480.577728,485.625153 
	C468.282501,474.051514 469.790833,450.243164 489.023224,442.213745 
	C501.781860,436.887024 515.202148,441.848877 522.046875,453.992798 
z"/>
<path fill="#0530B9" opacity="1.000000" stroke="none" 
	d="
M174.178848,175.365692 
	C182.242294,171.673584 190.189896,168.382996 198.073410,164.945389 
	C203.796921,162.449646 209.435623,159.759369 216.257812,156.630386 
	C213.896790,163.006485 212.024277,168.253754 210.003983,173.443466 
	C208.899490,176.280655 208.198212,178.824539 211.482468,180.796082 
	C212.197952,181.225586 212.976151,182.421051 212.891800,183.169846 
	C212.382370,187.691925 211.630646,192.186707 210.603363,196.809433 
	C209.517853,192.548828 208.788956,188.169403 207.980804,183.313889 
	C198.478180,187.920456 189.491272,192.175034 180.647125,196.708160 
	C179.667328,197.210358 178.965683,199.347305 179.074936,200.647095 
	C179.280670,203.094727 180.326340,205.461380 180.697067,207.910995 
	C180.832764,208.807556 180.043350,209.844131 179.272430,210.694458 
	C174.779480,204.979691 170.690048,199.387299 166.252594,193.318970 
	C171.135300,190.479584 170.889114,186.056763 169.387192,181.445099 
	C167.942963,177.010666 172.632019,177.727432 174.178848,175.365692 
z"/>
<path fill="#452462" opacity="1.000000" stroke="none" 
	d="
M192.392136,226.479828 
	C197.018768,224.171051 201.654572,222.318268 206.290390,220.465500 
	C206.542404,220.912399 206.794434,221.359283 207.046448,221.806183 
	C202.261932,225.949509 197.477402,230.092819 192.333923,234.283630 
	C192.117081,231.866028 192.259186,229.400925 192.392136,226.479828 
z"/>
<path fill="#FC79F1" opacity="1.000000" stroke="none" 
	d="
M196.003448,199.995728 
	C198.311584,202.913162 200.619720,205.830582 203.238480,209.140610 
	C201.291199,210.207458 199.773239,211.039108 197.219727,212.438095 
	C196.756073,208.123123 196.367569,204.507584 195.989532,200.446014 
	C196.000000,200.000000 196.003448,199.995728 196.003448,199.995728 
z"/>
<path fill="#FC79F1" opacity="1.000000" stroke="none" 
	d="
M195.997635,200.001862 
	C195.873138,199.857864 195.751007,199.711990 195.628876,199.566132 
	C195.717636,199.615158 195.806396,199.664185 195.949310,199.854477 
	C196.003448,199.995728 196.000000,200.000000 195.997635,200.001862 
z"/>
<path fill="#DDE9F4" opacity="1.000000" stroke="none" 
	d="
M621.339417,480.038239 
	C621.451782,473.222351 626.290283,469.784607 631.762756,467.809509 
	C638.714172,465.300629 646.054626,463.869354 653.648438,462.045959 
	C648.229187,465.421906 642.409668,468.763092 636.541748,472.017029 
	C631.624695,474.743652 626.651062,477.368469 621.339417,480.038239 
z"/>
<path fill="#BAEDF1" opacity="1.000000" stroke="none" 
	d="
M628.294922,511.885925 
	C627.748291,511.623199 627.497314,511.200317 627.247803,510.520325 
	C645.228271,493.854523 663.207275,477.445831 681.186279,461.037109 
	C681.606201,461.487610 682.026123,461.938110 682.446045,462.388580 
	C664.494202,478.834320 646.542419,495.280060 628.294922,511.885925 
z"/>
<path fill="#DDE9F4" opacity="1.000000" stroke="none" 
	d="
M620.407837,480.741913 
	C620.216370,480.477020 620.343933,480.205170 620.812866,479.978271 
	C621.041626,480.293060 620.899231,480.530304 620.407837,480.741913 
z"/>
<path fill="#09081B" opacity="1.000000" stroke="none" 
	d="
M233.992050,99.471649 
	C262.221191,99.061646 290.427063,99.119606 318.800110,99.582718 
	C291.321625,99.987862 263.675934,99.987862 235.349243,99.987862 
	C235.349243,112.379425 235.349243,124.429390 235.349243,136.479355 
	C234.889084,136.477280 234.428909,136.475189 233.968750,136.473114 
	C233.968750,124.295273 233.968750,112.117439 233.992050,99.471649 
z"/>
<path fill="#5751EA" opacity="1.000000" stroke="none" 
	d="
M584.184814,497.142761 
	C587.886658,502.590576 588.092590,508.626373 584.531860,515.062134 
	C584.227539,509.453674 584.165649,503.544281 584.184814,497.142761 
z"/>
</svg>
				</div>
				
				<h1><?php echo wp_kses_post( $content['title'] ); ?></h1>
				<p class="onboarding_complete_content"><?php echo wp_kses_post( $content['desc'] ); ?></p>
			</div>

			<?php
			/**
			 * WPHOOK: Action -> Fires after final step contents.
			 *
			 * @param string $prefix The onboarding prefix.
			 * @since 1.0
			 */
			do_action( 'hzfex_onboarding_after_final_step_contents', $this->prefix );
		}

		/**
		 * Queues the background silent installation of a recommended plugin.
		 *
		 * @param string $plugin_id   Plugin id used for background install.
		 * @param array  $plugin_info Plugin info array containing.
		 * * `string` `name` - plugin name/title,
		 * * `string` `slug` - plugin's `slug` on wordpress.org repsitory, and
		 * * `string` `file` - plugin's main file-name if different from `[slug].php`, if different from plugin's slug.
		 * @param bool   $activate Whether to activate plugin after installation or not.
		 *
		 * @since 1.0
		 */
		protected function install_plugin( $plugin_id, $plugin_info, $activate = false ) {
			// Make sure we don't trigger multiple simultaneous installs.
			if ( get_option( $this->prefix . '_silent_installing_' . $plugin_id ) ) {
				return;
			}

			// Hook silent installation at WordPress shutdown.
			if ( empty( $this->deferred_actions ) ) {
					add_action( 'shutdown', array( $this, 'run_deferred_actions' ) );
			}

			array_push(
				$this->deferred_actions,
				array(
					'func' => array( '\TheWebSolver', 'silent_plugin_installer' ),
					'args' => array( $plugin_id, $plugin_info, $activate ),
				)
			);

			// Set the background installation flag for this plugin.
			update_option( $this->prefix . '_silent_installing_' . $plugin_id, true );
		}

		/**
		 * Defers action execution.
		 *
		 * It is called after the HTTP request is finished,
		 * so it's executed without the client having to wait for it.
		 * In another words, it runs at WordPress `shutdown` action hook.
		 *
		 * @since 1.0
		 */
		public function run_deferred_actions() {
			$this->close_http_connection();

			// Get all recommended plugins active status.
			$plugins_status = get_option( $this->prefix . '_get_onboarding_recommended_plugins_status', array() );

			// Iterate over deferred actions and run them one by one at shutdown.
			foreach ( $this->deferred_actions as $action ) {
				// Call TheWebSolver::silent_plugin_installer() and it's args.
				$response = call_user_func_array( $action['func'], $action['args'] );

				if (
					isset( $action['func'][1] ) &&
					'silent_plugin_installer' === $action['func'][1] &&
					isset( $action['args'][0] )
				) {
					/**
					 * Clear the background installation flag for current plugin.
					 *
					 * This is to restart installation/activation process of the plugin
					 * if wizard recommended plugin step is run again.
					 *
					 * {@see @method `Wizard::install_plugin()`}
					 */
					delete_option( $this->prefix . '_silent_installing_' . $action['args'][0] );

					/**
					 * Set activation status of recommended plugin.
					 *
					 * This is to prevent being stuck on active status
					 * even though the plugin may have been
					 * activated/deactivated outside the scope of onboarding.
					 *
					 * {@see @method `Wizard::recommended_view()`}
					 */
					$plugins_status[ $action['args'][0] ] = true === $response ? 'true' : 'false';
				}
			}

			// Finally, update the recommended plugins active status from callback response.
			update_option( $this->prefix . '_get_onboarding_recommended_plugins_status', $plugins_status );
		}

		/**
		 * Registers necessary styles/scripts.
		 *
		 * @since 1.0
		 */
		protected function register_scripts() {
			// Scripts.
			wp_register_script( 'hzfex_select2', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js', array( 'jquery' ), '4.0.13', true );

			/**
			 * WPHOOK: Filter -> Onboarding registered scripts.
			 *
			 * Use this filter to add own scripts to onboarding wizard.
			 *
			 * @param array  $handles The already registered script handles.
			 * @param string $prefix  The onboarding prefix.
			 * @var array
			 * @since 1.0
			 * @example usage
			 * ```
			 * add_filter( 'hzfex_register_onboarding_scripts', 'dependency_scripts', 10, 2 );
			 * function dependency_scripts( array $handles, string $prefix ): array {
			 *  // Check if is our onboarding.
			 *  if ( 'my-prefix' !== $prefix ) {
			 *   return $handles;
			 *  }
			 *
			 *  // Register new script. (NO NEED TO ENQUEUE IT).
			 *  wp_register_script( 'my-new-script', 'path/to/my-new-script.js', array(), '1.0', false );
			 *
			 * // Then add the newly registered script handle to the $handles.
			 *  $handles[] = 'my-new-script';
			 *
			 *  // Return all the dependency handles including newly registered above.
			 *  return $handles;
			 * }
			 *```
			*/
			$script_handles = apply_filters( 'hzfex_register_onboarding_scripts', array( 'jquery', 'hzfex_select2' ), $this->prefix );

			wp_register_script( 'onboarding_script', $this->config->get_url() . 'Assets/onboarding.js', $script_handles, '1.0', false );

			$nonce_key    = $this->prefix . '_install_dep_key';
			$nonce_action = $this->prefix . '_install_dep_action';
			$ajax_action  = $this->prefix . '_silent_plugin_install';

			wp_localize_script(
				'onboarding_script',
				'tws_ob',
				array(
					'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
					'successText' => __( 'installed successfully.', 'tws-onboarding' ),
					'successNext' => __( 'Continue Next Step', 'tws-onboarding' ),
					'successStar' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon_stars" width="70" height="25" viewBox="0 0 20 30" stroke-width="2.5" stroke="#d84315" fill="none" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
					<path transform="translate(280, 470) rotate(-3.000000) translate(-280, -480)" d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
					<path transform="translate(280, 499) rotate(3.000000) translate(-280, -480)" d="M12 17.75l-6.172 3.245 1.179-6.873-4.993-4.867 6.9-1.002L12 2l3.086 6.253 6.9 1.002-4.993 4.867 1.179 6.873z"></path>
					</svg>',
					'errorText'   => __( 'installation error.', 'tws-onboarding' ),
					'ajaxdata'    => array(
						$nonce_key  => wp_create_nonce( $nonce_action ),
						'action'    => $ajax_action,
						'slug'      => $this->slug,
						'file'      => $this->filename ? $this->filename : $this->slug,
						'version'   => $this->version,
						'name'      => $this->name,
						'prefix'    => $this->prefix,
						'installed' => $this->is_installed,
					),
					'recommended' => array(
						'single' => __( 'Plugin', 'tws-onbaording' ),
						'plural' => __( 'Plugins', 'tws-onboarding' ),
						'suffix' => __( 'will be installed and/or activated', 'tws-onboarding' ),
						'select' => __( 'Select one or more plugins above to install/activate', 'tws-onboarding' ),
						'check'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="hz_ob_check"><g class="paths"><path fill="currentColor" d="M504.5 144.42L264.75 385.5 192 312.59l240.11-241a25.49 25.49 0 0 1 36.06-.14l.14.14L504.5 108a25.86 25.86 0 0 1 0 36.42z" class="secondary"></path><path fill="currentColor" d="M264.67 385.59l-54.57 54.87a25.5 25.5 0 0 1-36.06.14l-.14-.14L7.5 273.1a25.84 25.84 0 0 1 0-36.41l36.2-36.41a25.49 25.49 0 0 1 36-.17l.16.17z" class="primary"></path></g></svg>',
					),
					'selectPlh'   => __( 'Select Options', 'tws-onboarding' ),
				),
			);

			// Styles and fonts.
			wp_register_style( 'hzfex_select2_style', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css', array(), '4.0.13', 'all' );

			$style_handles = apply_filters( 'hzfex_register_onboarding_styles', array( 'hzfex_select2_style' ), $this->prefix );
			wp_register_style( 'onboarding_style', $this->config->get_url() . 'Assets/onboarding.css', $style_handles, '1.0' );

			/**
			 * WPHOOK: Action -> fires after enqueue onboarding styles and scripts.
			 *
			 * @param array $handles The registered scripts and styles for onboarding wizard.
			 * @param string $prefix The onboarding prefix.
			 * @since 1.0
			 */
			do_action(
				'hzfex_onboarding_register_scripts',
				array(
					'script' => $script_handles,
					'style'  => $style_handles,
				),
				$this->prefix
			);
		}

		/**
		 * Validates nonce before saving.
		 *
		 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between 0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
		 *
		 * @since 1.0
		 */
		protected function validate_save() {
			return check_admin_referer( 'hzfex-onboarding' );
		}

		/**
		 * Gets onboarding step action buttons.
		 *
		 * @param bool   $prev        The previous step button.
		 * @param bool   $next        The next step button.
		 * @param bool   $skip        The skip step button.
		 * @param string $submit_text The submit button text.
		 *
		 * @since 1.0
		 */
		public function get_step_buttons( $prev = false, $next = true, $skip = true, $submit_text = '' ) {
			$submit = '' === $submit_text ? __( 'Save & Continue' ) : $submit_text;
			?>
			<!-- onboarding-actions -->
			<fieldset class="onboarding-actions step <?php echo esc_attr( $this->step ); ?> hz_flx column center">
				<?php if ( $prev ) : ?>
					<a href="<?php echo esc_url( $this->get_previous_step_link() ); ?>" class="button button-large button-prev hz_btn__prev hz_btn__prim hz_btn__nav">← <?php esc_html_e( 'Previous Step', 'tws-onboarding' ); ?></a>
				<?php endif; ?>
				<?php if ( $skip ) : ?>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next hz_btn__skip hz_btn__nav"><?php esc_html_e( 'Skip this Step', 'tws-onboarding' ); ?></a>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<input type="submit" class="button-primary button button-large button-next hz_btn__prim" value="<?php echo esc_attr( $submit ); ?> →" />
					<?php
					wp_nonce_field( 'hzfex-onboarding' );

					/**
					 * Without this hidden input field, save function call will not trigger.
					 *
					 * {@see @method Wizard::start()}
					 */
					?>
					<input type="hidden" name="save_step" value="save_step">
				<?php endif; ?>
			</fieldset>
			<!-- .onboarding-actions -->
			<?php
		}

		/**
		 * Gets the current onboarding step.
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_step() {
			return $this->step;
		}

		/**
		 * Gets all onboarding steps.
		 *
		 * @return array
		 *
		 * @since 1.0
		 */
		public function get_steps() {
			return $this->steps;
		}

		/**
		 * Gets the previous step in queue.
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_previous_step_link() {
			$steps = array_keys( $this->steps );
			$index = array_search( $this->step, $steps, true );

			return add_query_arg( 'step', $steps[ $index - 1 ] );
		}

		/**
		 * Gets the next step in queue.
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public function get_next_step_link() {
			$steps = array_keys( $this->steps );
			$index = array_search( $this->step, $steps, true );

			return add_query_arg( 'step', $steps[ $index + 1 ] );
		}

		/**
		 * Gets active status and sets bool value as string.
		 *
		 * @param string $basename The plugin basename.
		 *
		 * @return string true if active, false otherwise.
		 *
		 * @since 1.0
		 */
		private function get_active_status( $basename ) {
			return TheWebSolver::maybe_plugin_is_active( $basename ) ? 'true' : 'false';
		}

		/**
		 * Finishes replying to the client, but keeps the process running for further (async) code execution.
		 *
		 * @see https://core.trac.wordpress.org/ticket/41358.
		 */
		protected function close_http_connection() {
			// Only 1 PHP process can access a session object at a time, close this so the next request isn't kept waiting.
			if ( session_id() ) {
				session_write_close();
			}

			if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
				@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}

			// fastcgi_finish_request is the cleanest way to send the response and keep the script running, but not every server has it.
			if ( is_callable( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			} else {
				// Fallback: send headers and flush buffers.
				if ( ! headers_sent() ) {
						header( 'Connection: close' );
				}
				@ob_end_flush(); // phpcs:ignore WordPress.PHP.NoSilencedErrors
				flush();
			}
		}

		/**
		 * Sets introduction page image.
		 *
		 * @since 1.0
		 * @since 1.1 Moved onboarding intro page hero image to a template file.
		 */
		protected function introduction_image() {
			TheWebSolver::get_template( 'onboarding-image.php', array(), '', $this->config->get_path() . 'templates/' );
		}
	}
}
