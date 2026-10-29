<?php
/**
 * Error raised by the iThink Logistics module. Messages are safe to show to shop managers: they never contain
 * credentials (the client scrubs them) or customer details.
 *
 * Kinds:
 *   config            the module is not configured (missing or invalid wp-config.php constants)
 *   validation        the request could not be built (missing package data, bad pincode, too many items)
 *   duplicate         a shipment exists or is being created for this order
 *   uncertain         an earlier create request got no answer; the panel must be checked before retrying
 *   auth              iThink rejected the credentials
 *   timeout, network  no answer from iThink (the request may or may not have been processed)
 *   server            iThink answered with an HTTP 5xx status (the request may or may not have been processed)
 *   http              iThink answered with another non-200 HTTP status
 *   invalid_response  the answer was not the documented JSON
 *   api               iThink answered with an error message
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Error extends RuntimeException {
	/** No usable answer was received, so iThink may still have processed the request. */
	public const UNANSWERED = array( 'timeout', 'network', 'server', 'invalid_response' );

	public function __construct( public readonly string $kind, string $message ) {
		parent::__construct( $message );
	}

	public function unanswered(): bool {
		return in_array( $this->kind, self::UNANSWERED, true );
	}
}
