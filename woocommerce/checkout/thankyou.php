<?php
/**
 * Order received (no reference page exists; built from the design's components: card, PriceDisplay rows,
 * OrderJourney, TrackHelp).
 *
 * Overrides woocommerce/templates/checkout/thankyou.php.
 *
 * @package Priniti
 * @version 8.1.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order flex flex-col gap-6">
	<?php if ( $order ) : ?>
		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>
			<div class="rounded-card border border-brand/30 bg-brand-tint p-5 sm:p-6">
				<h2 class="font-display text-lg font-bold">Payment was not completed</h2>
				<p class="mt-1 text-ink-soft"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>
				<div class="mt-4 flex flex-wrap gap-3">
					<?php priniti_button_link( $order->get_checkout_payment_url(), esc_html__( 'Pay', 'woocommerce' ) ); ?>
					<?php if ( is_user_logged_in() ) : ?>
						<?php priniti_button_link( wc_get_page_permalink( 'myaccount' ), esc_html__( 'My account', 'woocommerce' ), 'secondary' ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="relative overflow-hidden rounded-3xl bg-surface p-5 shadow-soft ring-1 ring-line/70 sm:p-7">
				<span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-leaf"></span>
				<div class="flex items-start gap-3">
					<span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-leaf-tint text-leaf"><?php priniti_the_icon( 'check', 'size-6' ); ?></span>
					<div>
						<h2 class="font-display text-xl font-extrabold">Your order has been received</h2>
						<p class="mt-1 text-ink-soft"><?php echo esc_html( sprintf( 'A confirmation has been sent to %s.', $order->get_billing_email() ) ); ?></p>
					</div>
				</div>
				<dl class="mt-5 grid gap-3 text-sm sm:grid-cols-4">
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Order ID</dt><dd class="mt-1 font-semibold"><?php echo esc_html( $order->get_order_number() ); ?></dd></div>
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Date</dt><dd class="mt-1 font-semibold"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></dd></div>
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Total</dt><dd class="mt-1 font-semibold tabular-nums"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd></div>
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Payment</dt><dd class="mt-1 font-semibold"><?php echo esc_html( $order->get_payment_method_title() ?: '—' ); ?></dd></div>
				</dl>
				<p class="mt-4 text-sm text-ink-soft">Keep your order ID: you can <a class="font-semibold text-brand underline-offset-4 hover:underline" href="<?php echo esc_url( priniti_url( '/track-order' ) ); ?>">track this order</a> with it and your email or mobile number.</p>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
	<?php else : ?>
		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>
	<?php endif; ?>
	<?php priniti_track_help(); ?>
</div>
