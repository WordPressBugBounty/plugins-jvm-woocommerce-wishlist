<?php
/**
 * Plugin Name: JVM WooCommerce Wishlist
 * Description: Enhance your e-commerce store's functionality with WooCommerce Wishlist - the ultimate tool that adds a powerful and lightweight wishlist feature. Improve your customer's shopping experience and boost your sales with this essential addition to your online store.
 * Version: 2.0.4
 * Author: Codeixer
 * Author URI: https://codeixer.com
 * Tested up to: 6.7.1
 * WC requires at least: 5.0
 * WC tested up to: 9.4.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * Text Domain: jvm-woocommerce-wishlist
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


define( 'CIXWW_PLUGIN_DIR', __DIR__ );
define( 'CIXWW_PLUGIN_VER', '2.0.4' );
define( 'CIXWW_PLUGIN_FILE', __FILE__ );
define( 'CIXWW_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'CIXWW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CIXWW_ASSETS', CIXWW_PLUGIN_URL . '/assets' );

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_jvm_woocommerce_wishlist() {

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once __DIR__ . '/appsero/src/Client.php';
	}

	$client = new Appsero\Client( '29ff6213-2aed-47b6-9bc2-f1ac982963e7', 'Wishlist for WooCommerce', __FILE__ );

	// Active insights
	$client->insights()->init();
}

//appsero_init_tracker_jvm_woocommerce_wishlist();

register_activation_hook( __FILE__, array( '\CIXW_WISHLIST\Bootstrap', 'activation' ) );

// plugin_loaded hook
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'jvm-woocommerce-wishlist', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
);
add_action(
	'plugins_loaded',
	function () {
		\CIXW_WISHLIST\Bootstrap::init();
	}
);
