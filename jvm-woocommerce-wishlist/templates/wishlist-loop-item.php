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
$product    = wc_get_product( $args['product_id'] );
$product_id = $args['product_id'];
$permalink  = get_the_permalink( $product_id );
?>
<tr class="jvm-woocommerce-wishlist-product">

	<td class="product-remove">
		<a href="#"
		class="remove www-remove"
		title="<?php esc_html_e( 'Remove this item', 'jvm-woocommerce-wishlist' ); ?>"
		data-product-title="<?php echo esc_attr( get_the_title( $product_id ) ); ?>"
		data-product-id="<?php echo absint( $product_id ); ?>">
			&times;
		</a>
	</td>

	<td class="product-thumbnail">
		<a href="<?php echo esc_url( $permalink ); ?>">
			<?php echo $product->get_image(); ?>
		</a>
	</td>

	<td class="product-name" data-title="<?php esc_html_e( 'Product', 'jvm-woocommerce-wishlist' ); ?>">
		<a href="<?php echo esc_url( $permalink ); ?>">
			<?php echo get_the_title( $product_id ); ?>
		</a>
	</td>
	<?php if ( cixww_get_option( 'wishlist_page_table_unit_price' ) ) : ?>
		<td class="product-price" data-title="<?php esc_html_e( 'Price', 'jvm-woocommerce-wishlist' ); ?>">
			
				<?php
				if ( $product->get_price() != '0' ) {
					echo wp_kses_post( $product->get_price_html() );
				}
				?>
			
		</td>
	<?php endif; ?>
	<?php if ( cixww_get_option( 'wishlist_page_table_stock_status' ) ) : ?>
	<td class="product-stock-status">
		<?php
		$availability = $product->get_availability();
		$stock_status = $availability['class'];

		if ( $stock_status == 'out-of-stock' ) {
			$stock_status = 'Out';
			echo '<span class="wishlist-out-of-stock">' . esc_html( cixww_get_option( 'wishlist_out_of_stock_text' ) ) . '</span>';
		} else {
			$stock_status = 'In';
			echo '<span class="wishlist-in-stock">' . esc_html( cixww_get_option( 'wishlist_in_stock_text' ) ) . '</span>';
		}
		?>
	</td>
	<?php endif; ?>
	
	<td class="product-add-to-cart">
		<?php if ( $stock_status != 'Out' ) : ?>
            <button class="button cixww-add-to-cart" name="cixww-add-to-cart" value="<?php echo absint( $product_id ); ?>" title="Add to Cart"><?php echo esc_html( cixww_get_option( 'wishlist_page_table_add_to_cart_text' ) ); ?></button>
		<?php endif; ?>
	</td>
</tr>