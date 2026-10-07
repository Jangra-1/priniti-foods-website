<?php
/**
 * Shipping row in the order summary (design: a summary row; options as the design's radio rows).
 *
 * Overrides woocommerce/templates/cart/cart-shipping.php. Input names/classes are what checkout.js listens to.
 *
 * @package Priniti
 * @version 8.8.0
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = $formatted_destination ?? WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
?>
<div class="woocommerce-shipping-totals shipping flex flex-col gap-2 text-sm">
	<dt class="text-ink-soft"><?php echo wp_kses_post( $package_name ); ?></dt>
	<dd data-title="<?php echo esc_attr( $package_name ); ?>">
		<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
			<ul id="shipping_method" class="woocommerce-shipping-methods flex flex-col gap-1.5">
				<?php foreach ( $available_methods as $method ) : ?>
					<li class="flex items-center gap-2.5">
						<?php
						if ( 1 < count( $available_methods ) ) {
							printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method size-4 accent-brand" %4$s />', (int) $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', (int) $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						printf( '<label for="shipping_method_%1$s_%2$s" class="flex-1">%3$s</label>', (int) $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						do_action( 'woocommerce_after_shipping_rate', $method, $index );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
			<p class="text-ink-soft">Enter your address to see delivery options.</p>
		<?php else : ?>
			<p class="text-ink-soft"><?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce' ) ) ); ?></p>
		<?php endif; ?>
	</dd>
</div>
