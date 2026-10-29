<?php
/**
 * Removes credentials and personal data before anything is logged or shown.
 *
 * - Credential fields are replaced entirely; the configured secret values are also scrubbed from free text
 *   (iThink error messages could echo them back).
 * - Names, addresses, phone numbers and email addresses are replaced. Pincodes keep their first three digits
 *   (enough to tell regions apart when debugging serviceability).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

final class Priniti_IThink_Redactor {
	private const SECRET_KEYS = array( 'access_token', 'secret_key', 'authorization', 'password' );

	private const PERSONAL_KEYS = array(
		'name', 'company_name', 'add', 'add2', 'add3', 'phone', 'alt_phone', 'email',
		'billing_name', 'billing_company_name', 'billing_add', 'billing_add2', 'billing_add3', 'billing_phone', 'billing_alt_phone', 'billing_email',
		'customer_name', 'customer_address', 'customer_address1', 'customer_address2', 'customer_mobile', 'customer_phone', 'customer_email',
		'customer_details', 'gst_number', 'what3words', 'mobile', 'address1', 'address2', 'store_email',
	);

	private const PINCODE_KEYS = array( 'pin', 'billing_pin', 'pincode', 'to_pincode', 'customer_pincode' );

	/** @param string[] $secrets */
	public function __construct( private array $secrets = array() ) {}

	public function data( $value, string $key = '' ) {
		$k = strtolower( $key );
		if ( in_array( $k, self::SECRET_KEYS, true ) ) {
			return '[redacted]';
		}
		if ( in_array( $k, self::PERSONAL_KEYS, true ) ) {
			return '[personal]';
		}
		if ( in_array( $k, self::PINCODE_KEYS, true ) && is_scalar( $value ) ) {
			return substr( (string) $value, 0, 3 ) . 'xxx';
		}
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $child_key => $child ) {
				$out[ $child_key ] = $this->data( $child, is_string( $child_key ) ? $child_key : $key );
			}
			return $out;
		}
		return is_string( $value ) ? $this->scrub_secrets( $value ) : $value;
	}

	/** Free text (error messages, remarks): scrub secret values, email addresses and 10-digit mobile numbers. */
	public function text( string $text ): string {
		$text = $this->scrub_secrets( $text );
		$text = (string) preg_replace( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $text );
		$text = (string) preg_replace( '/(?<!\d)(?:\+?91[\s-]?)?[6-9]\d{9}(?!\d)/', '[phone]', $text );
		return $text;
	}

	private function scrub_secrets( string $text ): string {
		foreach ( $this->secrets as $secret ) {
			if ( '' !== $secret ) {
				$text = str_replace( $secret, '[redacted]', $text );
			}
		}
		return $text;
	}
}
