<?php
/**
 * Form framework for the storefront's own forms (login, signup, contact, newsletter, order tracking).
 *
 * - Every form carries an action name, a nonce and a honeypot (priniti_core_form_fields()).
 * - Submissions are handled on template_redirect. Errors re-render the same page with the visitor's values and
 *   field messages; successes redirect (post/redirect/get) and show a one-time message.
 * - Per-IP rate limits protect login, signup, tracking and the public forms.
 * - Field rules and messages match the reference's validation (lib/validation.ts) so client and server agree.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registered handlers: form => callable( array $values ): array{errors?:array, message?:string, tone?:string, redirect?:string, order?:array}.
 *
 * @return array<string, callable>
 */
function priniti_core_form_handlers(): array {
	return apply_filters(
		'priniti_core_form_handlers',
		array(
			'login'      => 'priniti_core_handle_login',
			'signup'     => 'priniti_core_handle_signup',
			'contact'    => 'priniti_core_handle_contact',
			'newsletter' => 'priniti_core_handle_newsletter',
			'track'      => 'priniti_core_handle_track',
		)
	);
}

/** Hidden fields for a form: action, nonce, honeypot. */
function priniti_core_form_fields( string $form ): void {
	printf( '<input type="hidden" name="priniti_form" value="%s">', esc_attr( $form ) );
	wp_nonce_field( "priniti_form_{$form}", '_priniti_nonce', false );
	// Honeypot: invisible to people, tempting to bots. Any value = silently ignored submission.
	echo '<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
	if ( 'login' === $form && ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		printf( '<input type="hidden" name="redirect_to" value="%s">', esc_attr( wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ), '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}

/**
 * Result for a form on the current request (after a failed submit), or a one-time success message after redirect.
 *
 * @return array{values?:array, errors?:array, message?:string, tone?:string, order?:array}|null
 */
function priniti_core_form_result( string $form ): ?array {
	global $priniti_core_form_results;
	if ( isset( $priniti_core_form_results[ $form ] ) ) {
		return $priniti_core_form_results[ $form ];
	}
	$flash = priniti_core_read_flash();
	return ( $flash && $flash['form'] === $form ) ? $flash['result'] : null;
}

/** Client IP used for rate limiting only (never stored with submissions). */
function priniti_core_client_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return (string) filter_var( $ip, FILTER_VALIDATE_IP ) ?: 'unknown';
}

/**
 * Sliding-window rate limit. Returns false when the limit is reached.
 */
function priniti_core_rate_limit( string $bucket, int $max, int $window ): bool {
	$key   = 'priniti_rl_' . md5( $bucket . '|' . priniti_core_client_ip() );
	$hits  = array_filter( (array) get_transient( $key ), static fn ( $t ) => $t > time() - $window );
	if ( count( $hits ) >= $max ) {
		return false;
	}
	$hits[] = time();
	set_transient( $key, array_values( $hits ), $window );
	return true;
}

/* ---- One-time messages after redirect (no cookies: a short-lived token in the URL) ---- */

function priniti_core_flash_redirect( string $form, array $result, string $url ): void {
	$token = wp_generate_password( 20, false );
	set_transient( 'priniti_flash_' . $token, array( 'form' => $form, 'result' => $result ), 10 * MINUTE_IN_SECONDS );
	[ $base, $hash ] = array_pad( explode( '#', $url, 2 ), 2, '' );
	wp_safe_redirect( add_query_arg( 'priniti_msg', $token, $base ) . ( $hash ? "#{$hash}" : '' ), 303 );
	exit;
}

function priniti_core_read_flash(): ?array {
	static $flash = false;
	if ( false !== $flash ) {
		return $flash;
	}
	$token = isset( $_GET['priniti_msg'] ) ? sanitize_key( wp_unslash( $_GET['priniti_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$flash = $token ? ( get_transient( 'priniti_flash_' . $token ) ?: null ) : null;
	if ( $token ) {
		delete_transient( 'priniti_flash_' . $token );
	}
	return $flash;
}

/* ---- Validation (same rules and messages as the reference) ---- */

function priniti_core_validate_name( string $v ): string {
	return mb_strlen( trim( $v ) ) >= 2 ? '' : 'Enter your full name.';
}
function priniti_core_validate_email( string $v ): string {
	return is_email( trim( $v ) ) ? '' : 'Enter a valid email address.';
}
function priniti_core_validate_mobile( string $v ): string {
	return preg_match( '/^[6-9]\d{9}$/', preg_replace( '/\s+/', '', $v ) ) ? '' : 'Enter a valid 10-digit mobile number.';
}
function priniti_core_validate_email_or_mobile( string $v ): string {
	$t = trim( $v );
	if ( '' === $t ) {
		return 'Enter your email or mobile number.';
	}
	return str_contains( $t, '@' ) ? priniti_core_validate_email( $t ) : priniti_core_validate_mobile( $t );
}
/** Indian mobile number in its 10-digit form (drops +91 / 0 prefixes and spaces). */
function priniti_core_normalize_mobile( string $v ): string {
	$digits = preg_replace( '/\D/', '', $v );
	return strlen( $digits ) > 10 ? substr( $digits, -10 ) : $digits;
}

/** Reads and sanitizes posted fields. */
function priniti_core_posted( array $keys ): array {
	$out = array();
	foreach ( $keys as $key => $type ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified by the dispatcher; sanitized below.
		$raw = is_string( $raw ) ? $raw : '';
		switch ( $type ) {
			case 'password':
				$out[ $key ] = $raw; // Never sanitized or echoed back; only passed to WordPress auth APIs.
				break;
			case 'textarea':
				$out[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'email':
				$out[ $key ] = sanitize_email( $raw ) ?: sanitize_text_field( $raw );
				break;
			default:
				$out[ $key ] = sanitize_text_field( $raw );
		}
	}
	return $out;
}

/* ---- Dispatcher ---- */

add_action(
	'template_redirect',
	static function (): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['priniti_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		$form     = sanitize_key( wp_unslash( $_POST['priniti_form'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$handlers = priniti_core_form_handlers();
		if ( ! isset( $handlers[ $form ] ) ) {
			return;
		}
		global $priniti_core_form_results;

		$nonce = isset( $_POST['_priniti_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_priniti_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, "priniti_form_{$form}" ) ) {
			$priniti_core_form_results[ $form ] = array( 'message' => 'Your session expired. Please submit the form again.', 'tone' => 'error' );
			return;
		}
		if ( ! empty( $_POST['website'] ) ) {
			// Honeypot filled: pretend success, do nothing.
			$priniti_core_form_results[ $form ] = array( 'message' => 'Thank you.', 'tone' => 'success' );
			return;
		}

		$result = call_user_func( $handlers[ $form ] );
		if ( ! empty( $result['redirect'] ) ) {
			if ( ! empty( $result['message'] ) ) {
				priniti_core_flash_redirect( $form, array_diff_key( $result, array( 'redirect' => 1 ) ), $result['redirect'] );
			}
			wp_safe_redirect( $result['redirect'], 303 );
			exit;
		}
		$priniti_core_form_results[ $form ] = $result;
	},
	5
);
