<?php
/**
 * Newsletter sign-ups ("Stay in the Snack Loop"). Stored as private subscriber records; the
 * `priniti_core_newsletter_subscribed` action lets an email-marketing integration (e.g. Hostinger Reach) pick them up.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		register_post_type(
			'priniti_subscriber',
			array(
				'labels'          => array(
					'name'          => __( 'Subscribers', 'priniti-core' ),
					'singular_name' => __( 'Subscriber', 'priniti-core' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'edit.php?post_type=priniti_enquiry',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'show_in_rest'    => false,
			)
		);
	}
);

function priniti_core_handle_newsletter(): array {
	$v     = priniti_core_posted( array( 'email' => 'email' ) );
	$error = priniti_core_validate_email( $v['email'] );
	if ( $error ) {
		return array( 'values' => $v, 'errors' => array( 'email' => $error ) );
	}
	if ( ! priniti_core_rate_limit( 'newsletter', 5, HOUR_IN_SECONDS ) ) {
		return array( 'values' => $v, 'message' => 'Please try again later.', 'tone' => 'error' );
	}

	$email    = strtolower( $v['email'] );
	$existing = get_posts( array( 'post_type' => 'priniti_subscriber', 'post_status' => 'private', 'title' => $email, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( ! $existing ) {
		$id = wp_insert_post( array( 'post_type' => 'priniti_subscriber', 'post_status' => 'private', 'post_title' => $email ), true );
		if ( ! is_wp_error( $id ) ) {
			do_action( 'priniti_core_newsletter_subscribed', $email, $id );
		}
	}
	// Same answer for new and existing addresses (no way to probe who is subscribed).
	return array(
		'redirect' => home_url( '/#newsletter' ),
		'message'  => 'Thanks! You are on the list for new launches and offers.',
		'tone'     => 'success',
	);
}
