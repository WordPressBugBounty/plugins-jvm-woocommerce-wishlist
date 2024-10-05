<?php
namespace CIXW_WISHLIST;

class AjaxActions {
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
	 * constructor.
	 */
	private function __construct() {
		Helper::add_ajax( 'cix_update_wishlist', array( $this, 'update_wishlist' ) );
		Helper::add_ajax( 'cix_wishlist_add_to_cart', array( $this, 'add_to_cart_wishlist_page' ) );
		Helper::add_ajax( 'cix_remove_product', array( $this, 'remove_product_wishlist_page' ) );
	}
	/**
	 * Adds a product to the cart from the wishlist page.
	 *
	 * This function is called via AJAX and performs the necessary actions to add a product to the cart.
	 * It checks for nonce security, verifies the nonce, and adds the product to the cart.
	 * If the product is successfully added to the cart, it displays a success message and performs additional actions based on the plugin options.
	 * If the 'cart_all' parameter is set to true, it adds all products in the wishlist to the cart.
	 *
	 * @since 2.0.0
	 */
	public function add_to_cart_wishlist_page() {
		if ( ! DOING_AJAX ) {
			wp_die();
		} // Not Ajax

		// Check for nonce security
		$nonce      = sanitize_text_field( $_POST['nonce'] );
		$product_id = absint( $_POST['product_id'] );
		$cart_all   = isset( $_POST['cart_all'] ) ? sanitize_text_field( $_POST['cart_all'] ) : false;

		if ( ! wp_verify_nonce( $nonce, 'cix-wishlist-nonce' ) ) {
				wp_die( 'Oops! nonce error' );
		}

		if ( $cart_all ) {

			$product_ids = Wishlist::wishlist_product_ids();
			foreach ( $product_ids as $product_id ) {
				$added_to_cart = WC()->cart->add_to_cart( $product_id );

				if ( $added_to_cart && cixww_get_option( 'wishlist_page_table_remove_if_added_to_cart' ) ) {
					// wishlist_page_table_remove_if_added_to_cart
					wc_add_to_cart_message( $product_id );

						Wishlist::remove_product( $product_id );
						$data['removed'] = true;

				} else {
					$data['loop_item'][] = Wishlist::wishlist_loop_items( array( 'product_id' => $product_id ) );
				}
			}
			$data['add_to_cart_notice'] = wc_print_notices( true );
			wp_send_json_success( $data );
			wp_die();
		}

		$added_to_cart = WC()->cart->add_to_cart( $product_id );

		if ( $added_to_cart ) {

			wc_add_to_cart_message( $product_id );

			// Redirect to cart page after adding to cart from wishlist page.
			if ( cixww_get_option( 'wishlist_page_table_redirect_to_cart' ) ) {
				// cart page link in a tag
				$data['cart_url'] = wc_get_cart_url();
			}
			// wishlist_page_table_remove_if_added_to_cart
			if ( cixww_get_option( 'wishlist_page_table_remove_if_added_to_cart' ) ) {
				Wishlist::remove_product( $product_id );
				$data['removed'] = true;

			}
		}

		$data['added_to_cart']      = $added_to_cart;
		$data['add_to_cart_notice'] = wc_print_notices( true );
		wp_send_json_success( $data );
		wp_die();
	}

	/**
	 * Removes a product from the wishlist page via AJAX.
	 *
	 * This function is called when an AJAX request is made to remove a product from the wishlist page.
	 * It checks for the presence of a valid nonce for security purposes, and then removes the specified product from the wishlist.
	 * If the removal is successful, it sends a JSON response with a success message.
	 *
	 * @since 2.0.0
	 */
	public function remove_product_wishlist_page() {
		if ( ! DOING_AJAX ) {
			wp_die();
		} // Not Ajax

		// Check for nonce security
		$nonce = sanitize_text_field( $_POST['nonce'] );
		if ( ! wp_verify_nonce( $nonce, 'cix-wishlist-nonce' ) ) {
				wp_die( 'Oops! nonce error' );
		}
		$data = array();

		if ( isset( $_POST['product_id'] ) && absint( $_POST['product_id'] ) != 0 ) {
			$product_id = absint( $_POST['product_id'] );

				Wishlist::remove_product( $product_id );

				$data['remove_notice'] = '<a href="#" data-product-id="' . $product_id . '" class="wishlist-undo ciww-cart-link">' . __( 'Undo?', 'jvm-woocommerce-wishlist' ) . '</a>' . Helper::replace_text( cixww_get_option( 'removed_cart_notice' ), '{product_name}', $product_id );

			wp_send_json_success( $data );
		}

		wp_die();
	}

	/**
	 * Update the wishlist via AJAX.
	 *
	 * This function is responsible for updating the wishlist when an AJAX request is made.
	 * It checks for the nonce security, retrieves the necessary data from the AJAX request,
	 * and performs the required actions based on the configuration settings.
	 *
	 * @since 2.0
	 */
	public function update_wishlist() {

		if ( ! DOING_AJAX ) {
			wp_die();
		} // Not Ajax

		// Check for nonce security
		$nonce              = sanitize_text_field( $_POST['nonce'] );
		$product_id         = sanitize_text_field( $_POST['product_id'] );
		$show_icon          = cixww_get_option( 'product_button_icon' );
		$after_added_action = cixww_get_option( 'product_button_action' );

		if ( ! wp_verify_nonce( $nonce, 'cix-wishlist-nonce' ) ) {
				wp_die( 'Oops! nonce error' );
		}
		$data = array(
			'product_id'          => $product_id,
			'show_icon'           => $show_icon,
			'already_in_wishlist' => in_array( $product_id, Wishlist::wishlist_product_ids() ),
		);

		if ( $after_added_action == 'redirect' ) {
			$data['redirect']     = true;
			$data['redirect_url'] = get_the_permalink( cixww_get_option( 'wishlist_page' ) );
		}
		if ( $after_added_action == 'popup' ) {
			$data['popup'] = true;
		}
		if ( cixww_get_option( 'remove_on_second_click' ) && in_array( $product_id, Wishlist::wishlist_product_ids() ) ) {
			Wishlist::remove_product( $product_id );
			$data['removed'] = true;
			unset( $data['already_in_wishlist'] );
			$data['template'] = Wishlist::wishlist_popup( $data );
		} elseif ( in_array( $product_id, Wishlist::wishlist_product_ids() ) ) {

			$data['template'] = Wishlist::wishlist_popup( $data );

		} else {
			Wishlist::set_product( $product_id );
			$data['added']     = true;
			$data['template']  = Wishlist::wishlist_popup( $data );
			$data['loop_item'] = Wishlist::wishlist_loop_items( array( 'product_id' => $product_id ) );

		}

		wp_send_json_success( $data );
		wp_die(); // this is required to terminate immediately and return a proper response
	}
}
