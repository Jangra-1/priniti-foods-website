<?php
/**
 * WooCommerce presentation: theme support, styles, form fields, checkout readiness.
 * Business rules (checkout fields, India-only, quantities, accounts) live in priniti-core.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'woocommerce', array( 'product_grid' => array( 'default_columns' => 4 ) ) );
	}
);

if ( class_exists( 'WooCommerce' ) ) {
	priniti_register_woocommerce_hooks();
}

/** Hooks that only make sense with WooCommerce active. */
function priniti_register_woocommerce_hooks(): void {
	/** Every page is styled by the theme; WooCommerce's default stylesheets would fight the design. */
	add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

	/** Native selects (the design's Select), not select2, on checkout and account forms. */
	add_action(
		'wp_enqueue_scripts',
		static function (): void {
			wp_dequeue_style( 'select2' );
			wp_dequeue_script( 'selectWoo' );
			wp_dequeue_script( 'select2' );
		},
		100
	);

	/** The checkout has no coupon or login prompts in the design. */
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

	/**
	 * Form fields (checkout, addresses, account details) in the design's Input/Select style.
	 */
	add_filter(
		'woocommerce_form_field_args',
		static function ( array $args ): array {
			$args['class']       = array_merge( (array) $args['class'], array( 'flex', 'flex-col', 'gap-1.5' ) );
			$args['label_class'] = array_merge( (array) $args['label_class'], array( 'text-sm', 'font-medium' ) );
			$is_choice           = in_array( $args['type'], array( 'checkbox', 'radio' ), true );
			if ( ! $is_choice ) {
				$args['input_class'] = array_merge( (array) $args['input_class'], 'textarea' === $args['type'] ? array( 'w-full', 'rounded-xl', 'border', 'border-line', 'bg-surface', 'px-4', 'py-3', 'text-base', 'focus:border-ink' ) : array( 'h-12', 'w-full', 'rounded-xl', 'border', 'border-line', 'bg-surface', 'px-5', 'text-base', 'focus:border-ink' ) );
			} else {
				$args['input_class'] = array_merge( (array) $args['input_class'], array( 'size-4', 'accent-brand' ) );
			}
			return $args;
		}
	);
}

/**
 * What still has to be configured before checkout can take real orders (reference data/integrations.ts),
 * computed from the live WooCommerce settings instead of hard-coded.
 *
 * @return array<int, array{id:string, label:string, detail:string, done:bool}>
 */
function priniti_checkout_status(): array {
	static $status = null;
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}
	if ( null !== $status ) {
		return $status;
	}
	$priced = (bool) array_filter( priniti_catalog(), static fn ( $p ) => (bool) priniti_purchasable_variant( $p ) );

	$shipping = false;
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		foreach ( $zone['shipping_methods'] ?? array() as $method ) {
			$shipping = $shipping || 'yes' === $method->enabled;
		}
	}
	$rest = new WC_Shipping_Zone( 0 );
	foreach ( $rest->get_shipping_methods( true ) as $method ) {
		$shipping = true;
	}

	$tax = ! wc_tax_enabled() || (bool) WC_Tax::get_rates_for_tax_class( '' );

	$gateways = array_filter( WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array(), static fn ( $g ) => 'yes' === $g->enabled );

	$reference = array_column( priniti_data( 'integrations' ), null, 'id' );
	$item      = static fn ( string $id, bool $done ) => array(
		'id'     => $id,
		'label'  => $reference[ $id ]['label'],
		'detail' => $reference[ $id ]['detail'],
		'done'   => $done,
	);

	return $status = array(
		$item( 'pricing', $priced ),
		$item( 'shipping', $shipping ),
		$item( 'tax', $tax ),
		$item( 'payment', (bool) $gateways ),
		array( 'id' => 'orders', 'label' => 'Order backend and database', 'detail' => 'WooCommerce stores and manages orders.', 'done' => true ),
		array( 'id' => 'notifications', 'label' => 'Order confirmation (email / WhatsApp)', 'detail' => 'WooCommerce sends order emails. WhatsApp messages are not connected.', 'done' => true ),
	);
}

/** True when shipping rates, tax rules and a payment method are all configured. */
function priniti_checkout_ready(): bool {
	foreach ( priniti_checkout_status() as $s ) {
		if ( ! $s['done'] ) {
			return false;
		}
	}
	return true;
}

/** IntegrationStatus: shown on the checkout until every item is configured. */
function priniti_integration_status(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$status  = priniti_checkout_status();
	$pending = array_filter( $status, static fn ( $s ) => ! $s['done'] );
	if ( ! $pending ) {
		return;
	}
	$payment_off = (bool) array_filter( $pending, static fn ( $s ) => 'payment' === $s['id'] );
	?>
	<section aria-labelledby="not-live-heading" class="rounded-card border border-brand/30 bg-brand-tint p-5 sm:p-6">
		<div class="flex items-start gap-3">
			<?php priniti_the_icon( 'triangle-alert', 'mt-0.5 size-6 shrink-0 text-brand' ); ?>
			<div>
				<h2 id="not-live-heading" class="font-display text-lg font-bold"><?php echo esc_html( $payment_off ? 'Checkout is not live' : 'Checkout setup is incomplete' ); ?></h2>
				<p class="mt-1 text-ink-soft"><?php echo esc_html( $payment_off ? 'You can review your order and details here, but no order can be placed and no payment is taken until the items below are set up.' : 'Some checkout details are still being set up.' ); ?></p>
			</div>
		</div>
		<ul role="list" class="mt-4 grid gap-2 sm:grid-cols-2">
			<?php foreach ( $status as $s ) : ?>
				<li class="rounded-xl bg-surface p-3">
					<p class="flex items-center justify-between gap-2 text-sm font-semibold">
						<?php echo esc_html( $s['label'] ); ?>
						<span class="<?php echo esc_attr( priniti_cx( 'rounded-full px-2 py-0.5 text-xs font-semibold', $s['done'] ? 'bg-leaf-tint text-leaf' : 'bg-ink/8 text-ink-soft' ) ); ?>"><?php echo esc_html( $s['done'] ? 'Ready' : 'Pending' ); ?></span>
					</p>
					<p class="mt-1 text-xs text-ink-soft"><?php echo esc_html( $s['done'] ? ( 'pricing' === $s['id'] ? 'Prices are published.' : ( in_array( $s['id'], array( 'orders', 'notifications' ), true ) ? $s['detail'] : 'Configured.' ) ) : $s['detail'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}
