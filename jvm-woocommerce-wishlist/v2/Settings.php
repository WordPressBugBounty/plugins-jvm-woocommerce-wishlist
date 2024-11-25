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
		$doc_link     = '<a target="_blank" href="https://www.codeixer.com/docs-category/wishlist-for-wc/" title="' . __( 'Documentation', 'jvm-woocommerce-wishlist' ) . '">' . __( 'Docs', 'jvm-woocommerce-wishlist' ) . '</a>';
		$support_link = '<a style="color:red;" target="_blank" href="https://codeixer.com/contact-us/" title="' . __( 'Get help', 'jvm-woocommerce-wishlist' ) . '">' . __( 'Support', 'jvm-woocommerce-wishlist' ) . '</a>';
		$rate_plugin  = '<a target="_blank" href="https://wordpress.org/support/plugin/jvm-woocommerce-wishlist/reviews/?filter=5"> Rate this plugin » </a>';

		$links[] = $doc_link;
		$links[] = $support_link;
		$links[] = $rate_plugin;

		return $links;
	} // plugin_meta_links
	public function plugin_links( $links ) {
		$settings_link = '<a href="' . get_admin_url( null, 'admin.php?page=cixwishlist_settings' ) . '">' . __( 'Settings', 'jvm-woocommerce-wishlist' ) . '</a>';
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
		$prefix   = 'cixwishlist_settings';
		$doc_link = '<a class="button" target="_blank" href="https://www.codeixer.com/docs-category/wishlist-for-wc/" title="' . __( 'Documentation', 'jvm-woocommerce-wishlist' ) . '">' . __( 'Documentation', 'jvm-woocommerce-wishlist' ) . '</a>';
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
				'framework_title' => 'Wishlist for WooCommerce Settings <small>v' . CIXWW_PLUGIN_VER . '</small><br>'.$doc_link .$roadmap_link,
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

						'title'   => __( 'Default Wishlist Name', 'jvm-woocommerce-wishlist' ),
						'default' => 'Wishlist',
					),

					array(
						'id'          => 'wishlist_page',
						'type'        => 'select',
						'title'       => __( 'Wishlist Page', 'jvm-woocommerce-wishlist' ),
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
					// 'title'   => __( 'Require Login', 'jvm-woocommerce-wishlist' ),
					// 'default' => false,
					// 'desc'    => __( 'Require users to be logged in to add items to the wishlist.', 'jvm-woocommerce-wishlist' ),
					// ),
				// add select field for Action after added to wishlist
					array(
						'id'      => 'product_button_action',
						'type'    => 'select',
						'title'   => __( 'Action after added to Wishlist', 'jvm-woocommerce-wishlist' ),
						'options' => array(
							'none'     => __( 'None', 'jvm-woocommerce-wishlist' ),
							'redirect' => __( 'Redirect to Wishlist Page', 'jvm-woocommerce-wishlist' ),
							'popup'    => __( 'Show Popup', 'jvm-woocommerce-wishlist' ),
						),
						'default' => 'popup',
					),
					array(
						'id'      => 'remove_on_second_click',
						'type'    => 'switcher',
						'title'   => __( 'Remove product from Wishlist on the second click', 'jvm-woocommerce-wishlist' ),
						'default' => false,
						'desc'    => __( 'Remove product from Wishlist on the second click.', 'jvm-woocommerce-wishlist' ),
					),
					// add filed for guest wishlist delete
					array(
						'id'      => 'guest_wishlist_delete',
						'type'    => 'number',
						'title'   => __( 'Delete Guest Wishlist', 'jvm-woocommerce-wishlist' ),
						'default' => 30,
						'unit'    => 'Days',
						'desc'    => __( 'Delete guest wishlist after x days.', 'jvm-woocommerce-wishlist' ),
					),

				),
			)
		);
		// create a popup section
		\CSF::createSection(
			$prefix,
			array(
				'title'  => __( 'Popup', 'jvm-woocommerce-wishlist' ), // It will be displayed in the title bar
				'icon'   => 'fa fa-sliders',
				'fields' => array(
					// view wishlist text field
					array(
						'id'      => 'product_view_wishlist_text',
						'type'    => 'text',
						'title'   => __( 'View Wishlist Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'View Wishlist',
					),
					// prodct already in wishlist text field
					array(
						'id'      => 'product_already_in_wishlist_text',
						'type'    => 'text',
						'title'   => __( 'Product Already in Wishlist Text', 'jvm-woocommerce-wishlist' ),
						'default' => '{product_name} Already in Wishlist',
						'desc'    => __( 'Text to display when the product is already in the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.', 'jvm-woocommerce-wishlist' ),
					),
					// product added to wishlist text field
					array(
						'id'      => 'product_added_to_wishlist_text',
						'type'    => 'text',
						'title'   => __( 'Product Added to Wishlist Text', 'jvm-woocommerce-wishlist' ),
						'default' => '{product_name} Added to Wishlist',
						'desc'    => __( 'Text to display when the product is added to the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.', 'jvm-woocommerce-wishlist' ),
					),
					// product removed from wishlist text field
					array(
						'id'      => 'product_removed_from_wishlist_text',
						'type'    => 'text',
						'title'   => __( 'Product Removed from Wishlist Text', 'jvm-woocommerce-wishlist' ),
						'default' => '{product_name} Removed from Wishlist',
						'desc'    => __( 'Text to display when the product is removed from the wishlist. Use, placeholder <code>{product_name}</code> to display name of the product.', 'jvm-woocommerce-wishlist' ),
					),
				),
			)
		);
		// Create a top-tab
		\CSF::createSection(
			$prefix,
			array(
				'id'    => 'wb_tab', // Set a unique slug-like ID
				'title' => __( 'Add To Wishlist Button', 'jvm-woocommerce-wishlist' ), // It will be displayed in the title bar
				'icon'  => 'fas fa-heart',
			)
		);

		// Create a listing page
		\CSF::createSection(
			$prefix,
			array(
				'parent' => 'wb_tab',
				'title'  => __( 'Listing Page', 'jvm-woocommerce-wishlist' ), // It will be displayed in the title bar
				'fields' => array(
					// add switcher field for loop settings
					array(
						'id'      => 'loop_button',
						'type'    => 'switcher',
						'title'   => __( 'Display "Add to Wishlist"', 'jvm-woocommerce-wishlist' ),
						'desc'    => __( 'Display "Add to Wishlist" button on product listings like Shop page, categories, etc.', 'jvm-woocommerce-wishlist' ),
						'default' => true,

					),
					array(
						'id'         => 'loop_button_position',
						'type'       => 'select',
						'title'      => __( '"Add to Wishlist" Position', 'jvm-woocommerce-wishlist' ),
						'options'    => array(
							'after'    => __( 'After "Add to Cart" button', 'jvm-woocommerce-wishlist' ),
							'before'   => __( 'Before "Add to Cart" button', 'jvm-woocommerce-wishlist' ),
							'in_thumb' => __( 'Above Thumbnail', 'jvm-woocommerce-wishlist' ),
							'custom'   => __( 'Custom Position / Shortcode', 'jvm-woocommerce-wishlist' ),

						),
						'default'    => 'after',
						'dependency' => array( 'loop_button', '==', 'true' ),
					),
					// add text field with no edit
					array(
						'id'         => 'product_add_to_wishlist_text',
						'type'       => 'text',
						'title'      => __( 'Shortcode', 'jvm-woocommerce-wishlist' ),
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
				'title'  => __( 'Product Page', 'deposits-for-woocommerce' ),
				'icon'   => '',
				'fields' => array(
					array(
						'id'      => 'product_button',
						'type'    => 'switcher',
						'title'   => __( 'Display "Add to Wishlist"', 'jvm-woocommerce-wishlist' ),
						'default' => true,
						'desc'    => __( 'Display "Add to Wishlist" button on the single product page.', 'jvm-woocommerce-wishlist' ),

					),
					// select field for button position
					array(
						'id'         => 'product_button_position',
						'type'       => 'select',
						'title'      => __( '"Add to Wishlist" Position', 'jvm-woocommerce-wishlist' ),
						'options'    => array(
							'after'         => __( 'After "Add to Cart" button', 'jvm-woocommerce-wishlist' ),
							'before'        => __( 'Before "Add to Cart" button', 'jvm-woocommerce-wishlist' ),
							'after_summary' => __( 'After Summary', 'jvm-woocommerce-wishlist' ),
							'custom'        => __( 'Custom Position / Shortcode', 'jvm-woocommerce-wishlist' ),
						),
						'default'    => 'after',
						'dependency' => array( 'product_button', '==', 'true' ),
						'desc'       => __( 'Select the position where you want to display "Add to Wishlist" button on the single product page', 'jvm-woocommerce-wishlist' ),
					),
					// add text field with no edit
					array(
						'id'         => 'product_add_to_wishlist_text',
						'type'       => 'text',
						'title'      => __( 'Shortcode', 'jvm-woocommerce-wishlist' ),
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
				'title'  => __( 'Button', 'deposits-for-woocommerce' ),
				'icon'   => '',
				'fields' => array(
					// select field for button type
					array(
						'id'      => 'product_button_type',
						'type'    => 'select',
						'title'   => __( 'Button Type', 'jvm-woocommerce-wishlist' ),
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
						'title'   => __( 'Button Icon', 'jvm-woocommerce-wishlist' ),
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
						'title'   => __( 'Button Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'Add to Wishlist',
					),
					// add text field for Remove from Wishlist
					array(
						'id'      => 'product_button_remove_text',
						'type'    => 'text',
						'title'   => __( '"Remove from Wishlist" Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'Remove from Wishlist',
					),
					array(
						'id'      => 'product_button_already_wishlist_text',
						'type'    => 'text',
						'title'   => __( '"Already in wishlist" Text', 'jvm-woocommerce-wishlist' ),
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
						'title'   => __( 'Guest Notice', 'jvm-woocommerce-wishlist' ),
						'default' => 'please log in to save items to your wishlist. This wishlist will be deleted after {guest_session_in_days}.',
						'desc'    => __( 'Guest notice message.Use, placeholder <code>{guest_session_in_days}</code> to display expired tme.', 'jvm-woocommerce-wishlist' ),

					),
					// wishlist page no item text field
					array(
						'id'      => 'wishlist_page_no_item_text',
						'type'    => 'text',
						'title'   => __( 'No Item Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'No items in your wishlist',
					),

					// wishlist page table add to cart text field
					array(
						'id'      => 'wishlist_page_table_add_to_cart_text',
						'type'    => 'text',
						'title'   => __( 'Add to Cart Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'Add to Cart',
					),
					// add in stock text field
					array(
						'id'      => 'wishlist_in_stock_text',
						'type'    => 'text',
						'title'   => __( 'In Stock Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'In Stock',
					),
					// add out of stock text field
					array(
						'id'      => 'wishlist_out_of_stock_text',
						'type'    => 'text',
						'title'   => __( 'Out of Stock Text', 'jvm-woocommerce-wishlist' ),
						'default' => 'Out of Stock',
					),
					array(
						'id'      => 'wishlist_page_table_unit_price',
						'type'    => 'switcher',
						'title'   => __( 'Show Unit Price', 'jvm-woocommerce-wishlist' ),
						'default' => true,
					),
					// add switcher for stock status
					array(
						'id'      => 'wishlist_page_table_stock_status',
						'type'    => 'switcher',
						'title'   => __( 'Show Stock Status', 'jvm-woocommerce-wishlist' ),
						'default' => true,
					),

					// removed_cart_notice notice field
					array(
						'id'      => 'removed_cart_notice',
						'type'    => 'text',
						'title'   => __( 'Removed from Cart Notice', 'jvm-woocommerce-wishlist' ),
						'default' => '{product_name} removed from cart',
						'desc'    => __( 'Removed from cart notice message. Use, placeholder <code>{product_name}</code> to display name of the product.', 'jvm-woocommerce-wishlist' ),
					),
					// // add switcher for quantity
					// array(
					// 'id'      => 'wishlist_page_table_quantity',
					// 'type'    => 'switcher',
					// 'title'   => __( 'Show Quantity', 'jvm-woocommerce-wishlist' ),
					// 'default' => true,
					// ),
					// // add switcher for total price
					// array(
					// 'id'      => 'wishlist_page_table_total_price',
					// 'type'    => 'switcher',
					// 'title'   => __( 'Show Total Price', 'jvm-woocommerce-wishlist' ),
					// 'default' => true,
					// ),

					// // add switcher for added date
					// array(
					// 'id'      => 'wishlist_page_table_added_date',
					// 'type'    => 'switcher',
					// 'title'   => __( 'Show Added Date', 'jvm-woocommerce-wishlist' ),
					// 'default' => true,
					// ),
					// // add switcher for show checkbox
					// array(
					// 'id'      => 'wishlist_page_table_checkbox',
					// 'type'    => 'switcher',
					// 'title'   => __( 'Show Checkbox', 'jvm-woocommerce-wishlist' ),
					// 'default' => true,
					// ),
					// add switcher for redirect to cart
					array(
						'id'      => 'wishlist_page_table_redirect_to_cart',
						'type'    => 'switcher',
						'title'   => __( 'Redirect to Cart', 'jvm-woocommerce-wishlist' ),
						'desc'    => __( 'Redirect to cart page after adding to cart from wishlist page.', 'jvm-woocommerce-wishlist' ),
						'default' => true,
					),
					// add switcher for remove if added to cart
					array(
						'id'      => 'wishlist_page_table_remove_if_added_to_cart',
						'type'    => 'switcher',
						'title'   => __( 'Remove if Added to Cart', 'jvm-woocommerce-wishlist' ),
						'desc'    => __( 'Remove item from wishlist if added to cart.', 'jvm-woocommerce-wishlist' ),
						'default' => true,
					),
					// add switcher for "Add All to Cart" button
					array(
						'id'      => 'table_add_all_to_cart',
						'type'    => 'switcher',
						'title'   => __( 'Show "Add All to Cart" Button', 'jvm-woocommerce-wishlist' ),
						'default' => true,
					),
					// add text field for "Add All to Cart" button text
					array(
						'id'         => 'table_add_all_to_cart_text',
						'type'       => 'text',
						'title'      => __( '"Add All to Cart" Button Text', 'jvm-woocommerce-wishlist' ),
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
						'title'    => __( 'Custom CSS', 'jvm-woocommerce-wishlist' ),
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
		// 'title'  => __( 'License', 'deposits-for-woocommerce' ),
		// 'icon'   => 'fas fa-key',
		// 'fields' => array(

		// A Callback Field Example
		// array(
		// 'id'          => 'license-key',
		// 'type'        => 'text',
		// 'title'       => __( 'Purchase Code', 'deposits-for-woocommerce' ),
		// 'placeholder' => __( 'Enter Purchase Code', 'deposits-for-woocommerce' ),
		// 'desc'        => __( 'Enter your license key here, to activate <strong>Bayna - Deposits for WooCommerce PRO</strong>, and get automatic updates and premium support. <a href="' . apply_filters( 'bayna_learn_more', 'https://www.codeixer.com/docs/where-is-my-purchase-code/' ) . '" target="_blank">Learn More</a>', 'deposits-for-woocommerce' ),
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
