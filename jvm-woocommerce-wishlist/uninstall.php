<?php
/**
 * JVM Woocommerce Wishlist Uninstall
 *
 * Uninstalling JVM Woocommerce Wishlist
 *
 
 * @category Core
 * @package JVMWooCommerceWishlist/Uninstaller
 * @version 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'jvm_woocommerce_wishlist_page_id' );