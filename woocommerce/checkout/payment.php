<?php
/**
 * Payment methods and the place-order button, at the bottom of the order summary.
 * With no payment method enabled it shows the reference's disabled "Payment integration coming next" button.
 *
 * Overrides woocommerce/templates/checkout/payment.php (root keeps #payment.woocommerce-checkout-payment).
 *
 * @package Priniti
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment mt-5 flex flex-col gap-3">
	<?php if ( WC()->cart && WC()->cart->needs_payment() && ! empty( $available_gateways ) ) : ?>
		<ul class="wc_payment_methods payment_methods methods flex flex-col gap-2">
			<?php
			foreach ( $available_gateways as $gateway ) {
				wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
			}
			?>
		</ul>
	<?php endif; ?>
	<div class="form-row place-order flex flex-col gap-3">
		<noscript>
			<?php esc_html_e( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the Update Totals button before placing your order.', 'woocommerce' ); ?>
			<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'secondary' ) ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>
		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php if ( WC()->cart && WC()->cart->needs_payment() && empty( $available_gateways ) ) : ?>
			<button type="button" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'lg', true ) ); ?>" disabled aria-describedby="place-order-note">Payment integration coming next</button>
			<p id="place-order-note" class="text-xs text-ink-soft">Placing an order is switched off. No order is created and no payment is taken until a payment method is connected.</p>
		<?php else : ?>
			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="' . esc_attr( priniti_button_classes( 'primary', 'lg', true, 'alt' ) ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
