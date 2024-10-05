<?php
/**
 * Template to render the wishlit table.
 *
 * @version 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php
$product_ids = \CIXW_WISHLIST\Wishlist::wishlist_product_ids();

?>
<div id="cixwishlist-guest-notice"></div>
<?php echo ( cixww_get_option( 'wishlist_name' ) ) ? '<h2>' . esc_html( cixww_get_option( 'wishlist_name' ) ) . '</h2>' : ''; ?>
<div id="cixwishlist-notice" class="cixwishlist-notice"></div>
<div class="jvm-woocommerce-wishlist-container woocommerce woocommerce-cart-form">
<?php
if ( ! empty( $product_ids ) ) {
	?>
	<table class="jvm-woocommerce-wishlist-table shop_table shop_table_responsive cart woocommerce-cart-form__contents">
		<thead>
			<tr>
				<th class="product-remove">&nbsp;</th>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name"><?php esc_html_e( 'Product', 'jvm-woocommerce-wishlist' ); ?></th>

				<?php if ( cixww_get_option( 'wishlist_page_table_unit_price' ) ) : ?>
				<th class="product-price"><?php esc_html_e( 'Price', 'jvm-woocommerce-wishlist' ); ?></th>
				<?php endif; ?>

				<?php if ( cixww_get_option( 'wishlist_page_table_stock_status' ) ) : ?>
				<th class="product-stock-status"><?php esc_html_e( 'Stock Status', 'jvm-woocommerce-wishlist' ); ?></th>
				<?php endif; ?>

				<th class="product-add-to-cart"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			do_action( 'cix_woocommerce_wishlist_before_wishlist_contents' );

			?>

			<?php

			foreach ( $product_ids as $product_id ) {

				$product = wc_get_product( $product_id );

				if ( $product && $product->exists() ) {
					
				\CIXW_WISHLIST\Wishlist::woocommerce_wishlist_locate_template( 'wishlist-loop-item.php', array( 'product_id' => $product_id));
				}
			}

			do_action( 'cix_woocommerce_wishlist_after_wishlist_contents' );

			?>
		</tbody>
		<tfoot>
			
			<?php if ( cixww_get_option( 'table_add_all_to_cart' ) ) : ?>
			<tr>
				<td colspan="6">
					
					<a href="#" class="button cixww-wishlist-all-cart" data-cart-all="true" ><?php echo esc_html( cixww_get_option( 'table_add_all_to_cart_text' ) ); ?></a>
					
				</td>
			</tr>
			<?php endif; ?>
		</tfoot>
	</table>
	<?php

}
	$class = empty( $product_ids ) ? '' : ' hidden';

?>
	<div class="empty-wishlist<?php echo $class; ?>">
		<p><?php echo esc_html( cixww_get_option( 'wishlist_page_no_item_text' ) ); ?></p>

		<p class="return-to-shop">
			<a class="button wc-backward" href="<?php echo get_permalink( get_option( 'woocommerce_shop_page_id' ) ); ?>"><?php _e( 'Return to shop', 'jvm-woocommerce-wishlist' ); ?></a>
		</p>
	</div>
</div>