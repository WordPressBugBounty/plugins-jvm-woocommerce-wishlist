=== JVM WooCommerce Wishlist ===
Contributors: im_niloy,codeixer,jorisvanmontfort
Tags: wishlist for woocommerce,wishlist, woocommerce wishlist,ti wishlist, add to wishlist
Requires at least: 5.0
Tested up to: 6.5.4
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Supercharge your sales with WooCommerce Wishlist - a powerful tool that empowers customers to create wishlists and enhances their shopping experience.

== Description ==

Enhance your e-commerce store's functionality with WooCommerce Wishlist - the ultimate tool that adds a powerful and lightweight wishlist feature. Improve your customer's shopping experience and boost your sales with this essential addition to your online store. 🚀

[__Live Demo__](https://wp.codeixer.com) | [__Documentation__](https://www.codeixer.com/docs-category/wishlist-for-wc) | [__Support__](https://www.codeixer.com/contact-us/)


WooCommece wishlist is a helpful plugin that allows customers to create a personalized list of products they want to purchase. It simplifies the shopping experience by enabling individuals to save items they may want to buy later rather than searching for them again. By using a wishlist, customers can easily keep track of the products they are considering and make informed decisions about what to purchase. Additionally, it provides retailers with valuable insights into their customers' preferences, helping them better understand their target audience and optimize their marketing strategies.


== 🌟 Wishlist for WooCommerce Free Features ==

* Customize the position of the wishlist button
* Ability to add wishlist buttons to any products
* Auto Remove from wishlist when added to cart
* Wishlist tables with customizable columns
* Product variation support
* Shortcode support
* Pop-up wishlist view
* Fast loading and efficient cache performance.
* Override wishlist template
* Wishlists stored for up to [x] days for guests
* Custom CSS styling

[__Live Demo__](https://wp.codeixer.com) | [__Documentation__](https://www.codeixer.com/docs-category/wishlist-for-wc) | [__Support__](https://www.codeixer.com/contact-us/)

**Remove from wishlist when added to cart:** Removes a product from the wishlist when it is added to the cart, saving you the hassle of manually managing wishlist.

🎨 **Custom CSS styling:** supports custom CSS styling, allowing you to style your wishlist to match your website's theme. 

📝 **[Shortcode](https://www.codeixer.com/docs/shortcodes/) support:** The plugin supports product variation and shortcode, providing flexibility and convenience in creating and managing your wishlist.

**Pop-up wishlist:** Show pop-up with product details when wishlist action triggered.

🎨 **[Override wishlist](https://www.codeixer.com/docs/overwrite-wishlist-template/):** Override the wishlist template from a child theme, giving complete control over the look and feel of your wishlist.

📅 **Guest Wishlist:** stores wishlists for guests for up to [x] days, ensuring that their wishlists remain available even after leaving your website.

🧑‍💻 **[Developer-friendly hooks for actions and filters](https://www.codeixer.com/docs/for-developers/):** The plugin is developer-friendly, offering hooks for actions and filters that allow developers to extend and customize the plugin's functionality according to their needs.

## 🔥 WHAT’S NEXT ##

If you like this Wishlist plugin, then consider checking out our other free plugins:

[Product gallery slider for WooCommerce](https://wordpress.org/plugins/woo-product-gallery-slider/) – Best product image gallery slider for WooCommerce. It shows your WooCommerce products with an image carousel slider. Beautiful style, increase sales and get customer attention.

[Custom Order Status Manager for WooCommerce](https://wordpress.org/plugins/bp-custom-order-status-for-woocommerce/) allows you to create, delete and edit order statuses to control the flow of your orders better.

[Bayna - Deposits & Partial Payments for WooCommerce](https://www.codeixer.com/deposits-payment-plugin-for-woocommerce/?utm_source=wp&utm_medium=site&utm_campaign=free_plugin) plugin allows customers to pay for WooCommerce products using a partial payment.


== Installation ==

To install the WooCommerce Wishlist plugin, please follow these steps:

1. Unzip the downloaded zip file.
2. Upload the plugin folder to the wp-content/plugins/ directory on your WordPress site.
3. Activate the Wishlist for WooCommerce plugin from the Plugins page.
4. After activation, you will see a new submenu called “Wishlist” under the “Codeixer” menu. Here you can configure all the plugin settings to your preference.

== Screenshots ==
1. Shop page
2. Single Product Page
3. Pop-up View
4. Wishlist Template
5. Wishlist Settings


== Changelog ==

= 2.0.3 - 15 Jul 24 =

* Added: Global scope is added 'cix_wishlist_init()' so that the wishlist init function can be used by other plugins. 
* Added: URL parameters are supported for direct add to the wishlist via a link. For example https://domain.com/?add-to-wishlist=product_ID
* Added: Singleton Pattern



= 2.0.2 - 23 Jun 24 =

* Added: 'cixww_get_wishlist_page_link' function to get the wishlist page link.
* Added: Display admin to notice to make sure the wishlist page is set.
* Fixed: Minor CSS Padding issue for wishlist button
* Compatibility with WooCommerce 9.0.1



= 2.0.1 - 06 may 24 =

* Fixed: 'woocommerce' class has been added to set the default theme style for the wishlist page.

= 2.0 - 23 May 24 =

- New plugin settings page with lots of settings added
- Setup wizard added for quick onboarding
- All codes have been rewritten as part of an update
- HPOS compatibility has been declared
- Compatibility with WooCommerce 8.8.3 ensured


= 1.3.6 - 23 Sep 22 =

Fixed: default WooCommerce style for wishlist page table
Compatibility with WooCommerce 7.1


= 1.3.5 - 23 Sep 22 =

Bug fix
Added: appsero insights
Compatibility with WooCommerce 6.9.3


= 1.3.4 =
Added a new filter for modifying the icon HTML: jvm_add_to_wishlist_icon_html

= 1.3.3 =
Bug fix php error notice in ajax/www-ajax-functions.php

= 1.3.2 =
Added an optional $product _id parameter to the jvm_woocommerce_add_to_wishlist function for use of this function outside of the loop, for increased flexibility.

= 1.3.1 =
Bug fix. Whitespace in main plugin file. Please update.

= 1.3.0 =
Fixed a fatal error in update 1.2.9. Please upgrade if you are on 1.2.9.

= 1.2.9 =
Some slight changes to wishlist storage. The cookie is now always cleared on logout. Also newly added products when not logged in will be added after login.

= 1.2.8 =
Bug fix: When logged in last item on wishlist would need to be removed twice. Should be fixed now. Also no ajax requests will de done if a user is not logged in to reduce overhead.

= 1.2.7 =
Security fix. User ID passed to ajax calls must match the current user.

= 1.2.6 =
Another whitespace fix.

= 1.2.5 =
Fixed a whitespace issue in front end link.

= 1.2.4 =
Added a partially Japanese translation.

= 1.2.3 =
Added a grunt task for automated POT files.
Added and a Dutch translation and auto generated POT file.

= 1.2.2 =
Added a dontation button.

= 1.2.1 =
Added a Dutch translation.

= 1.2.0 =
Fixed a bug where the custom wishlist template would not load from the (child) theme.

= 1.1.0 =
Fixed a bug  "No products on your wishlist yet." shown with products in wishlist on other pages than the main wishlist page (plugin settings).

= 1.0.0 =
Initial release

= Stable =
1.0.0