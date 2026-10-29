<?php
/**
 * Adds the courier, AWB and tracking status to the /track-order result (includes/tracking.php) for orders with an
 * iThink shipment. Loaded only while the integration is enabled; when it is off the page behaves exactly as before.
 *
 * The lookup is already gated by order number + billing email/mobile and rate-limited per IP. Tracking older than
 * 30 minutes is refreshed from iThink (status text only: the order status itself is only moved from wp-admin).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

const PRINITI_ITHINK_TRACK_MAX_AGE = 30 * MINUTE_IN_SECONDS;

add_filter(
	'priniti_core_track_order',
	static function ( array $data, WC_Order $order ): array {
		$store = new Priniti_IThink_WC_Order_Store( $order );
		$awb   = $store->get( Priniti_Meta::ITHINK_AWB );
		if ( '' === $awb || Priniti_IThink_Shipments::STATE_CREATED !== $store->get( Priniti_Meta::ITHINK_STATE ) || '' !== $data['closed'] ) {
			return $data;
		}

		$synced = strtotime( $store->get( Priniti_Meta::ITHINK_SYNCED_AT ) ) ?: 0;
		if ( $synced < time() - PRINITI_ITHINK_TRACK_MAX_AGE && ! Priniti_IThink::config()->problems() ) {
			try {
				Priniti_IThink::shipments()->refresh( $store, false );
			} catch ( Priniti_IThink_Error $e ) {
				// The stored status is shown instead. Messages are already redacted.
				Priniti_IThink::log( 'warning', "track-order refresh failed ({$e->kind}): {$e->getMessage()}" );
			}
		}

		$data['shipment'] = array(
			'courier'  => $store->get( Priniti_Meta::ITHINK_COURIER ),
			'awb'      => $awb,
			'status'   => $store->get( Priniti_Meta::ITHINK_STATUS ),
			'expected' => $store->get( Priniti_Meta::ITHINK_EXPECTED ),
			'url'      => Priniti_IThink_Client::https_url( $store->get( Priniti_Meta::ITHINK_TRACKING_URL ) ),
		);
		return $data;
	},
	10,
	2
);
