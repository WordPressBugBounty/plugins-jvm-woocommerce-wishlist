<?php
namespace CIXW_WISHLIST;

class Wishlist {
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
	private function __construct() {
		add_shortcode( 'jvm_woocommerce_wishlist', array( $this, 'wishlist_shortcode' ) );
		add_shortcode( 'jvm_add_to_wishlist', array( $this, 'add_to_wishlist_shortcode' ) );

		$this->display_loop_wishlist_button();
		$this->display_single_product_wishlist_button();
		add_action( 'init', array( $this, 'create_wishlist_page' ) );
		add_action( 'wp_footer', array( $this, 'wishlist_popup_html' ) );
		add_filter( 'cix_replace_text_list', array( $this, 'replace_info' ), 10, 2 );
		add_action( 'wp_login', array( $this, 'merge_wishlists' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'wishlist_page_notice' ) );
		add_action( 'wp_loaded', array( $this, 'add_to_wishlist_action' ) );
	}
	/**
	 * Adds a product to the wishlist and performs necessary actions.
	 *
	 * @param bool|string $url The URL to redirect to after adding the product to the wishlist.
	 * @return void
	 */
	public function add_to_wishlist_action( $url = false ) {
		if ( ! isset( $_REQUEST['add-to-wishlist'] ) || ! is_numeric( wp_unslash( $_REQUEST['add-to-wishlist'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		wc_nocache_headers();

		$product_id            = apply_filters( 'woocommerce_add_to_wishlist_product_id', absint( wp_unslash( $_REQUEST['add-to-wishlist'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$was_added_to_wishlist = false;
		$adding_to_wishlist    = wc_get_product( $product_id );

		if ( ! $adding_to_wishlist ) {
			return;
		}
		self::set_product( $product_id );

		$message = '<a href="' . esc_url( get_the_permalink( cixww_get_option( 'wishlist_page' ) ) ) . '" class="button wc-forward">' . esc_html( cixww_get_option( 'product_view_wishlist_text' ) ) . '</a>' . self::added_to_wishlist_text( $product_id );
		wc_add_notice( $message, 'success' );

		// If we added the product to the cart we can now optionally do a redirect.
		if ( $was_added_to_wishlist && 0 === wc_notice_count( 'error' ) ) {
			$url = apply_filters( 'woocommerce_add_to_wishlist', $url, $adding_to_wishlist );

			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			}
			// TODO: add redirect link after wihsist by url
			// elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			// wp_safe_redirect( wc_get_cart_url() );
			// exit;
			// }
		}
	}
	/**
	 * Displays a notice on the admin dashboard if the Wishlist Page is not set up properly.
	 *
	 * @since 2.0.2
	 */
	public function wishlist_page_notice() {
		if ( ! empty( cixww_get_option( 'wishlist_page' ) ) ) {
			return;
		}
		?>
	<div class="error notice notice-error">
		<h4>Ensure that the Wishlist Page is set up in order for the plugin to work properly.</h4>
		<ol>
			<li>To show the Wishlist, just create a new page or open an existing one.</li>
			<li>Add the <code>[jvm_woocommerce_wishlist]</code> shortcode into the page content.</li>
			<li>Go to the <a href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=cixwishlist_settings' ) ); ?>"><strong>Wishlist Settings</strong></a> and select the page for the "Wishlist Page" option.</li>
		</ol>

	</div>
		<?php
	}
	/**
	 * Creates a wishlist page if it doesn't already exist.
	 *
	 * This method checks if the wishlist page option is set to 0. If it is, it creates a new page with the title "Wishlist" and the shortcode "[jvm_woocommerce_wishlist]".
	 *
	 * @return void
	 */
	public function create_wishlist_page() {

		$cixwishlist_settings = get_option( 'cixwishlist_settings' );

		if ( cixww_get_option( 'create_wishlist_page' ) == 'gen_page' ) {

			$page_id = Helper::create_page( 'Wishlist', '[jvm_woocommerce_wishlist]' );

			$cixwishlist_settings['wishlist_page'] = $page_id;

		} else {
			$cixwishlist_settings['wishlist_page'] = ( cixww_get_option( 'create_wishlist_page' ) ) ? cixww_get_option( 'create_wishlist_page' ) : $cixwishlist_settings['wishlist_page'];
		}

		// Update the option
		update_option( 'cixwishlist_settings', $cixwishlist_settings );
	}

	/**
	 * Merge the user's wishlist with the guest wishlist.
	 *
	 * This function merges the user's wishlist with the guest wishlist and updates the user meta.
	 *
	 * @return void
	 */
	public function merge_wishlists( $user_login, \WP_User $user ) {

		$user_id = $user->ID;
		// add user wishlist to user meta
		$wishlist = ( get_user_meta( $user_id, 'cix_default_wc_wishlist', true ) ) ? get_user_meta( $user_id, 'cix_default_wc_wishlist', true ) : array();

		$guest_wishlist = ( get_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() ) ) ? get_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() ) : array();

		$merge_wishlist = array_unique( array_merge( $wishlist, $guest_wishlist ) );
		update_user_meta( $user_id, 'cix_default_wc_wishlist', $merge_wishlist );

		// delete guest wishlist
		delete_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() );

		do_action( 'cix_woocommerce_wishlist_after_merge_wishlists', $user_id, $wishlist );
	}
	/**
	 * Generates the HTML for the wishlist popup.
	 *
	 * This function checks the value of the 'product_button_action' option and the current post ID to determine if the wishlist popup should be displayed. If the value is 'popup' or the current post ID matches the 'wishlist_page' option, the wishlist modal is embedded in the page.
	 */
	public function wishlist_popup_html() {
		$wishlist_popup = cixww_get_option( 'product_button_action' );
		if ( $wishlist_popup == 'popup' || get_the_id() == cixww_get_option( 'wishlist_page' ) ) {
			?>
			<!-- wishlist modal embedded in page -->
			<div id="wishlist-modal" class="modal"></div>
			<?php
		}
	}
	/**
	 * Display wishlist button on single product
	 */
	public function display_single_product_wishlist_button() {

		if ( cixww_get_option( 'product_button' ) == 1 && cixww_get_option( 'product_button_position' ) == 'after' ) {
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'woocommerce_add_to_wishlist' ), 10 );
		} elseif ( cixww_get_option( 'product_button' ) == 1 && cixww_get_option( 'product_button_position' ) == 'before' ) {
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'woocommerce_add_to_wishlist' ), 5 );
		} elseif ( cixww_get_option( 'product_button' ) == 1 && cixww_get_option( 'product_button_position' ) == 'after_summary' ) {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'woocommerce_add_to_wishlist' ), 10 );
		}
	}
	/**
	 * Display wishlist button on loop
	 */
	public function display_loop_wishlist_button() {
		if ( cixww_get_option( 'loop_button' ) == 1 && cixww_get_option( 'loop_button_position' ) == 'after' ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woocommerce_add_to_wishlist' ), 10 );
		} elseif ( cixww_get_option( 'loop_button' ) == 1 && cixww_get_option( 'loop_button_position' ) == 'before' ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woocommerce_add_to_wishlist' ), 5 );
		} elseif ( cixww_get_option( 'loop_button' ) == 1 && cixww_get_option( 'loop_button_position' ) == 'in_thumb' ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woocommerce_add_to_wishlist' ), 10 );
		}
	}
	/**
	 * Render wishlist shortcode
	 */
	public function add_to_wishlist_shortcode() {

		ob_start();
		self::woocommerce_add_to_wishlist();
		return ob_get_clean();
	}

	/**
	 * Render wishlist shortcode
	 */
	public function wishlist_shortcode() {

		ob_start();
		do_action( 'cix_woocommerce_wishlist_before_wishlist' );
		self::woocommerce_wishlist_locate_template( 'wishlist.php' );
		do_action( 'cix_woocommerce_wishlist_after_wishlist' );
		return ob_get_clean();
	}
	/**
	 * Render wishlist popup for added item
	 */
	public static function wishlist_popup( $args = array() ) {

		ob_start();
		self::woocommerce_wishlist_locate_template( 'wishlist-popup.php', $args );
		return ob_get_clean();
	}
	/**
	 * Render wishlist loop items
	 */
	public static function wishlist_loop_items( $args = array() ) {

		ob_start();
		self::woocommerce_wishlist_locate_template( 'wishlist-loop-item.php', $args );
		return ob_get_clean();
	}

	/**
	 * Locates and includes a template file for the WooCommerce Wishlist plugin.
	 *
	 * @param string $path The path of the template file.
	 * @param mixed  $params Optional parameters to be passed to the template.
	 * @return void
	 */
	public static function woocommerce_wishlist_locate_template( $path, $args = array() ) {
		$located = locate_template( array( 'wishlist' . DIRECTORY_SEPARATOR . $path ) );

		$plugin_path = CIXWW_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $path;

		if ( ! $located ) {
			$final_file = $plugin_path;
		} else {
			$final_file = $located;
		}

		include $final_file;
	}

	/**
	 * Sets a product in the wishlist.
	 *
	 * This function adds a product to the wishlist by updating the wishlist array and storing it in user meta if the user is logged in,
	 * or storing it in a transient if the user is not logged in.
	 *
	 * @param int $product_id The ID of the product to be added to the wishlist.
	 * @return void
	 */
	public static function set_product( $product_id ) {
		$wishlist   = self::wishlist_product_ids();
		$wishlist[] = $product_id;
		$wishlist   = array_unique( $wishlist );

		// if user is logged in, add to user meta
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'cix_default_wc_wishlist', $wishlist );
		} else {

			$expiration = DAY_IN_SECONDS * cixww_get_option( 'guest_wishlist_delete', 30 ); // 30 days
			set_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id(), $wishlist, $expiration );
		}
	}
	/**
	 * Get a PHP array of products in the wishlist
	 */
	public static function wishlist_product_ids( $product_ids = array(), $wishlist_id = null ) {

		// if user is logged in, get the wishlist from user meta
		if ( is_user_logged_in() ) {
			$user_id  = get_current_user_id();
			$wishlist = ( get_user_meta( $user_id, 'cix_default_wc_wishlist', true ) ) ? get_user_meta( $user_id, 'cix_default_wc_wishlist', true ) : array();

		} else {
			$wishlist = ( get_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() ) ) ? get_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() ) : array();
		}

		return $wishlist;
	}
	// add count_items method to Wishlist class to count the number of items in the wishlist
	/**
	 * Count the number of items in the wishlist.
	 *
	 * This function counts the number of items in the wishlist by calling the wishlist_product_ids method and returning the count of the array.
	 *
	 * @return int The number of items in the wishlist.
	 */
	public static function count_items() {
		$wishlist = self::wishlist_product_ids();
		return is_array( $wishlist ) ? count( $wishlist ) : 0;
	}


	/**
	 * Get the temporary wishlist ID from the cookie value.
	 */
	public static function get_wishlist_temp_id() {
		$cookie_name = 'cix_wc_wishlist_temp';
		$cookie      = ( isset( $_COOKIE[ $cookie_name ] ) ) ? $_COOKIE[ $cookie_name ] : null;
		return $cookie;
	}

	/**
	 * Removes a product from the wishlist.
	 *
	 * @param int      $product_id   The ID of the product to be removed.
	 * @param int|null $wishlist_id  The ID of the wishlist. If null, the default wishlist is used.
	 * @return void
	 */
	public static function remove_product( $product_id, $wishlist_id = null ) {
		if ( $product_id ) {
			$wishlist = self::wishlist_product_ids();

			$wishlist = array_diff( $wishlist, array( $product_id ) );
			$wishlist = array_unique( $wishlist );

			// if user is logged in, remove from user meta
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				update_user_meta( $user_id, 'cix_default_wc_wishlist', $wishlist );
			} else {
				$expiration = DAY_IN_SECONDS * cixww_get_option( 'guest_wishlist_delete', 30 ); // 30 days
				set_transient( 'cix_wc_wishlist_' . self::get_wishlist_temp_id(), $wishlist, $expiration );

			}
		}
	}

	/**
	 * Adds or removes a product from the wishlist and display button HTML
	 *
	 * @param int|null $product_id The ID of the product to add or remove from the wishlist. If not provided, the current post ID is used.
	 * @return void
	 */
	public static function woocommerce_add_to_wishlist( $product_id = null ) {
		$wishlist       = self::wishlist_product_ids();
		$product_id     = empty( $product_id ) ? get_the_ID() : $product_id;
		$is_in_wishlist = ( $wishlist ) ? ( in_array( $product_id, $wishlist ) ) : false;
		$no_btn_text    = ( empty( cixww_get_option( 'product_button_already_wishlist_text' ) ) && empty( cixww_get_option( 'product_button_text' ) ) && empty( cixww_get_option( 'product_button_remove_text' ) ) ) ? 'no-btn-txt ' : '';
		$class          = ( $is_in_wishlist ) ? 'in_wishlist ' : '';
		$text           = ( $is_in_wishlist && cixww_get_option( 'remove_on_second_click' ) ) ? cixww_get_option( 'product_button_remove_text' ) : esc_html( cixww_get_option( 'product_button_text' ) );
		$show_icon      = ( cixww_get_option( 'product_button_icon' ) == 1 ) ? true : false;
		// Hook for icon HTML
		$icon_html = ( $show_icon ) ? apply_filters( 'cix_add_to_wishlist_icon_html', '<span class="jvm_add_to_wishlist_heart"></span>' ) : '';

		do_action( 'cix_woocommerce_wishlist_before_add_to_wishlist', $product_id );
		$button_class = ( cixww_get_option( 'product_button_type' ) == 'button' ) ? 'button ' . $no_btn_text : 'btn-link ' . $no_btn_text;
		$class       .= apply_filters( 'cix_add_to_wishlist_class', ' jvm_add_to_wishlist ' . $button_class );
		?>
			<a class="<?php echo esc_attr( $class ); ?>" href="?add-to-wishlist=<?php echo $product_id; ?>" title="<?php echo esc_attr( $text ); ?>" rel="nofollow" data-product-title="<?php echo esc_attr( get_the_title( $product_id ) ); ?>" data-product-id="<?php echo $product_id; ?>" <?php echo ( cixww_get_option( 'remove_on_second_click' ) && in_array( $product_id, $wishlist ) ) ? 'data-remove=' . $product_id : ''; ?>>
					<?php echo $icon_html; ?>
				<span class="jvm_add_to_wishlist_text_add"><?php echo esc_html( cixww_get_option( 'product_button_text' ) ); ?></span>

				<?php if ( cixww_get_option( 'remove_on_second_click' ) ) : ?>
				<span class="jvm_add_to_wishlist_text_remove"><?php echo esc_html( cixww_get_option( 'product_button_remove_text' ) ); ?></span>
				<?php endif; ?>

				<?php if ( cixww_get_option( 'product_button_already_wishlist_text' ) && ! cixww_get_option( 'remove_on_second_click' ) ) : ?>
				<span class="jvm_add_to_wishlist_text_already_in"><?php echo esc_html( cixww_get_option( 'product_button_already_wishlist_text' ) ); ?></span>
				<?php endif; ?>
				
			</a>
		<?php
		do_action( 'cix_woocommerce_wishlist_after_add_to_wishlist', $product_id );
	}
	/**
	 * Replaces the placeholders in the text with the actual values.
	 *
	 * @param array $param_list The list of placeholders and their corresponding values.
	 * @param int   $post_id    The ID of the post.
	 * @return array The list of placeholders and their corresponding values.
	 */
	public function replace_info( $param_list, $post_id ) {

		$param_list['{guest_session_in_days}'] = Helper::get_transient_expiration( 'cix_wc_wishlist_' . self::get_wishlist_temp_id() );
		$param_list['{product_name}']          = get_the_title( $post_id );
		$param_list['{view_cart_url}']         = '<a class="ciww-cart-link" href="' . wc_get_cart_url() . '">' . __( 'View Cart', 'jvm-woocommerce-wishlist' ) . '</a>';

		return $param_list;
	}
	/**
	 * Returns the text for the 'Already in Wishlist' notice.
	 *
	 * @param int $product_id The ID of the product.
	 * @return string The text for the 'Already in Wishlist' notice.
	 */
	public static function already_in_wishlist_text( $product_id ) {

		return Helper::replace_text( cixww_get_option( 'product_already_in_wishlist_text' ), '{product_name}', $product_id );
	}
	/**
	 * Returns the text for the 'Added to Wishlist' notice.
	 *
	 * @param int $product_id The ID of the product.
	 * @return string The text for the 'Added to Wishlist' notice.
	 */
	public static function added_to_wishlist_text( $product_id ) {

		return Helper::replace_text( cixww_get_option( 'product_added_to_wishlist_text' ), '{product_name}', $product_id );
	}
	/**
	 * Returns the text for the 'Removed from Wishlist' notice.
	 *
	 * @param int $product_id The ID of the product.
	 * @return string The text for the 'Removed from Wishlist' notice.
	 */
	public static function removed_from_wishlist_text( $product_id ) {

		return Helper::replace_text( cixww_get_option( 'product_removed_from_wishlist_text' ), '{product_name}', $product_id );
	}
}
