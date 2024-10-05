<?php
namespace CIXW_WISHLIST;

class Bootstrap {

	protected $wishlist_slug;
	/**
	 * The unique instance of the plugin.
	 */
	private static $instance;

	/**
	 * Gets an instance of our plugin.
	 *
	 * @return Class Instance.
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->load_classes();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'display_post_states', array( $this, 'wishlist_page_state' ), 10, 2 );
		add_action( 'admin_init', array( 'PAnD', 'init' ) );
	}
	protected function load_classes() {
		AjaxActions::init();
		Wishlist::init();
		Settings::init();
	}


	/**
	 * Adds the "Wishlist Page" state to the post states array.
	 *
	 * This function is used to modify the post states array by adding the "Wishlist Page" state
	 * if the current post ID matches the wishlist page ID set in the plugin options.
	 *
	 * @param array   $states The array of post states.
	 * @param WP_Post $post   The current post object.
	 * @return array The modified array of post states.
	 */
	public function wishlist_page_state( $states, $post ) {
		if ( $post->ID == cixww_get_option( 'wishlist_page' ) ) {
			$states[] = __( 'Wishlist Page', 'jvm-woocommerce-wishlist' );
		}
		return $states;
	}
	public function enqueue_scripts() {
		$wishlist_popup = cixww_get_option( 'product_button_action' );
		$js_deps        = array( 'jquery', 'cix-cookie' );
		if ( $wishlist_popup == 'popup' || get_the_id() == cixww_get_option( 'wishlist_page' ) ) {
			wp_enqueue_style( 'cix-wishlist-modal', CIXWW_PLUGIN_URL . 'assets/css/jquery.modal.min.css', array(), CIXWW_PLUGIN_VER, 'all' );
			wp_enqueue_script( 'cix-wishlist-modal', CIXWW_PLUGIN_URL . 'assets/js/jquery.modal.min.js', array( 'jquery' ), CIXWW_PLUGIN_VER, true );

			$js_deps = array( 'jquery', 'cix-wishlist-modal' );
		}
		wp_enqueue_script( 'cix-cookie', CIXWW_PLUGIN_URL . 'assets/js/js.cookie.min.js', array(), CIXWW_PLUGIN_VER, true );
		wp_enqueue_script( 'cix-wishlist', CIXWW_PLUGIN_URL . 'assets/js/wishlist.js', $js_deps, CIXWW_PLUGIN_VER, true );

		wp_localize_script(
			'cix-wishlist',
			'cix_wishlist_args',
			array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'cix-wishlist-nonce' ),
				'logged_in'      => is_user_logged_in(),
				'wishlist_count' => Wishlist::count_items(),
				'guest_notice'   => Helper::replace_text( cixww_get_option( 'guest_notice' ), '{guest_session_in_days}' ),

			)
		);
		wp_enqueue_style( 'cix-wishlist', CIXWW_PLUGIN_URL . 'assets/css/wishlist.css', array(), CIXWW_PLUGIN_VER );
		// add inline css
		$i_color = cixww_get_option( 'product_button_txt_color' );
		$css     = cixww_get_option( 'wishlist_css' );
		if ( cixww_get_option( 'loop_button_position' ) == 'in_thumb' ) {
			$css .= '.archive .jvm_add_to_wishlist{position: absolute;
				top: 5px;
				left: 5px;
			}
			.archive .jvm_add_to_wishlist.btn-link{
				top: 10px;
			}';
		}
		if ( $i_color ) {
			$css .= '.in_wishlist.jvm_add_to_wishlist span{
				color: ' . $i_color['active'] . ' !important;
			}
			.jvm_add_to_wishlist:hover span{
				color: ' . $i_color['hover'] . ' !important;
			}';
		}
		if ( cixww_get_option( 'product_button_text' ) ) {
			$css .= '.jvm_add_to_wishlist_heart{
				margin-right: 5px;
				}';
		}
		wp_add_inline_style( 'cix-wishlist', $css );
	}


	public static function activation() {

		$plugin_opt = get_option( 'cixwishlist_settings' );
		if ( ! $plugin_opt ) {
			update_option( 'cixwishlist_settings', Settings::defaults() );
		}
	}
}
