<?php
/**
 * "iThink courier rate" shipping method. Registered only while the integration is enabled, and it does nothing
 * until a shop manager adds it to a shipping zone (WooCommerce > Settings > Shipping), so loading it changes no
 * checkout behaviour by itself.
 *
 * The rate is the cheapest prepaid courier rate iThink quotes for the destination pincode and the cart's verified
 * packed weight. It is only offered when every cart line has a verified weight. When the weight is missing or
 * iThink cannot be reached, the optional fallback cost is offered instead (or nothing, if no fallback is set). When
 * iThink answers but quotes no prepaid courier, the pincode is treated as not serviceable and no rate is offered.
 * Answers are cached for 30 minutes per pincode and weight. Whether iThink's rate includes GST or COD charges is
 * not documented: confirm before going live.
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'woocommerce_shipping_methods',
	static function ( array $methods ): array {
		$methods['priniti_ithink'] = 'Priniti_IThink_Shipping_Method';
		return $methods;
	}
);

add_action(
	'woocommerce_shipping_init',
	static function (): void {
		if ( class_exists( 'Priniti_IThink_Shipping_Method' ) ) {
			return;
		}

		final class Priniti_IThink_Shipping_Method extends WC_Shipping_Method {
			public function __construct( $instance_id = 0 ) {
				$this->id                 = 'priniti_ithink';
				$this->instance_id        = absint( $instance_id );
				$this->method_title       = __( 'iThink courier rate', 'priniti-core' );
				$this->method_description = __( 'Live courier rate from iThink Logistics for the cart\'s verified packed weight. Products without a verified weight get the fallback cost (or no rate).', 'priniti-core' );
				$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
				$this->instance_form_fields = array(
					'title'         => array(
						'title'   => __( 'Title', 'priniti-core' ),
						'type'    => 'text',
						'default' => __( 'Standard delivery', 'priniti-core' ),
					),
					'fallback_cost' => array(
						'title'       => __( 'Fallback cost (₹)', 'priniti-core' ),
						'type'        => 'text',
						'description' => __( 'Charged when no live rate is available. Empty = offer no rate in that case.', 'priniti-core' ),
						'default'     => '',
					),
					'free_above'    => array(
						'title'       => __( 'Free delivery from (₹)', 'priniti-core' ),
						'type'        => 'text',
						'description' => __( 'Cart subtotal from which delivery is free. Empty = never free.', 'priniti-core' ),
						'default'     => '',
					),
				);
				$this->init_settings();
				$this->title = (string) $this->get_option( 'title', __( 'Standard delivery', 'priniti-core' ) );
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			public function calculate_shipping( $package = array() ): void {
				$pin = (string) ( $package['destination']['postcode'] ?? '' );
				if ( ! preg_match( '/^[1-9]\d{5}$/', $pin ) ) {
					return;
				}
				$subtotal = 0.0;
				$lines    = array();
				foreach ( (array) ( $package['contents'] ?? array() ) as $item ) {
					$product  = $item['data'] ?? null;
					$subtotal += (float) ( $item['line_total'] ?? 0 ) + (float) ( $item['line_tax'] ?? 0 );
					$lines[]  = array(
						'product' => $product instanceof WC_Product ? $product : null,
						'qty'     => (int) ( $item['quantity'] ?? 1 ),
						'name'    => $product instanceof WC_Product ? $product->get_name() : '',
					);
				}
				$free = $this->money_option( 'free_above' );
				if ( null !== $free && $subtotal >= $free ) {
					$this->add_rate( array( 'id' => $this->get_rate_id(), 'label' => $this->title, 'cost' => 0 ) );
					return;
				}
				$cost = $this->live_cost( $pin, Priniti_IThink::parcel( $lines ), $subtotal );
				if ( false === $cost ) {
					return; // iThink quoted no prepaid courier for this pincode: not serviceable.
				}
				$cost = $cost ?? $this->money_option( 'fallback_cost' );
				if ( null !== $cost ) {
					$this->add_rate( array( 'id' => $this->get_rate_id(), 'label' => $this->title, 'cost' => $cost ) );
				}
			}

			/** Cheapest prepaid rate; false when iThink quotes none (not serviceable); null when unknown. */
			private function live_cost( string $pin, array $parcel, float $mrp ): float|false|null {
				if ( null === $parcel['weight_g'] ) {
					return null;
				}
				$box   = $parcel['box'] ?? array();
				$key   = 'priniti_ithink_rate_' . md5( Priniti_IThink::config()->env() . "|{$pin}|{$parcel['weight_g']}|" . implode( 'x', (array) $box ) );
				$cache = get_transient( $key );
				if ( is_array( $cache ) ) {
					return $cache['cost'];
				}
				$cost = false;
				try {
					$rates = Priniti_IThink::client()->get_rates( array( 'to_pincode' => $pin, 'weight_g' => $parcel['weight_g'], 'payment' => 'prepaid', 'product_mrp' => $mrp ) + (array) $box );
					foreach ( $rates as $r ) {
						if ( $r['prepaid'] ) {
							$cost = round( $r['rate'], 2 );
							break;
						}
					}
				} catch ( Priniti_IThink_Error $e ) {
					// Logged (redacted) by the client. Not cached, so the next calculation tries again.
					return null;
				}
				set_transient( $key, array( 'cost' => $cost ), 30 * MINUTE_IN_SECONDS );
				return $cost;
			}

			private function money_option( string $key ): ?float {
				$v = trim( (string) $this->get_option( $key, '' ) );
				return is_numeric( $v ) && (float) $v >= 0 ? (float) $v : null;
			}
		}
	}
);
