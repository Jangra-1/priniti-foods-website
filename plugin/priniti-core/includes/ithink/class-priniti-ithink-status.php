<?php
/**
 * Maps iThink tracking statuses (doc-track-order/3) onto the order journey in includes/tracking.php.
 *
 * Only the forward journey Packed -> Shipped -> Delivered is ever set automatically, and only forwards. Cancelled,
 * RTO, reverse, lost and unknown statuses never change the order status: they are flagged for a person to handle.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Status {
	/** Not yet with the courier. */
	private const PACKED = array( 'manifested', 'not picked' );

	/** Still moving forward but needing a look. */
	private const PROBLEM = array( 'undelivered', 'out of delivery area', 'damaged', 'misrouted' );

	/**
	 * @return array{stage: string|null, attention: bool}
	 *   stage: 'packed' | 'shipped' | 'delivered' | null (leave the order status alone)
	 */
	public static function map( string $code, string $status ): array {
		$code   = strtoupper( trim( $code ) );
		$status = strtolower( trim( $status ) );
		$return = str_contains( $status, 'rto' ) || str_starts_with( $status, 'rev' );

		if ( 'DL' === $code ) {
			return array( 'stage' => $return ? null : 'delivered', 'attention' => $return );
		}
		if ( 'UD' === $code && ! $return ) {
			if ( in_array( $status, self::PACKED, true ) ) {
				return array( 'stage' => 'packed', 'attention' => false );
			}
			return array( 'stage' => 'shipped', 'attention' => in_array( $status, self::PROBLEM, true ) );
		}
		// CN (cancelled), RT (return to origin), reverse movements, Lost, Shortage and anything undocumented.
		return array( 'stage' => null, 'attention' => true );
	}

	/** Journey rank of a WooCommerce status (without "wc-"), 0 for statuses outside the forward journey. */
	public static function rank( string $wc_status ): int {
		return array( 'processing' => 2, 'packed' => 3, 'shipped' => 4, 'delivered' => 5 )[ $wc_status ] ?? 0;
	}

	/** True when moving from $wc_status to $stage is a step forward within Processing..Delivered. */
	public static function advances( string $wc_status, ?string $stage ): bool {
		$from = self::rank( $wc_status );
		return null !== $stage && $from >= 2 && self::rank( $stage ) > $from;
	}
}
