<?php
/**
 * Order summary on the checkout (reference OrderSummary with showLines). Root keeps the
 * woocommerce-checkout-review-order-table class: checkout.js replaces it on every update.
 *
 * Shipping, tax and the total follow the live configuration: while a part is not configured it is labelled
 * the way the reference does ("To be calculated", "To be confirmed before ecommerce launch", "Not final").
 *
 * Overrides woocommerce/templates/checkout/review-order.php.
 *
 * @package Priniti
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

$status    = array_column( priniti_checkout_status(), 'done', 'id' );
$row       = 'flex items-baseline justify-between gap-4 text-sm';
$cart      = WC()->cart;
$count     = $cart->get_cart_contents_count();
$savings   = 0.0;
foreach ( $cart->get_cart() as $item ) {
	$p        = $item['data'];
	$regular  = (float) $p->get_regular_price();
	$price    = (float) $p->get_price();
	$savings += $regular > $price ? ( $regular - $price ) * (int) $item['quantity'] : 0;
}
?>
<div class="woocommerce-checkout-review-order-table">
	<ul role="list" class="mt-4 flex flex-col divide-y divide-line">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}
			$image_id = $_product->get_image_id() ?: ( $_product->get_parent_id() ? wc_get_product( $_product->get_parent_id() )->get_image_id() : 0 );
			$image    = $image_id ? array( 'src' => (string) wp_get_attachment_image_url( (int) $image_id, 'woocommerce_thumbnail' ), 'alt' => '' ) : null;
			$meta     = wc_get_formatted_cart_item_data( $cart_item, true );
			?>
			<li class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item flex gap-3 py-3 first:pt-0', $cart_item, $cart_item_key ) ); ?>">
				<div class="relative size-14 shrink-0 overflow-hidden rounded-lg border border-line bg-surface"><?php priniti_product_image( $image, $_product->get_name(), '56px' ); ?></div>
				<div class="min-w-0 flex-1">
					<p class="line-clamp-2 text-sm font-semibold leading-snug"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></p>
					<?php if ( $meta ) : ?>
						<p class="text-xs text-ink-soft"><?php echo esc_html( str_replace( "\n", ' · ', trim( $meta ) ) ); ?></p>
					<?php endif; ?>
					<p class="mt-0.5 text-xs tabular-nums text-ink-soft"><?php echo esc_html( sprintf( 'Qty %d × %s', $cart_item['quantity'], wp_strip_all_tags( wc_price( (float) $_product->get_price() ) ) ) ); ?></p>
				</div>
				<p class="shrink-0 text-sm font-semibold tabular-nums"><span class="sr-only">Line total </span><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?></p>
			</li>
			<?php
		endforeach;
		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</ul>

	<dl class="mt-3 flex flex-col gap-3 border-t border-line pt-4">
		<div class="<?php echo esc_attr( $row ); ?>">
			<dt class="text-ink-soft"><?php echo esc_html( sprintf( 'Subtotal (%d %s)', $count, 1 === $count ? 'item' : 'items' ) ); ?></dt>
			<dd class="font-semibold tabular-nums"><?php wc_cart_totals_subtotal_html(); ?></dd>
		</div>
		<?php if ( $savings > 0 ) : ?>
			<div class="<?php echo esc_attr( $row ); ?>"><dt class="text-ink-soft">Savings on MRP</dt><dd class="font-semibold tabular-nums text-leaf"><?php echo wp_kses_post( wc_price( $savings ) ); ?></dd></div>
		<?php endif; ?>
		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<div class="<?php echo esc_attr( $row ); ?>"><dt class="text-ink-soft"><?php wc_cart_totals_coupon_label( $coupon ); ?></dt><dd class="font-semibold tabular-nums text-leaf"><?php wc_cart_totals_coupon_html( $coupon ); ?></dd></div>
		<?php endforeach; ?>

		<?php if ( $cart->needs_shipping() && $cart->show_shipping() && ! empty( $status['shipping'] ) ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php else : ?>
			<div class="<?php echo esc_attr( $row ); ?>"><dt class="text-ink-soft">Shipping</dt><dd class="text-right font-medium text-ink-soft">To be calculated</dd></div>
		<?php endif; ?>

		<?php foreach ( $cart->get_fees() as $fee ) : ?>
			<div class="<?php echo esc_attr( $row ); ?>"><dt class="text-ink-soft"><?php echo esc_html( $fee->name ); ?></dt><dd class="font-semibold tabular-nums"><?php wc_cart_totals_fee_html( $fee ); ?></dd></div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! empty( $status['tax'] ) && ! $cart->display_prices_including_tax() ) : ?>
			<?php foreach ( $cart->get_tax_totals() as $code => $tax ) : ?>
				<div class="<?php echo esc_attr( $row ); ?>"><dt class="text-ink-soft"><?php echo esc_html( $tax->label ); ?></dt><dd class="font-semibold tabular-nums"><?php echo wp_kses_post( $tax->formatted_amount ); ?></dd></div>
			<?php endforeach; ?>
		<?php elseif ( empty( $status['tax'] ) ) : ?>
			<div class="<?php echo esc_attr( $row ); ?>"><dt class="shrink-0 text-ink-soft">Tax (GST)</dt><dd class="max-w-[12rem] text-right text-xs font-medium leading-snug text-ink-soft">To be confirmed before ecommerce launch</dd></div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
		<div class="<?php echo esc_attr( $row . ' border-t border-line pt-3' ); ?>">
			<dt class="font-semibold">Total</dt>
			<?php if ( ! empty( $status['payment'] ) ) : ?>
				<dd class="font-display text-base font-bold tabular-nums"><?php wc_cart_totals_order_total_html(); ?></dd>
			<?php else : ?>
				<dd class="font-display text-base font-bold text-ink-soft">Not final</dd>
			<?php endif; ?>
		</div>
		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</dl>
	<?php if ( ! priniti_checkout_ready() ) : ?>
		<p class="mt-3 text-xs leading-relaxed text-ink-soft">Shipping, tax and payment are not finalized yet, so the subtotal above is not a payable amount.</p>
	<?php endif; ?>
</div>
