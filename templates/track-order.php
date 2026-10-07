<?php
/**
 * /track-order (reference/nextjs/app/track-order/page.tsx). Looks up a WooCommerce order by order number plus the
 * billing email or mobile (priniti-core) and shows its stage on the order journey.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$result = function_exists( 'priniti_core_form_result' ) ? priniti_core_form_result( 'track' ) : null;
$values = $result['values'] ?? array();
$errors = $result['errors'] ?? array();
$order  = $result['order'] ?? null;

get_header();
priniti_part( 'layout/page-header', array( 'eyebrow' => 'Order status', 'title' => 'Track Your Order', 'description' => 'Enter your order details to check your order status.' ) );
?>
<section class="bg-canvas py-6 lg:py-9">
	<?php priniti_container_open( 'flex flex-col gap-6 lg:gap-8' ); ?>
		<div class="mx-auto w-full max-w-xl">
			<form method="post" action="<?php echo esc_url( priniti_url( '/track-order' ) ); ?>" novalidate data-priniti-form class="flex flex-col gap-4 rounded-3xl bg-surface p-5 shadow-soft ring-1 ring-line/70 sm:p-7">
				<?php priniti_form_hidden_fields( 'track' ); ?>
				<?php
				priniti_input( array( 'label' => 'Order ID *', 'name' => 'order_id', 'value' => $values['order_id'] ?? '', 'error' => $errors['order_id'] ?? '', 'validate' => 'order-id', 'attrs' => array( 'autocomplete' => 'off', 'required' => true ) ) );
				priniti_input( array( 'label' => 'Email or Mobile *', 'name' => 'contact', 'value' => $values['contact'] ?? '', 'error' => $errors['contact'] ?? '', 'validate' => 'email-or-mobile', 'attrs' => array( 'autocomplete' => 'email', 'required' => true ) ) );
				?>
				<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', true, 'h-12' ) ); ?>">Track Order</button>
				<?php priniti_form_notice( $result['message'] ?? '', $result['tone'] ?? 'info' ); ?>
			</form>
		</div>

		<div class="rounded-3xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-7">
			<h2 class="mb-5 text-center font-display text-xl font-extrabold tracking-tight sm:text-2xl"><?php echo esc_html( $order ? 'Order #' . $order['number'] : 'Your order journey' ); ?></h2>
			<?php if ( $order ) : ?>
				<dl class="mb-6 grid gap-3 text-sm sm:grid-cols-3">
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Placed on</dt><dd class="mt-1 font-semibold"><?php echo esc_html( $order['date'] ); ?></dd></div>
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Status</dt><dd class="mt-1 font-semibold"><?php echo esc_html( $order['status'] ); ?></dd></div>
					<div class="rounded-xl bg-canvas p-3"><dt class="text-xs font-bold uppercase tracking-wide text-ink-soft">Total</dt><dd class="mt-1 font-semibold tabular-nums"><?php echo esc_html( $order['total'] ); ?></dd></div>
				</dl>
				<?php if ( $order['closed'] ) : ?>
					<p class="rounded-xl bg-brand-tint px-4 py-3 text-center text-sm font-semibold text-brand"><?php echo esc_html( $order['closed'] ); ?></p>
				<?php else : ?>
					<?php priniti_order_journey( (int) $order['stage'] ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php priniti_order_journey( 0 ); ?>
			<?php endif; ?>
		</div>

		<?php priniti_track_help(); ?>
	<?php priniti_container_close(); ?>
</section>
<?php
get_footer();
