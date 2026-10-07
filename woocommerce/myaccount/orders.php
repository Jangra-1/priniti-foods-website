<?php
/**
 * Orders list as design cards (responsive without a wide table).
 *
 * Overrides woocommerce/templates/myaccount/orders.php.
 *
 * @package Priniti
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>
<h2 class="font-display text-xl font-extrabold">Orders</h2>
<?php if ( $has_orders ) : ?>
	<ul role="list" class="mt-4 flex flex-col divide-y divide-line rounded-card border border-line">
		<?php
		foreach ( $customer_orders->orders as $customer_order ) :
			$order = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			if ( ! $order ) {
				continue;
			}
			$actions = wc_get_account_orders_actions( $order );
			?>
			<li class="flex flex-wrap items-center justify-between gap-3 p-4">
				<div>
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="font-display font-bold hover:text-brand"><?php echo esc_html( '#' . $order->get_order_number() ); ?></a>
					<p class="text-xs text-ink-soft"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) . ' · ' . wc_get_order_status_name( $order->get_status() ) . ' · ' . sprintf( _n( '%s item', '%s items', $order->get_item_count(), 'priniti' ), $order->get_item_count() ) ); ?></p>
				</div>
				<div class="flex items-center gap-3">
					<span class="font-semibold tabular-nums"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
					<?php foreach ( $actions as $key => $action ) : ?>
						<a href="<?php echo esc_url( $action['url'] ); ?>" class="<?php echo esc_attr( priniti_button_classes( 'view' === $key ? 'secondary' : 'primary', 'sm' ) . ' ' . sanitize_html_class( $key ) ); ?>"><?php echo esc_html( $action['name'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>
	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="mt-4 flex gap-2">
			<?php if ( 1 !== $current_page ) : ?>
				<?php priniti_button_link( wc_get_endpoint_url( 'orders', $current_page - 1 ), esc_html__( 'Previous', 'woocommerce' ), 'secondary', 'sm' ); ?>
			<?php endif; ?>
			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<?php priniti_button_link( wc_get_endpoint_url( 'orders', $current_page + 1 ), esc_html__( 'Next', 'woocommerce' ), 'secondary', 'sm' ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div class="mt-4 flex flex-col items-center gap-3 rounded-card border border-dashed border-line px-6 py-10 text-center">
		<p class="font-display text-lg font-semibold">No orders yet</p>
		<?php priniti_button_link( priniti_url( '/shop' ), 'Browse products' ); ?>
	</div>
<?php endif; ?>
<?php
do_action( 'woocommerce_after_account_orders', $has_orders );
