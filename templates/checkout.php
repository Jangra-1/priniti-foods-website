<?php
/**
 * /checkout (reference/nextjs/app/checkout/page.tsx) on WooCommerce's classic checkout, rendered with the
 * design's templates (woocommerce/checkout/*). While payment, shipping or tax are not configured, the
 * "Checkout is not live" panel lists what is missing (IntegrationStatus), computed from the live settings.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$received = function_exists( 'is_order_received_page' ) && is_order_received_page();

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php if ( $received ) : ?>
		<?php priniti_breadcrumbs( array( array( 'label' => 'Order received' ) ) ); ?>
		<h1 class="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">Thank you</h1>
	<?php else : ?>
		<?php priniti_breadcrumbs( array( array( 'label' => 'Cart', 'href' => priniti_url( '/cart' ) ), array( 'label' => 'Checkout' ) ) ); ?>
		<h1 class="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">Checkout</h1>
	<?php endif; ?>
	<div class="flex flex-col gap-8">
		<?php
		if ( ! $received ) {
			priniti_integration_status();
		}
		echo do_shortcode( '[woocommerce_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce output.
		?>
	</div>
</div>
<?php
get_footer();
