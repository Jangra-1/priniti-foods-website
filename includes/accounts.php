<?php
/**
 * Accounts: sign in with email or mobile number, and sign up (creates a WooCommerce customer with name and mobile).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

/** Finds the user for an email address or a 10-digit Indian mobile number (billing phone). */
function priniti_core_find_user( string $identifier ): ?WP_User {
	$identifier = trim( $identifier );
	if ( str_contains( $identifier, '@' ) ) {
		return get_user_by( 'email', $identifier ) ?: null;
	}
	$mobile = priniti_core_normalize_mobile( $identifier );
	if ( 10 !== strlen( $mobile ) ) {
		return null;
	}
	$users = get_users(
		array(
			'number'     => 2,
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array( 'key' => 'billing_phone', 'value' => $mobile ),
				array( 'key' => 'billing_phone', 'value' => '+91' . $mobile ),
				array( 'key' => 'billing_phone', 'value' => '0' . $mobile ),
			),
		)
	);
	// Ambiguous (two accounts share the number): require the email instead.
	return 1 === count( $users ) ? $users[0] : null;
}

function priniti_core_handle_login(): array {
	$v      = priniti_core_posted( array( 'identifier' => 'text', 'password' => 'password', 'remember' => 'text', 'redirect_to' => 'text' ) );
	$values = array( 'identifier' => $v['identifier'] );
	$errors = array_filter(
		array(
			'identifier' => priniti_core_validate_email_or_mobile( $v['identifier'] ),
			'password'   => '' === $v['password'] ? 'Enter your password.' : '',
		)
	);
	if ( $errors ) {
		return compact( 'values', 'errors' );
	}
	if ( ! priniti_core_rate_limit( 'login', 10, 15 * MINUTE_IN_SECONDS ) ) {
		return array( 'values' => $values, 'message' => 'Too many sign-in attempts. Please wait a few minutes and try again.', 'tone' => 'error' );
	}

	$user   = priniti_core_find_user( $v['identifier'] );
	$signed = $user ? wp_signon(
		array(
			'user_login'    => $user->user_login,
			'user_password' => $v['password'],
			'remember'      => '1' === $v['remember'],
		),
		is_ssl()
	) : new WP_Error( 'invalid' );

	if ( is_wp_error( $signed ) ) {
		return array( 'values' => $values, 'message' => 'The email/mobile number or password is incorrect.', 'tone' => 'error' );
	}

	$fallback = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
	return array( 'redirect' => wp_validate_redirect( $v['redirect_to'], $fallback ) ?: $fallback );
}

/** Whether visitors may create accounts on /signup (filterable; on by default because the design offers it). */
function priniti_core_signup_enabled(): bool {
	return (bool) apply_filters( 'priniti_core_signup_enabled', true );
}

function priniti_core_handle_signup(): array {
	$v      = priniti_core_posted( array( 'name' => 'text', 'email' => 'email', 'mobile' => 'text', 'password' => 'password', 'confirm' => 'password', 'terms' => 'text' ) );
	$values = array( 'name' => $v['name'], 'email' => $v['email'], 'mobile' => $v['mobile'], 'terms' => $v['terms'] );

	if ( ! priniti_core_signup_enabled() ) {
		return array( 'values' => $values, 'message' => 'Account creation is not available at the moment.', 'tone' => 'error' );
	}

	$errors = array_filter(
		array(
			'name'     => priniti_core_validate_name( $v['name'] ),
			'email'    => priniti_core_validate_email( $v['email'] ),
			'mobile'   => priniti_core_validate_mobile( $v['mobile'] ),
			'password' => strlen( $v['password'] ) >= 8 ? '' : 'Use at least 8 characters.',
			'confirm'  => ( '' !== $v['confirm'] && $v['confirm'] === $v['password'] ) ? '' : 'Passwords do not match.',
			'terms'    => '1' === $v['terms'] ? '' : 'Please agree to the terms to continue.',
		)
	);
	if ( ! $errors && email_exists( $v['email'] ) ) {
		$errors['email'] = 'An account with this email already exists. Log in instead.';
	}
	$mobile = priniti_core_normalize_mobile( $v['mobile'] );
	if ( ! $errors && priniti_core_find_user( $mobile ) ) {
		$errors['mobile'] = 'An account with this mobile number already exists. Log in instead.';
	}
	if ( $errors ) {
		return compact( 'values', 'errors' );
	}
	if ( ! priniti_core_rate_limit( 'signup', 5, HOUR_IN_SECONDS ) ) {
		return array( 'values' => $values, 'message' => 'Too many accounts were created from this connection. Please try again later.', 'tone' => 'error' );
	}

	$parts = preg_split( '/\s+/', trim( $v['name'] ), 2 );
	$first = $parts[0];
	$last  = $parts[1] ?? '';

	$user_id = function_exists( 'wc_create_new_customer' )
		? wc_create_new_customer( $v['email'], '', $v['password'], array( 'first_name' => $first, 'last_name' => $last ) )
		: wp_insert_user( array( 'user_login' => sanitize_user( strstr( $v['email'], '@', true ) . wp_rand( 100, 999 ) ), 'user_email' => $v['email'], 'user_pass' => $v['password'], 'first_name' => $first, 'last_name' => $last, 'role' => 'customer' ) );

	if ( is_wp_error( $user_id ) ) {
		return array( 'values' => $values, 'message' => wp_strip_all_tags( $user_id->get_error_message() ), 'tone' => 'error' );
	}

	foreach ( array( 'billing_first_name' => $first, 'billing_last_name' => $last, 'billing_email' => $v['email'], 'billing_phone' => $mobile, 'billing_country' => 'IN' ) as $key => $value ) {
		update_user_meta( $user_id, $key, $value );
	}

	if ( function_exists( 'wc_set_customer_auth_cookie' ) ) {
		wc_set_customer_auth_cookie( $user_id );
	} else {
		wp_set_auth_cookie( $user_id, false, is_ssl() );
	}

	$account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
	return array( 'redirect' => $account );
}
