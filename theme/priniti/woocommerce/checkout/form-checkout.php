<?php
/**
 * Checkout form in the design's layout (reference CheckoutView): details on the left, order summary with
 * payment on the right (sticky on large screens, first on phones).
 *
 * Overrides woocommerce/templates/checkout/form-checkout.php. Keeps WooCommerce's form name, classes,
 * hooks and the #order_review container that checkout.js refreshes.
 *
 * @package Priniti
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout grid gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:gap-10" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'Checkout', 'woocommerce' ); ?>" novalidate>
	<div class="order-2 flex flex-col gap-8 lg:order-1" id="customer_details">
		<?php if ( $checkout->get_checkout_fields() ) : ?>
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
			<?php do_action( 'woocommerce_checkout_billing' ); ?>
			<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
		<?php endif; ?>

		<?php
		$status = array_column( priniti_checkout_status(), 'done', 'id' );
		if ( empty( $status['shipping'] ) ) :
			?>
			<section aria-labelledby="delivery-heading" class="rounded-card border border-dashed border-line p-5">
				<h2 id="delivery-heading" class="font-display text-xl font-semibold">Shipping</h2>
				<p class="mt-1 text-sm text-ink-soft">To be calculated. Delivery options, charges and timelines will appear here once shipping rules are configured.</p>
			</section>
		<?php endif; ?>
		<?php if ( empty( $status['payment'] ) ) : ?>
			<section aria-labelledby="payment-heading" class="rounded-card border border-dashed border-line p-5">
				<h2 id="payment-heading" class="font-display text-xl font-semibold">Payment</h2>
				<p class="mt-1 text-sm text-ink-soft">Payment integration is coming next. Payment methods will appear here once a payment gateway is connected. No payment is taken on this page.</p>
			</section>
		<?php endif; ?>
	</div>

	<aside class="order-1 lg:order-2 lg:sticky lg:top-28 lg:self-start">
		<div class="rounded-card border border-line bg-surface p-5 sm:p-6">
			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
			<h2 id="order_review_heading" class="font-display text-lg font-semibold"><?php esc_html_e( 'Order summary', 'priniti' ); ?></h2>
			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>
			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="mt-3 block text-center text-sm font-semibold text-brand underline-offset-4 hover:underline">Back to cart</a>
		</div>
	</aside>
</form>
<?php
do_action( 'woocommerce_after_checkout_form', $checkout );
