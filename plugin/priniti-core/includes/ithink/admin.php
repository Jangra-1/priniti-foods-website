<?php
/**
 * iThink admin screens.
 *
 * Always (also while the integration is off, so data can be prepared):
 *   - "Verified packed weight (g)" on products and variations, and a "Shipping data" column on the product list
 *     that flags products without it;
 *   - an "iThink Logistics" section under Settings > Priniti showing which wp-config.php constants are set
 *     (never their values).
 * Only when PRINITI_ITHINK_ENABLED is true:
 *   - the "iThink shipment" box on the order screen (shop managers only) with Create shipment, Refresh tracking,
 *     Get label and Cancel shipment, each nonce-protected; creating needs a confirmation tick and a dialog;
 *   - "Test connection" on the settings section (read-only calls).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

const PRINITI_ITHINK_CAP = 'manage_woocommerce';

/* ---- Verified packed weight on products and variations ---- */

add_action(
	'woocommerce_product_options_shipping',
	static function (): void {
		global $product_object;
		woocommerce_wp_text_input(
			array(
				'id'                => Priniti_Meta::SHIP_WEIGHT_G,
				'label'             => __( 'Verified packed weight (g)', 'priniti-core' ),
				'description'       => __( 'Weighed weight of one sellable unit ready to ship, in grams (product plus its own packing). Used for courier rates and shipments. Leave empty until weighed: the net weight on the pack is not a shipping weight.', 'priniti-core' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '1', 'step' => '1' ),
				'value'             => $product_object ? (string) $product_object->get_meta( Priniti_Meta::SHIP_WEIGHT_G, true ) : '',
				'wrapper_class'     => 'show_if_simple',
			)
		);
	}
);

