<?php
/**
 * Contact enquiries: validated, stored as private "Enquiry" records (so nothing is lost if email fails) and
 * emailed to the team (Settings > Priniti > Enquiry email, default: the site admin email).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

const PRINITI_CORE_ENQUIRY_TYPES = array( 'General Enquiry', 'Product Enquiry', 'Distributor Enquiry', 'Export Enquiry' );

add_action(
	'init',
	static function (): void {
		register_post_type(
			'priniti_enquiry',
			array(
				'labels'          => array(
					'name'          => __( 'Enquiries', 'priniti-core' ),
					'singular_name' => __( 'Enquiry', 'priniti-core' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 58,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'show_in_rest'    => false,
			)
		);
	}
);

add_filter(
	'manage_priniti_enquiry_posts_columns',
	static fn ( array $cols ): array => array_merge( array_slice( $cols, 0, 2 ), array( 'priniti_type' => 'Type', 'priniti_from' => 'From' ), array_slice( $cols, 2 ) )
);
add_action(
	'manage_priniti_enquiry_posts_custom_column',
	static function ( string $col, int $id ): void {
		if ( 'priniti_type' === $col ) {
			echo esc_html( (string) get_post_meta( $id, '_priniti_type', true ) );
		}
		if ( 'priniti_from' === $col ) {
			echo esc_html( trim( get_post_meta( $id, '_priniti_email', true ) . ' ' . get_post_meta( $id, '_priniti_phone', true ) ) );
		}
	},
	10,
	2
);

function priniti_core_enquiry_recipient(): string {
	$email = (string) get_option( 'priniti_core_enquiry_email', '' );
	return is_email( $email ) ? $email : (string) get_option( 'admin_email' );
}

function priniti_core_handle_contact(): array {
	$v      = priniti_core_posted( array( 'name' => 'text', 'email' => 'email', 'phone' => 'text', 'type' => 'text', 'message' => 'textarea' ) );
	$values = $v;
	$errors = array_filter(
		array(
			'name'    => priniti_core_validate_name( $v['name'] ),
			'email'   => priniti_core_validate_email( $v['email'] ),
			'phone'   => '' !== trim( $v['phone'] ) ? priniti_core_validate_mobile( $v['phone'] ) : '',
			'type'    => in_array( $v['type'], PRINITI_CORE_ENQUIRY_TYPES, true ) ? '' : 'Enter an enquiry type.',
			'message' => mb_strlen( trim( $v['message'] ) ) >= 10 ? '' : 'Please write at least 10 characters.',
		)
	);
	if ( $errors ) {
		return compact( 'values', 'errors' );
	}
	if ( ! priniti_core_rate_limit( 'contact', 5, HOUR_IN_SECONDS ) ) {
		return array( 'values' => $values, 'message' => 'You have sent several enquiries already. Please wait a while, or call us using the numbers on this page.', 'tone' => 'error' );
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'priniti_enquiry',
			'post_status'  => 'private',
			'post_title'   => sprintf( '%s: %s', $v['type'], $v['name'] ),
			'post_content' => $v['message'],
			'meta_input'   => array(
				'_priniti_name'  => $v['name'],
				'_priniti_email' => $v['email'],
				'_priniti_phone' => $v['phone'],
				'_priniti_type'  => $v['type'],
			),
		),
		true
	);

	$body = implode(
		"\n",
		array(
			'New enquiry from the website contact form.',
			'',
			"Type: {$v['type']}",
			"Name: {$v['name']}",
			"Email: {$v['email']}",
			'Phone: ' . ( $v['phone'] ?: '-' ),
			'',
			$v['message'],
		)
	);
	$sent = wp_mail( priniti_core_enquiry_recipient(), sprintf( '[Priniti Foods] %s from %s', $v['type'], $v['name'] ), $body, array( 'Reply-To: ' . $v['name'] . ' <' . $v['email'] . '>' ) );
	if ( ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_priniti_emailed', $sent ? 'yes' : 'no' );
	}

	return array(
		'redirect' => home_url( '/contact/#enquiry' ),
		'message'  => sprintf( 'Thank you, %s. Your enquiry has been received and our team will get back to you. For anything urgent, please call us using the numbers on this page.', $v['name'] ),
		'tone'     => 'success',
	);
}
