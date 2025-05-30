<?php
namespace CIXW_WISHLIST;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Settings {
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
		$this->pluginOptions();
		add_action( 'csf_cixwishlist_settings_save_after', array( $this, 'save_after' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );
		add_filter( 'plugin_action_links_' . CIXWW_PLUGIN_BASE, array( $this, 'plugin_links' ) );
	}

	/**
	 * Add links to plugin's description in plugins table
	 *
	 * @param array  $links Initial list of links.
	 * @param string $file  Basename of current plugin.
	 */
	function plugin_meta_links( $links, string $file ) {
		if ( CIXWW_PLUGIN_BASE !== $file ) {
			return $links;
		}
		// add doc link
		$doc_link     = '<a target="_blank" href="https://www.codeixer.com/docs-category/wishlist-for-wc/" title="' . 'Documentation' . '">' . 'Docs' . '</a>';
		$support_link = '<a style="color:red;" target="_blank" href="https://codeixer.com/contact-us/" title="' . 'Get help' . '">' . 'Support' . '</a>';
		$rate_plugin  = '<a target="_blank" href="https://wordpress.org/support/plugin/jvm-woocommerce-wishlist/reviews/?filter=5"> Rate this plugin » </a>';

		$links[] = $doc_link;
		$links[] = $support_link;
		$links[] = $rate_plugin;

		return $links;
	} // plugin_meta_links
	public function plugin_links( $links ) {
		$settings_link = '<a href="' . get_admin_url( null, 'admin.php?page=cixwishlist_settings' ) . '">' . 'Settings' . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	public function save_after( $data ) {
		update_option( 'cixww_onboarding_onboarding_steps_status', 'complete' );
	}
	// create a function that return all publish pages
	public static function get_pages( $create = '' ) {
		$pages       = get_pages(
			array(
				'sort_order'   => 'asc',
				'sort_column'  => 'post_title',
				'hierarchical' => 0,
				'parent'       => -1,
				'post_type'    => 'page',
				'post_status'  => 'publish',
			)
		);
		$pages_array = array();
		if ( $create ) {
			$pages_array['gen_page'] = 'Create Automatically';
		}
		foreach ( $pages as $page ) {
			$pages_array[ $page->ID ] = $page->post_title;
		}
			return $pages_array;
	}



	public function pluginOptions() {

		// Set a unique slug-like ID
		$prefix       = 'cixwishlist_settings';
		$doc_link     = '<a class="button" target="_blank" href="https://www.codeixer.com/docs-category/wishlist-for-wc/" title="' . 'Documentation' . '">' . 'Documentation' . '</a>';
		$roadmap_link = '<script>
  var li_sidebar = {
	workspace_id : "0d913973-c1ab-470c-90f1-a1b3cb5046b7"
  };
</script>
<script type="text/javascript" src="https://cdn.loopedin.io/js/sidebar.min.js?v=0.2" defer="defer"></script>';
		//
		// Create options
		\CSF::createOptions(
			$prefix,
			array(
				'menu_title'      => 'Wishlist Settings',
				'menu_slug'       => $prefix,
				'framework_title' => 'Wishlist for WooCommerce Settings <small>v' . CIXWW_PLUGIN_VER . '</small><br>' . $doc_link . $roadmap_link,
				'menu_type'       => 'submenu',
				'menu_parent'     => apply_filters( 'ciwishlist_menu_parent', 'codeixer' ),
				// 'nav'             => 'tab',
				// 'theme'           => 'light',

				'footer_credit'   => 'Please Rate <strong>WooCommerce Wishlist</strong> on <a href="https://wordpress.org/support/plugin/jvm-woocommerce-wishlist/reviews/?filter=5" target="_blank"> WordPress.org</a>  to help us spread the word. Thank you from the Codeixer team!',
				'show_bar_menu'   => false,
				'show_footer'     => false,
				'ajax_save'       => false,
				'defaults'        => self::defaults(),

			)
		);

		// Create General section
		\CSF::createSection(
			$prefix,
			array(
				'title'  => 'General Settings',
				'icon'   => 'fa fa-sliders',
				'fields' => array(

					// A text field
					array(
						'id'      => 'wishlist_name',
						'type'    => 'text',

						'title'   => 'Default Wishlist Name',
						'default' => 'Wishlist',
					),

					array(
						'id'          => 'wishlist_page',
						'type'        => 'select',
						'title'       => 'Wishlist Page',
						'placeholder' => 'Select a page',

						'ajax'        => true,
						'options'     => 'pages',
						'width'       => '250px',
						'class'       => 'default-wishlist-page-field',
						'desc'        => '<style>.default-wishlist-page-field .chosen-container {width: 445px !important;}</style>The page must contain the <code>[jvm_woocommerce_wishlist]</code> shortcode.',
					),
					// add switcher for Require Login
					// array(
					// 'id'      => 'wishlist_require_login',
					// 'type'    => 'switcher',
					// 'title'   => 'Require Login',
					// 'default' => false,
					// 'desc'    => 'Require users to be logged in to add items to the wishlist.',
					// ),
				// add select field for Action after added to wishlist
					array(
						'id'      => 'product_button_action',
						'type'    => 'select',
						'title'   => 'Action after added to Wishlist',
						'options' => array(
							'none'     => 'None',
							'redirect' => 'Redirect to Wishlist Page',
							'popup'    => 'Show Popup',
						),
						'default' => 'popup',
					),
					array(
						'id'      => 'remove_on_second_click',
						'type'    => 'switcher',
						'title'   => 'Remove product from Wishlist on the second click',
						'default' => false,
						'desc'    => 'Remove product from Wishlist on the second click.',
					),
					// add filed for guest wishlist delete
					array(
						'id'      => 'guest_wishlist_delete',
						'type'    => 'number',
						'title'   => 'Delete Guest Wishlist',
						'default' => 30,
						'unit'    => 'Days',
						'desc'    => 'Delete guest wishlist after x days.',
					),

				),
			)
		);
		// create a popup section
		\CSF::createSection(
			$prefix,
			array(
				'title'  => 'Popup', // It will be displayed in the title bar
				'icon'   => 'fa fa-sliders',
				'fields' => array(
					// view wishlist text field
					array(
						'id'      => 'product_view_wishlist_text',
						'type'    => 'text',
						'title'   => 'View Wishlist Text',
						'default' => 'View Wishlist',
					),
					// prodct already in wishlist text field
					array(
						'id'      => 'product_already_in_wishlist_text',
						'type'    => 'text',
						'title'   => 'Product Already in Wishlist Text',
						'default' => '{product_name} Already in Wishlist',
						'desc'    => 'Text to display when the product is already in the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
					),
					// product added to wishlist text field
					array(
						'id'      => 'product_added_to_wishlist_text',
						'type'    => 'text',
						'title'   => 'Product Added to Wishlist Text',
						'default' => '{product_name} Added to Wishlist',
						'desc'    => 'Text to display when the product is added to the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
					),
					// product removed from wishlist text field
					array(
						'id'      => 'product_removed_from_wishlist_text',
						'type'    => 'text',
						'title'   => 'Product Removed from Wishlist Text',
						'default' => '{product_name} Removed from Wishlist',
						'desc'    => 'Text to display when the product is removed from the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.',
					),
				),
			)
		);
		// Create a top-tab
		\CSF::createSection(
			$prefix,
			array(
				'id'    => 'wb_tab', // Set a unique slug-like ID
				'title' => 'Add To Wishlist Button', // It will be displayed in the title bar
				'icon'  => 'fas fa-heart',
			)
		);

		// Create a listing page
		\CSF::createSection(
			$prefix,
			array(
				'parent' => 'wb_tab',
				'title'  => 'Listing Page', // It will be displayed in the title bar
				'fields' => array(
					// add switcher field for loop settings
					array(
						'id'      => 'loop_button',
						'type'    => 'switcher',
						'title'   => 'Display "Add to Wishlist"',
						'desc'    => 'Display "Add to Wishlist" button on product listings like Shop page, categories, etc.',
						'default' => true,

					),
					array(
						'id'         => 'loop_button_position',
						'type'       => 'select',
						'title'      => '"Add to Wishlist" Position',
						'options'    => array(
							'after'    => 'After "Add to Cart" button',
							'before'   => 'Before "Add to Cart" button',
							'in_thumb' => 'Above Thumbnail',
							'custom'   => 'Custom Position / Shortcode',

						),
						'default'    => 'after',
						'dependency' => array( 'loop_button', '==', 'true' ),
					),
					// add text field with no edit
					array(
						'id'         => 'product_add_to_wishlist_text',
						'type'       => 'text',
						'title'      => 'Shortcode',
						'default'    => '[jvm_add_to_wishlist]',
						'attributes' => array(
							'readonly' => 'readonly',
						),
						'dependency' => array( 'loop_button_position', '==', 'custom' ),
					),

				),
			)
		);
		// single product page
		\CSF::createSection(
			$prefix,
			array(
				'parent' => 'wb_tab', // The slug of the parent section
				'title'  => 'Product Page',
				'icon'   => '',
				'fields' => array(
					array(
						'id'      => 'product_button',
						'type'    => 'switcher',
						'title'   => 'Display "Add to Wishlist"',
						'default' => true,
						'desc'    => 'Display "Add to Wishlist" button on the single product page.',

					),
					// select field for button position
					array(
						'id'         => 'product_button_position',
						'type'       => 'select',
						'title'      => '"Add to Wishlist" Position',
						'options'    => array(
							'after'         => 'After "Add to Cart" button',
							'before'        => 'Before "Add to Cart" button',
							'after_summary' => 'After Summary',
							'custom'        => 'Custom Position / Shortcode',
						),
						'default'    => 'after',
						'dependency' => array( 'product_button', '==', 'true' ),
						'desc'       => 'Select the position where you want to display "Add to Wishlist" button on the single product page',
					),
					// add text field with no edit
					array(
						'id'         => 'product_add_to_wishlist_text',
						'type'       => 'text',
						'title'      => 'Shortcode',
						'default'    => '[jvm_add_to_wishlist]',
						'attributes' => array(
							'readonly' => 'readonly',
						),
						'dependency' => array( 'product_button_position', '==', 'custom' ),
					),

				),
			)
		);
		// button
		\CSF::createSection(
			$prefix,
			array(
				'parent' => 'wb_tab', // The slug of the parent section
				'title'  => 'Button',
				'icon'   => '',
				'fields' => array(
					// select field for button type
					array(
						'id'      => 'product_button_type',
						'type'    => 'select',
						'title'   => 'Button Type',
						'options' => array(
							'button' => 'Button',
							'link'   => 'Link',
						),
						'default' => 'button',
					),
					// button icon switcher
					array(
						'id'      => 'product_button_icon',
						'type'    => 'switcher',
						'title'   => 'Button Icon',
						'default' => true,
					),

					array(
						'id'      => 'product_button_txt_color',
						'type'    => 'link_color',
						'title'   => 'Icon & Text Color',
						'color'   => true,
						'hover'   => true,

						'active'  => true,
						'output'  => '.jvm_add_to_wishlist span',
						'default' => array(
							'color'  => '#1e73be',
							'hover'  => '#259ded',
							'active' => '#333',
						),
					),

					// wisth button text field
					array(
						'id'      => 'product_button_text',
						'type'    => 'text',
						'title'   => 'Button Text',
						'default' => 'Add to Wishlist',
					),
					// add text field for Remove from Wishlist
					array(
						'id'      => 'product_button_remove_text',
						'type'    => 'text',
						'title'   => '"Remove from Wishlist" Text',
						'default' => 'Remove from Wishlist',
					),
					array(
						'id'      => 'product_button_already_wishlist_text',
						'type'    => 'text',
						'title'   => '"Already in wishlist" Text',
						'default' => 'Already in Wishlist',
					),

				),
			)
		);
		// Create wishlist page section
		\CSF::createSection(
			$prefix,
			array(
				'title'  => 'Wishlist Page',
				'icon'   => 'fa fa-sliders',
				'fields' => array(
					// add guest_notice field
					array(
						'id'      => 'guest_notice',
						'type'    => 'textarea',
						'title'   => 'Guest Notice',
						'default' => 'please log in to save items to your wishlist. This wishlist will be deleted after {guest_session_in_days}.',
						'desc'    => 'Guest notice message.Use, placeholder <code>{guest_session_in_days}</code> to display expired tme.',

					),
					// wishlist page no item text field
					array(
						'id'      => 'wishlist_page_no_item_text',
						'type'    => 'text',
						'title'   => 'No Item Text',
						'default' => 'No items in your wishlist',
					),

					// wishlist page table add to cart text field
					array(
						'id'      => 'wishlist_page_table_add_to_cart_text',
						'type'    => 'text',
						'title'   => 'Add to Cart Text',
						'default' => 'Add to Cart',
					),
					// add in stock text field
					array(
						'id'      => 'wishlist_in_stock_text',
						'type'    => 'text',
						'title'   => 'In Stock Text',
						'default' => 'In Stock',
					),
					// add out of stock text field
					array(
						'id'      => 'wishlist_out_of_stock_text',
						'type'    => 'text',
						'title'   => 'Out of Stock Text',
						'default' => 'Out of Stock',
					),
					array(
						'id'      => 'wishlist_page_table_unit_price',
						'type'    => 'switcher',
						'title'   => 'Show Unit Price',
						'default' => true,
					),
					// add switcher for stock status
					array(
						'id'      => 'wishlist_page_table_stock_status',
						'type'    => 'switcher',
						'title'   => 'Show Stock Status',
						'default' => true,
					),

					// removed_cart_notice notice field
					array(
						'id'      => 'removed_cart_notice',
						'type'    => 'text',
						'title'   => 'Removed from Cart Notice',
						'default' => '{product_name} removed from cart',
						'desc'    => 'Removed from cart notice message. Use, placeholder <code>{product_name}</code> to display name of the product.',
					),
					// // add switcher for quantity
					// array(
					// 'id'      => 'wishlist_page_table_quantity',
					// 'type'    => 'switcher',
					// 'title'   => 'Show Quantity',
					// 'default' => true,
					// ),
					// // add switcher for total price
					// array(
					// 'id'      => 'wishlist_page_table_total_price',
					// 'type'    => 'switcher',
					// 'title'   => 'Show Total Price',
					// 'default' => true,
					// ),

					// // add switcher for added date
					// array(
					// 'id'      => 'wishlist_page_table_added_date',
					// 'type'    => 'switcher',
					// 'title'   => 'Show Added Date',
					// 'default' => true,
					// ),
					// // add switcher for show checkbox
					// array(
					// 'id'      => 'wishlist_page_table_checkbox',
					// 'type'    => 'switcher',
					// 'title'   => 'Show Checkbox',
					// 'default' => true,
					// ),
					// add switcher for redirect to cart
					array(
						'id'      => 'wishlist_page_table_redirect_to_cart',
						'type'    => 'switcher',
						'title'   => 'Redirect to Cart',
						'desc'    => 'Redirect to cart page after adding to cart from wishlist page.',
						'default' => true,
					),
					// add switcher for remove if added to cart
					array(
						'id'      => 'wishlist_page_table_remove_if_added_to_cart',
						'type'    => 'switcher',
						'title'   => 'Remove if Added to Cart',
						'desc'    => 'Remove item from wishlist if added to cart.',
						'default' => true,
					),
					// add switcher for "Add All to Cart" button
					array(
						'id'      => 'table_add_all_to_cart',
						'type'    => 'switcher',
						'title'   => 'Show "Add All to Cart" Button',
						'default' => true,
					),
					// add text field for "Add All to Cart" button text
					array(
						'id'         => 'table_add_all_to_cart_text',
						'type'       => 'text',
						'title'      => '"Add All to Cart" Button Text',
						'default'    => 'Add All to Cart',
						'dependency' => array( 'table_add_all_to_cart', '==', 'true' ),
					),

				),
			)
		);

		// add section for Advanced Settings
		\CSF::createSection(
			$prefix,
			array(
				'title'  => 'Advanced Settings',
				'icon'   => 'fa fa-sliders',
				'fields' => array(
					// add css field
					array(
						'id'       => 'wishlist_css',
						'type'     => 'code_editor',
						'title'    => 'Custom CSS',
						'default'  => '',
						'settings' => array(
							'theme' => 'mbo',
							'mode'  => 'css',
						),
					),
				),
			)
		);

					// add switcher for disable cache

		// TODO: move to pro version
		// License key
		// \CSF::createSection(
		// $prefix,
		// array(
		// 'title'  => 'License',
		// 'icon'   => 'fas fa-key',
		// 'fields' => array(

		// A Callback Field Example
		// array(
		// 'id'          => 'license-key',
		// 'type'        => 'text',
		// 'title'       => 'Purchase Code',
		// 'placeholder' => 'Enter Purchase Code',
		// 'desc'        => __( 'Enter your license key here, to activate <strong>Bayna - Deposits for WooCommerce PRO</strong>, and get automatic updates and premium support. <a href="' . apply_filters( 'bayna_learn_more', 'https://www.codeixer.com/docs/where-is-my-purchase-code/' ) . '" target="_blank">Learn More</a>', 'jvm-woocommerce-wishlist' ),
		// ),
		// array(
		// 'type'     => 'callback',
		// 'function' => 'wcbaynaLicense',
		// ),

		// ),
		// )
		// );
		// add backups section
		\CSF::createSection(
			$prefix,
			array(
				'title'  => 'Backups',
				'icon'   => 'fas fa-cog',
				'fields' => array(
					// add backup field
					array(
						'id'          => 'backup',
						'type'        => 'backup',
						'title'       => 'Backup Settings',
						'desc'        => 'Backup your settings',
						'backup'      => 'cixwishlist_settings',
						'backup_args' => array(
							'prefix' => 'cixwishlist_settings',
						),
					),
				),
			)
		);
	}
	public static function defaults() {
		$data = array(
			'wishlist_name'                               => 'Wishlist',
			'product_button_action'                       => 'popup',
			'remove_on_second_click'                      => '0',
			'guest_wishlist_delete'                       => 30,
			'product_view_wishlist_text'                  => 'View Wishlist',
			'product_already_in_wishlist_text'            => '{product_name} Already in Wishlist',
			'product_added_to_wishlist_text'              => '{product_name} Added to Wishlist',
			'product_removed_from_wishlist_text'          => '{product_name} Removed from Wishlist',
			'loop_button'                                 => '1',
			'loop_button_position'                        => 'after',
			'product_add_to_wishlist_text'                => '[jvm_add_to_wishlist]',
			'product_button'                              => '1',
			'product_button_position'                     => 'after',
			'product_button_type'                         => 'button',
			'product_button_icon'                         => '1',
			'product_button_txt_color'                    => array(
				'color'  => '#1e73be',
				'hover'  => '#259ded',
				'active' => '#333',
			),
			'product_button_text'                         => 'Add to Wishlist',
			'product_button_remove_text'                  => 'Remove from Wishlist',
			'product_button_already_wishlist_text'        => 'Already in Wishlist',
			'guest_notice'                                => 'please log in to save items to your wishlist. This wishlist will be deleted after {guest_session_in_days}.',
			'wishlist_page_no_item_text'                  => 'No items in your wishlist',
			'wishlist_page_table_add_to_cart_text'        => 'Add to Cart',
			'wishlist_in_stock_text'                      => 'In Stock',
			'wishlist_out_of_stock_text'                  => 'Out of Stock',
			'wishlist_page_table_unit_price'              => '1',
			'wishlist_page_table_stock_status'            => '1',
			'removed_cart_notice'                         => '{product_name} removed from cart',
			'wishlist_page_table_redirect_to_cart'        => '1',
			'wishlist_page_table_remove_if_added_to_cart' => '1',
			'table_add_all_to_cart'                       => '1',
			'table_add_all_to_cart_text'                  => 'Add All to Cart',
			'wishlist_css'                                => '',
			'backup'                                      => '',
		);
		return $data;
	}
	/**
	 * Delete all '$preifx' transients from the database.
	 */
	public static function delete_transients( $prefix ) {
		$pf = new self();
		$pf->delete_transients_with_prefix( $prefix );
	}
	/**
	 * Delete all transients from the database whose keys have a specific prefix.
	 *
	 * @param string $prefix The prefix. Example: 'my_cool_transient_'.
	 */
	public function delete_transients_with_prefix( $prefix ) {
		foreach ( $this->get_transient_keys_with_prefix( $prefix ) as $key ) {
			delete_transient( $key );
		}
	}
	/**
	 * Gets all transient keys in the database with a specific prefix.
	 *
	 * Note that this doesn't work for sites that use a persistent object
	 * cache, since in that case, transients are stored in memory.
	 *
	 * @param  string $prefix Prefix to search for.
	 * @return array          Transient keys with prefix, or empty array on error.
	 */
	private function get_transient_keys_with_prefix( $prefix ) {
		global $wpdb;

		$prefix = $wpdb->esc_like( '_transient_' . $prefix );
		$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
		$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );

		if ( is_wp_error( $keys ) ) {
			return array();
		}

		return array_map(
			function ( $key ) {
				// Remove '_transient_' from the option name.
				return substr( $key['option_name'], strlen( '_transient_' ) );
			},
			$keys
		);
	}
}