add_action(
	'woocommerce_admin_process_product_object',
	static function ( WC_Product $product ): void {
		// Nonce and capability are checked by WooCommerce before this hook runs.
		$grams = isset( $_POST[ Priniti_Meta::SHIP_WEIGHT_G ] ) ? absint( wp_unslash( $_POST[ Priniti_Meta::SHIP_WEIGHT_G ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $grams > 0 ) {
			$product->update_meta_data( Priniti_Meta::SHIP_WEIGHT_G, $grams );
		} else {
			$product->delete_meta_data( Priniti_Meta::SHIP_WEIGHT_G );
		}
	}
);

add_action(
	'woocommerce_variation_options_dimensions',
	static function ( int $loop, array $variation_data, WP_Post $variation ): void {
		woocommerce_wp_text_input(
			array(
				'id'                => Priniti_Meta::SHIP_WEIGHT_G . "_{$loop}",
				'name'              => Priniti_Meta::SHIP_WEIGHT_G . "[{$loop}]",
				'label'             => __( 'Verified packed weight (g)', 'priniti-core' ),
				'description'       => __( 'Weighed weight of this pack ready to ship. Leave empty until weighed.', 'priniti-core' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '1', 'step' => '1' ),
				'value'             => (string) get_post_meta( $variation->ID, Priniti_Meta::SHIP_WEIGHT_G, true ),
				'wrapper_class'     => 'form-row form-row-full',
			)
		);
	},
	10,
	3
);

add_action(
	'woocommerce_admin_process_variation_object',
	static function ( WC_Product_Variation $variation, int $i ): void {
		// Nonce and capability are checked by WooCommerce before this hook runs.
		$grams = isset( $_POST[ Priniti_Meta::SHIP_WEIGHT_G ][ $i ] ) ? absint( wp_unslash( $_POST[ Priniti_Meta::SHIP_WEIGHT_G ][ $i ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $grams > 0 ) {
			$variation->update_meta_data( Priniti_Meta::SHIP_WEIGHT_G, $grams );
		} else {
			$variation->delete_meta_data( Priniti_Meta::SHIP_WEIGHT_G );
		}
	},
	10,
	2
);

/** "Shipping data" column: Verified, or what is missing. */
add_filter(
	'manage_edit-product_columns',
	static function ( array $columns ): array {
		$columns['priniti_ship'] = __( 'Shipping data', 'priniti-core' );
		return $columns;
	},
	20
);
add_action(
	'manage_product_posts_custom_column',
	static function ( string $column, int $post_id ): void {
		if ( 'priniti_ship' !== $column ) {
			return;
		}
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}
		$units   = $product->is_type( 'variable' ) ? array_filter( array_map( 'wc_get_product', $product->get_children() ) ) : array( $product );
		$missing = count( array_filter( $units, static fn ( $u ) => null === Priniti_IThink::unit_weight( $u ) ) );
		if ( ! $units || $missing ) {
			/* translators: 1: units missing a weight, 2: all units */
			$text = $units && count( $units ) > 1 ? sprintf( __( 'Missing weight (%1$d of %2$d packs)', 'priniti-core' ), $missing, count( $units ) ) : __( 'Missing weight', 'priniti-core' );
			echo '<span style="color:#b32d2e">' . esc_html( $text ) . '</span>';
		} else {
			echo '<span style="color:#008a20">' . esc_html__( 'Verified', 'priniti-core' ) . '</span>';
		}
	},
	10,
	2
);

/* ---- Notices (one per user, shown on the next screen) ---- */

function priniti_ithink_notice( string $type, string $message, string $link = '' ): void {
	set_transient( 'priniti_ithink_notice_' . get_current_user_id(), compact( 'type', 'message', 'link' ), 120 );
}

add_action(
	'admin_notices',
	static function (): void {
		$key    = 'priniti_ithink_notice_' . get_current_user_id();
		$notice = get_transient( $key );
		if ( ! is_array( $notice ) ) {
			return;
		}
		delete_transient( $key );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p><strong>iThink:</strong> %2$s%3$s</p></div>',
			esc_attr( in_array( $notice['type'], array( 'success', 'error', 'warning', 'info' ), true ) ? $notice['type'] : 'info' ),
			esc_html( $notice['message'] ),
			$notice['link'] ? ' <a href="' . esc_url( $notice['link'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Open', 'priniti-core' ) . '</a>' : ''
		);
	}
);

/* ---- Settings > Priniti: configuration overview and connection test ---- */

add_action(
	'priniti_core_settings_after',
	static function (): void {
		$config = Priniti_IThink::config();
		echo '<hr><h2>' . esc_html__( 'iThink Logistics', 'priniti-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Configured in wp-config.php only (see docs/ITHINK.md). Credential values are never shown here.', 'priniti-core' ) . '</p>';
		echo '<table class="widefat striped" style="max-width:720px"><tbody>';
		foreach ( $config->status() as $row ) {
			printf( '<tr><th scope="row"><code>%s</code></th><td>%s</td></tr>', esc_html( $row['name'] ), esc_html( $row['state'] ) );
		}
		echo '</tbody></table>';
		if ( $config->enabled() && current_user_can( PRINITI_ITHINK_CAP ) ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:12px">';
			echo '<input type="hidden" name="action" value="priniti_ithink_verify">';
			wp_nonce_field( 'priniti_ithink_verify' );
			/* translators: %s: staging or production */
			submit_button( sprintf( __( 'Test connection (%s, read-only)', 'priniti-core' ), $config->env() ), 'secondary', 'submit', false );
			echo '</form>';
		}
	}
);

add_action(
	'admin_post_priniti_ithink_verify',
	static function (): void {
		if ( ! current_user_can( PRINITI_ITHINK_CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'priniti-core' ), 403 );
		}
		check_admin_referer( 'priniti_ithink_verify' );
		try {
			$result = Priniti_IThink::client()->verify();
			$parts  = array( __( 'Credentials accepted.', 'priniti-core' ) );
			$parts[] = $result['warehouse']
				/* translators: 1: address id, 2: pincode */
				? sprintf( __( 'Pickup address %1$d found (pincode %2$s).', 'priniti-core' ), $result['warehouse']['id'], $result['warehouse']['pincode'] )
				: __( 'The pickup address ID was NOT found on this account.', 'priniti-core' );
			if ( Priniti_IThink::config()->store_id() ) {
				$parts[] = $result['store']
					/* translators: 1: store id, 2: platform */
					? sprintf( __( 'Store %1$d found (%2$s).', 'priniti-core' ), $result['store']['id'], $result['store']['platform'] )
					: __( 'The store ID was NOT found on this account.', 'priniti-core' );
			}
			priniti_ithink_notice( $result['warehouse'] ? 'success' : 'warning', implode( ' ', $parts ) );
		} catch ( Priniti_IThink_Error $e ) {
			priniti_ithink_notice( 'error', $e->getMessage() );
		}
		wp_safe_redirect( admin_url( 'options-general.php?page=priniti-core' ) );
		exit;
	}
);

if ( ! Priniti_IThink::enabled() ) {
	return;
}

/* ---- Order screen: the "iThink shipment" box ---- */

add_action(
	'add_meta_boxes',
	static function (): void {
		if ( ! current_user_can( PRINITI_ITHINK_CAP ) ) {
			return;
		}
		$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		add_meta_box( 'priniti-ithink', __( 'iThink shipment', 'priniti-core' ), 'priniti_ithink_order_box', $screen, 'side', 'default' );
	}
);

/** Statuses from which a shipment may be created. */
function priniti_ithink_shippable_statuses(): array {
	return array( 'processing', 'packed' );
}

function priniti_ithink_order_box( $post_or_order ): void {
	$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID ?? 0 );
	if ( ! $order instanceof WC_Order ) {
		return;
	}
	$config = Priniti_IThink::config();
	$store  = new Priniti_IThink_WC_Order_Store( $order );
	$env    = $config->env();
	$get    = static fn ( string $k ): string => $store->get( $k );

	printf(
		'<p><span style="display:inline-block;padding:2px 8px;border-radius:3px;font-weight:600;color:#fff;background:%s">%s</span></p>',
		esc_attr( $config->is_production() ? '#b32d2e' : '#2271b1' ),
		esc_html( strtoupper( $env ) )
	);

	if ( $problems = $config->problems() ) {
		echo '<p>' . esc_html__( 'iThink is not configured:', 'priniti-core' ) . '</p><ul style="list-style:disc;margin-left:16px">';
		foreach ( $problems as $p ) {
			echo '<li>' . esc_html( $p ) . '</li>';
		}
		echo '</ul>';
		return;
	}

	wp_nonce_field( 'priniti_ithink_order_' . $order->get_id(), '_priniti_ithink_nonce' );
	echo '<input type="hidden" name="priniti_ithink_do" value="">';
	$state = $get( Priniti_Meta::ITHINK_STATE );
	$awb   = $get( Priniti_Meta::ITHINK_AWB );

	if ( '' !== $awb ) {
		$rows = array(
			__( 'AWB', 'priniti-core' )        => $awb,
			__( 'Courier', 'priniti-core' )    => $get( Priniti_Meta::ITHINK_COURIER ),
			__( 'Sent to', 'priniti-core' )    => $get( Priniti_Meta::ITHINK_ENV ),
			__( 'State', 'priniti-core' )      => $state,
			__( 'Tracking', 'priniti-core' )   => trim( $get( Priniti_Meta::ITHINK_STATUS ) . ' ' . ( $get( Priniti_Meta::ITHINK_STATUS_CODE ) ? '(' . $get( Priniti_Meta::ITHINK_STATUS_CODE ) . ')' : '' ) ),
			__( 'Expected', 'priniti-core' )   => $get( Priniti_Meta::ITHINK_EXPECTED ),
			__( 'Checked at', 'priniti-core' ) => $get( Priniti_Meta::ITHINK_SYNCED_AT ),
		);
		echo '<table style="width:100%">';
		foreach ( array_filter( $rows ) as $label => $value ) {
			printf( '<tr><th style="text-align:left;padding-right:6px">%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( $value ) );
		}
		echo '</table><p>';
		foreach ( array( Priniti_Meta::ITHINK_TRACKING_URL => __( 'Courier tracking', 'priniti-core' ), Priniti_Meta::ITHINK_LABEL_URL => __( 'Label PDF', 'priniti-core' ) ) as $key => $label ) {
			if ( '' !== $get( $key ) ) {
				printf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a><br>', esc_url( $get( $key ) ), esc_html( $label ) );
			}
		}
		echo '</p><p>';
		priniti_ithink_button( 'track', __( 'Refresh tracking', 'priniti-core' ) );
		priniti_ithink_button( 'label', __( 'Get label', 'priniti-core' ) );
		if ( Priniti_IThink_Shipments::STATE_CREATED === $state ) {
			/* translators: %s: AWB */
			priniti_ithink_button( 'cancel', __( 'Cancel shipment', 'priniti-core' ), sprintf( __( 'Cancel iThink shipment AWB %s? The courier booking will be withdrawn.', 'priniti-core' ), $awb ) );
		}
		echo '</p>';
		return;
	}

	if ( Priniti_IThink_Shipments::STATE_PENDING === $state ) {
		echo '<p>' . esc_html__( 'A shipment request is in progress or was interrupted. Check the iThink panel for this order number before doing anything else.', 'priniti-core' ) . '</p>';
		return;
	}
	if ( '' !== $get( Priniti_Meta::ITHINK_ERROR ) ) {
		printf( '<p style="color:#b32d2e">%s</p>', esc_html( $get( Priniti_Meta::ITHINK_ERROR ) ) );
	}

	if ( ! in_array( $order->get_status(), priniti_ithink_shippable_statuses(), true ) ) {
		echo '<p>' . esc_html__( 'Shipments can be created for Processing or Packed orders.', 'priniti-core' ) . '</p>';
		return;
	}

	foreach ( $config->shipment_problems() as $p ) {
		printf( '<p style="color:#b32d2e">%s</p>', esc_html( $p ) );
	}
	$parcel = Priniti_IThink::parcel( Priniti_IThink::order_lines( $order ) );
	if ( $parcel['missing'] ) {
		echo '<p style="color:#b32d2e">' . esc_html__( 'No verified packed weight for:', 'priniti-core' ) . '</p><ul style="list-style:disc;margin-left:16px">';
		foreach ( $parcel['missing'] as $name ) {
			echo '<li>' . esc_html( $name ) . '</li>';
		}
		echo '</ul><p>' . esc_html__( 'Weigh the packed parcel and enter it below.', 'priniti-core' ) . '</p>';
	} else {
		echo '<p>' . esc_html__( 'Weight below is the sum of the verified pack weights. Change it to the weighed parcel if it differs.', 'priniti-core' ) . '</p>';
	}
	$box    = $parcel['box'] ?? array();
	$fields = array(
		'weight_g'  => array( __( 'Parcel weight (g)', 'priniti-core' ), $parcel['weight_g'], '1' ),
		'length_cm' => array( __( 'Length (cm)', 'priniti-core' ), $box['length_cm'] ?? '', '0.1' ),
		'width_cm'  => array( __( 'Width (cm)', 'priniti-core' ), $box['width_cm'] ?? '', '0.1' ),
		'height_cm' => array( __( 'Height (cm)', 'priniti-core' ), $box['height_cm'] ?? '', '0.1' ),
	);
	foreach ( $fields as $key => [ $label, $value, $step ] ) {
		printf(
			'<p><label for="priniti_ithink_%1$s">%2$s</label><br><input type="number" min="%3$s" step="%3$s" id="priniti_ithink_%1$s" name="priniti_ithink[%1$s]" value="%4$s" style="width:100%%"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $step ),
			esc_attr( (string) $value )
		);
	}
	if ( Priniti_IThink_Shipments::STATE_UNCERTAIN === $state ) {
		printf(
			'<p><label><input type="checkbox" name="priniti_ithink[checked_panel]" value="1"> %s</label></p>',
			/* translators: %s: order number */
			esc_html( sprintf( __( 'I searched the iThink panel for order %s and no shipment exists.', 'priniti-core' ), $order->get_order_number() ) )
		);
	}
	printf(
		'<p><label><input type="checkbox" name="priniti_ithink[confirm]" value="1" data-priniti-ithink-confirm> %s</label></p>',
		/* translators: 1: order number, 2: staging or production */
		esc_html( sprintf( __( 'Book a courier shipment for order %1$s with iThink (%2$s).', 'priniti-core' ), $order->get_order_number(), $env ) )
	);
	priniti_ithink_button(
		'create',
		__( 'Create shipment', 'priniti-core' ),
		/* translators: 1: order number, 2: staging or production */
		sprintf( __( 'Create an iThink shipment for order %1$s on %2$s? This books a courier pickup.', 'priniti-core' ), $order->get_order_number(), strtoupper( $env ) )
	);
}

/** A button that confirms, records the action and submits the order form through its own Update button. */
function priniti_ithink_button( string $action, string $label, string $confirm = '' ): void {
	printf(
		'<button type="button" class="button%1$s" style="margin:0 4px 4px 0" data-priniti-ithink="%2$s" data-confirm="%3$s">%4$s</button>',
		'create' === $action ? ' button-primary' : '',
		esc_attr( $action ),
		esc_attr( $confirm ),
		esc_html( $label )
	);
	static $script = false;
	if ( $script ) {
		return;
	}
	$script = true;
	?>
	<script>
	document.addEventListener( 'click', function ( e ) {
		var button = e.target.closest( '[data-priniti-ithink]' );
		if ( ! button ) { return; }
		var form = button.closest( 'form' );
		var action = button.getAttribute( 'data-priniti-ithink' );
		if ( 'create' === action ) {
			var tick = form.querySelector( '[data-priniti-ithink-confirm]' );
			if ( ! tick || ! tick.checked ) { window.alert( <?php echo wp_json_encode( __( 'Tick the confirmation box first.', 'priniti-core' ) ); ?> ); return; }
		}
		var question = button.getAttribute( 'data-confirm' );
		if ( question && ! window.confirm( question ) ) { return; }
		form.querySelector( 'input[name="priniti_ithink_do"]' ).value = action;
		var save = form.querySelector( 'button[name="save"], input[name="save"]' );
		button.disabled = true;
		if ( save ) { save.click(); } else { form.submit(); }
	} );
	</script>
	<?php
}

/** Runs the requested action after WooCommerce has saved the order (HPOS and classic screens both fire this). */
add_action(
	'woocommerce_process_shop_order_meta',
	static function ( $order_id ): void {
		$action = isset( $_POST['priniti_ithink_do'] ) ? sanitize_key( wp_unslash( $_POST['priniti_ithink_do'] ) ) : '';
		if ( '' === $action ) {
			return;
		}
		$order_id = (int) $order_id;
		$nonce    = isset( $_POST['_priniti_ithink_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_priniti_ithink_nonce'] ) ) : '';
		if ( ! current_user_can( PRINITI_ITHINK_CAP ) || ! wp_verify_nonce( $nonce, 'priniti_ithink_order_' . $order_id ) ) {
			priniti_ithink_notice( 'error', __( 'The request could not be verified. Reload the order and try again.', 'priniti-core' ) );
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$input   = isset( $_POST['priniti_ithink'] ) && is_array( $_POST['priniti_ithink'] ) ? wp_unslash( $_POST['priniti_ithink'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each value is cast below.
		$store   = new Priniti_IThink_WC_Order_Store( $order );
		$service = Priniti_IThink::shipments();

		try {
			switch ( $action ) {
				case 'create':
					if ( empty( $input['confirm'] ) ) {
						throw new Priniti_IThink_Error( 'validation', 'Tick the confirmation box to create a shipment.' );
					}
					if ( ! in_array( $order->get_status(), priniti_ithink_shippable_statuses(), true ) ) {
						throw new Priniti_IThink_Error( 'validation', 'Shipments can be created for Processing or Packed orders.' );
					}
					$package = array(
						'weight_g'  => min( 50000, absint( $input['weight_g'] ?? 0 ) ),
						'length_cm' => min( 1000.0, max( 0.0, (float) ( $input['length_cm'] ?? 0 ) ) ),
						'width_cm'  => min( 1000.0, max( 0.0, (float) ( $input['width_cm'] ?? 0 ) ) ),
						'height_cm' => min( 1000.0, max( 0.0, (float) ( $input['height_cm'] ?? 0 ) ) ),
					);
					$result = $service->create( $store, Priniti_IThink::order_data( $order ), $package, ! empty( $input['checked_panel'] ) );
					/* translators: 1: AWB, 2: courier */
					priniti_ithink_notice( 'success', sprintf( __( 'Shipment created: AWB %1$s %2$s.', 'priniti-core' ), $result['awb'], $result['courier'] ) );
					break;
				case 'track':
					$t = $service->refresh( $store, true );
					priniti_ithink_notice( $t['attention'] ? 'warning' : 'success', trim( $t['status'] . ' (' . $t['code'] . ')' . ( $t['attention'] ? ' Needs attention.' : '' ) ) );
					break;
				case 'label':
					priniti_ithink_notice( 'success', __( 'Label ready.', 'priniti-core' ), $service->label( $store ) );
					break;
				case 'cancel':
					$service->cancel( $store );
					priniti_ithink_notice( 'success', __( 'Shipment cancelled at iThink. The order status was not changed.', 'priniti-core' ) );
					break;
			}
		} catch ( Priniti_IThink_Error $e ) {
			priniti_ithink_notice( 'error', $e->getMessage() );
		}
	},
	60
);

/** Cancelling an order does not cancel its courier booking automatically: remind the team. */
add_action(
	'woocommerce_order_status_cancelled',
	static function ( $order_id, $order = null ): void {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
		if ( $order && Priniti_IThink_Shipments::STATE_CREATED === $order->get_meta( Priniti_Meta::ITHINK_STATE ) ) {
			$order->add_order_note( 'This order still has an active iThink shipment (AWB ' . $order->get_meta( Priniti_Meta::ITHINK_AWB ) . '). Use "Cancel shipment" in the iThink box if the parcel should not go.' );
		}
	},
	10,
	2
);
