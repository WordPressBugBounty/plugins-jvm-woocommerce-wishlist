<?php

use CIXW_WISHLIST\Helper;
/**
 * Get the value of a settings field
 *
 * @param  string $option  settings field name
 * @param  string $section the section name this field belongs to
 * @param  string $default default text if it's not found
 * @return mixed
 */
function cixww_get_option( $option = '', $default = '', $section = 'cixwishlist_settings' ) {
	$options = get_option( $section );
	return ( isset( $options[ $option ] ) ) ? $options[ $option ] : $default;
}

/**
 * Get the wishlist page link
 *
 * @return string
 */
function cixww_get_wishlist_page_link() {
	$wishlist_page_id = cixww_get_option('wishlist_page');
	$wishlist_page_link = get_permalink($wishlist_page_id);
	return $wishlist_page_link;
}