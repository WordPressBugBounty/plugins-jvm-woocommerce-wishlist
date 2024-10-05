<?php
/**
 * The Template for displaying wishlist popup.
 *
 * This template can be overridden by copying it to yourtheme/wishlist/wishlist-popup.php.
 *
 * @version 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php if ( isset( $args['already_in_wishlist'] ) && $args['already_in_wishlist'] == 1 ) : ?>
	<div class="modal-wishlist-icon"></div>
	<p class="modal-product-info"><?php echo \CIXW_WISHLIST\Wishlist::already_in_wishlist_text( $args['product_id'] ); ?></p>
<?php endif; ?>

<?php if ( isset( $args['added'] ) ) : ?>
	<div class="modal-wishlist-icon"></div>
	<p class="modal-product-info"><?php echo \CIXW_WISHLIST\Wishlist::added_to_wishlist_text( $args['product_id'] ); ?></p>
<?php endif; ?>

<?php if ( isset( $args['removed'] ) ) : ?>
	<div class="not modal-wishlist-icon"></div>
	<p class="modal-product-info"><?php echo \CIXW_WISHLIST\Wishlist::removed_from_wishlist_text( $args['product_id'] ); ?></p>
<?php endif; ?>

<?php if ( isset( $args['product_id'] ) ) : ?>
	<div class="modal-action-btns">
		<a href="<?php echo esc_url( get_the_permalink( cixww_get_option( 'wishlist_page' ) ) ); ?>" class="button modal-btn-view-wishlish"><?php echo esc_html( cixww_get_option( 'product_view_wishlist_text' ) ); ?></a>
	</div>
<?php endif; ?>
