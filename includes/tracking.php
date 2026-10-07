<?php
/**
 * Order tracking (/track-order) and the order journey.
 *
 * Adds three order statuses so the team can move an order along the design's journey from the Orders screen:
 * Packed, Shipped, Delivered. Journey stages: 1 Placed (pending, on-hold), 2 Confirmed (processing),
 * 3 Packed, 4 Shipped, 5 Delivered (delivered, completed). Cancelled, refunded and failed orders show a message.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

/** @return array<string, string> slug (without wc-) => label */
function priniti_core_journey_statuses(): array {
	return array(
		'packed'    => __( 'Packed', 'priniti-core' ),
		'shipped'   => __( 'Shipped', 'priniti-core' ),
		'delivered' => __( 'Delivered', 'priniti-core' ),
	);
}

add_action(
	'init',
	static function (): void {
		foreach ( priniti_core_journey_statuses() as $slug => $label ) {
			register_post_status(
				"wc-{$slug}",
				array(
					'label'                     => $label,
					'public'                    => false,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'priniti-core' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralSingular,WordPress.WP.I18n.NonSingularStringLiteralPlural
				)
			);
		}
	}
);

add_filter(
	'wc_order_statuses',
	static function ( array $statuses ): array {
		$out = array();
		foreach ( $statuses as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'wc-processing' === $key ) {
				foreach ( priniti_core_journey_statuses() as $slug => $l ) {
					$out[ "wc-{$slug}" ] = $l;
				}
			}
		}
		return $out;
	}
);

// Paid statuses: these count as paid for reports and allow downloads/reviews like "processing".
add_filter( 'woocommerce_order_is_paid_statuses', static fn ( array $s ): array => array_merge( $s, array_keys( priniti_core_journey_statuses() ) ) );
add_filter( 'woocommerce_reports_order_statuses', static fn ( $s ) => is_array( $s ) ? array_merge( $s, array_keys( priniti_core_journey_statuses() ) ) : $s );

/** Bulk actions on the Orders screen (both storage modes). */
foreach ( array( 'bulk_actions-edit-shop_order', 'bulk_actions-woocommerce_page_wc-orders' ) as $priniti_core_screen ) {
	add_filter(
		$priniti_core_screen,
		static function ( array $actions ): array {
			foreach ( priniti_core_journey_statuses() as $slug => $label ) {
				/* translators: %s: status */
				$actions[ "mark_{$slug}" ] = sprintf( __( 'Change status to %s', 'priniti-core' ), strtolower( $label ) );
			}
			return $actions;
		}
	);
}

/** Journey stage (1-5) for an order status, 0 when the order is closed. */
function priniti_core_order_stage( string $status ): int {
	$map = array(
		'pending'    => 1,
		'on-hold'    => 1,
		'processing' => 2,
		'packed'     => 3,
		'shipped'    => 4,
		'delivered'  => 5,
		'completed'  => 5,
	);
	return (int) apply_filters( 'priniti_core_order_stage', $map[ $status ] ?? 0, $status );
}

function priniti_core_handle_track(): array {
	$v      = priniti_core_posted( array( 'order_id' => 'text', 'contact' => 'text' ) );
	$values = $v;
	$errors = array_filter(
		array(
			'order_id' => preg_match( '/^#?[A-Za-z0-9-]+$/', trim( $v['order_id'] ) ) ? '' : 'Enter the order ID from your confirmation message.',
			'contact'  => priniti_core_validate_email_or_mobile( $v['contact'] ),
		)
	);
	if ( $errors ) {
		return compact( 'values', 'errors' );
	}
	if ( ! priniti_core_rate_limit( 'track', 10, 10 * MINUTE_IN_SECONDS ) ) {
		return array( 'values' => $values, 'message' => 'Too many lookups. Please wait a few minutes and try again.', 'tone' => 'error' );
	}

	$not_found = array( 'values' => $values, 'message' => 'We could not find an order with those details. Check the order ID and the email or mobile number used at checkout.', 'tone' => 'error' );
	if ( ! function_exists( 'wc_get_order' ) ) {
		return $not_found;
	}

	$number   = ltrim( trim( $v['order_id'] ), '#' );
	$order_id = (int) apply_filters( 'woocommerce_shortcode_order_tracking_order_id', $number );
	$order    = $order_id ? wc_get_order( $order_id ) : false;
	if ( ! $order instanceof WC_Order ) {
		return $not_found;
	}

	$contact = trim( $v['contact'] );
	$matches = str_contains( $contact, '@' )
		? 0 === strcasecmp( $contact, (string) $order->get_billing_email() )
		: ( 10 === strlen( priniti_core_normalize_mobile( $contact ) ) && priniti_core_normalize_mobile( $contact ) === priniti_core_normalize_mobile( (string) $order->get_billing_phone() ) );
	if ( ! $matches ) {
		return $not_found;
	}

	$status = $order->get_status();
	$closed = array(
		'cancelled' => 'This order was cancelled.',
		'refunded'  => 'This order was refunded.',
		'failed'    => 'Payment for this order did not go through. Please contact Customer Care if you need help.',
	);

	return array(
		'values' => $values,
		'order'  => array(
			'number' => $order->get_order_number(),
			'date'   => wc_format_datetime( $order->get_date_created() ),
			'status' => wc_get_order_status_name( $status ),
			'total'  => wp_strip_all_tags( $order->get_formatted_order_total() ),
			'stage'  => priniti_core_order_stage( $status ),
			'closed' => $closed[ $status ] ?? '',
		),
	);
}
