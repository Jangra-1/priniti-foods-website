<?php
/**
 * Admin screens: a "Priniti details" tab on the product editor (the design's product fields), category flags on
 * the product category form, and Settings > Priniti (enquiry email).
 *
 * List fields are edited one item per line ("Label | value" for nutrition, "Question | Answer" for FAQs).
 *
 * @package PrinitiCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

/* ---- Product editor ---- */

add_filter(
	'woocommerce_product_data_tabs',
	static function ( array $tabs ): array {
		$tabs['priniti'] = array(
			'label'    => __( 'Priniti details', 'priniti-core' ),
			'target'   => 'priniti_product_data',
			'priority' => 65,
		);
		return $tabs;
	}
);

/** Array meta <-> textarea lines. */
function priniti_core_lines_from_meta( $value, string $type ): string {
	if ( ! is_array( $value ) ) {
		return '';
	}
	return implode(
		"\n",
		array_map(
			static function ( $row ) use ( $type ) {
				if ( 'nutrition' === $type ) {
					return ( $row['label'] ?? '' ) . ' | ' . ( $row['per100g'] ?? '' );
				}
				if ( 'faqs' === $type ) {
					return ( $row['q'] ?? '' ) . ' | ' . ( $row['a'] ?? '' );
				}
				return (string) $row;
			},
			$value
		)
	);
}

function priniti_core_meta_from_lines( string $text, string $type ): array {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( 'nutrition' === $type || 'faqs' === $type ) {
			[ $a, $b ] = array_map( 'trim', array_pad( explode( '|', $line, 2 ), 2, '' ) );
			if ( '' === $a || '' === $b ) {
				continue;
			}
			$out[] = 'nutrition' === $type ? array( 'label' => $a, 'per100g' => $b ) : array( 'q' => $a, 'a' => $b );
		} else {
			$out[] = $line;
		}
	}
	return $out;
}

add_action(
	'woocommerce_product_data_panels',
	static function (): void {
		global $post;
		$id = (int) $post->ID;
		echo '<div id="priniti_product_data" class="panel woocommerce_options_panel hidden">';
		echo '<p class="form-field" style="padding-top:8px"><em>' . esc_html__( 'Only fill in confirmed details. Empty fields show "Coming soon" on the product page; nothing is invented.', 'priniti-core' ) . '</em></p>';

		woocommerce_wp_text_input(
			array(
				'id'            => Priniti_Meta::PACK_SIZE,
				'label'         => __( 'Pack size', 'priniti-core' ),
				'description'   => __( 'For products sold in one size, e.g. "400 g". Products in several sizes use the "Pack size" attribute and variations instead.', 'priniti-core' ),
				'desc_tip'      => true,
				'value'         => (string) get_post_meta( $id, Priniti_Meta::PACK_SIZE, true ),
				'wrapper_class' => 'show_if_simple',
			)
		);
		woocommerce_wp_select(
			array(
				'id'          => Priniti_Meta::PACK_SOURCE,
				'label'       => __( 'Pack size verified from', 'priniti-core' ),
				'options'     => array(
					''               => __( 'Not specified', 'priniti-core' ),
					'pack-art'       => __( 'Printed on the pack', 'priniti-core' ),
					'website'        => __( 'Official website', 'priniti-core' ),
					'image-filename' => __( 'Image file name (shown as "to be confirmed")', 'priniti-core' ),
				),
				'value'       => (string) get_post_meta( $id, Priniti_Meta::PACK_SOURCE, true ),
			)
		);
		$areas = array(
			array( Priniti_Meta::HIGHLIGHTS, __( 'Highlights', 'priniti-core' ), __( 'One per line.', 'priniti-core' ), 'list' ),
			array( Priniti_Meta::INGREDIENTS, __( 'Ingredients', 'priniti-core' ), '', 'text' ),
			array( Priniti_Meta::NUTRITION, __( 'Nutrition (per 100 g)', 'priniti-core' ), __( 'One per line: Nutrient | value, e.g. "Energy | 520 kcal".', 'priniti-core' ), 'nutrition' ),
			array( Priniti_Meta::STORAGE, __( 'Storage', 'priniti-core' ), '', 'text' ),
			array( Priniti_Meta::SHIPPING_NOTE, __( 'Shipping and delivery note', 'priniti-core' ), '', 'text' ),
			array( Priniti_Meta::FAQS, __( 'FAQs', 'priniti-core' ), __( 'One per line: Question | Answer.', 'priniti-core' ), 'faqs' ),
			array( Priniti_Meta::INTERNAL_NOTES, __( 'Internal notes (never shown)', 'priniti-core' ), __( 'Data-quality notes for the team. One per line.', 'priniti-core' ), 'list' ),
		);
		foreach ( $areas as [ $key, $label, $desc, $type ] ) {
			$raw = get_post_meta( $id, $key, true );
			woocommerce_wp_textarea_input(
				array(
					'id'          => $key,
					'label'       => $label,
					'description' => $desc,
					'desc_tip'    => (bool) $desc,
					'value'       => 'text' === $type ? (string) $raw : priniti_core_lines_from_meta( $raw, $type ),
					'rows'        => 4,
				)
			);
		}
		echo '</div>';
	}
);

add_action(
	'woocommerce_admin_process_product_object',
	static function ( WC_Product $product ): void {
		// Nonce and capability are checked by WooCommerce before this hook runs.
		$get = static fn ( string $k ): string => isset( $_POST[ $k ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$set = static function ( string $key, $value ) use ( $product ): void {
			if ( '' === $value || array() === $value ) {
				$product->delete_meta_data( $key );
			} else {
				$product->update_meta_data( $key, $value );
			}
		};
		$set( Priniti_Meta::PACK_SIZE, sanitize_text_field( $get( Priniti_Meta::PACK_SIZE ) ) );
		$source = $get( Priniti_Meta::PACK_SOURCE );
		$set( Priniti_Meta::PACK_SOURCE, in_array( $source, array( 'pack-art', 'website', 'image-filename' ), true ) ? $source : '' );
		$set( Priniti_Meta::INGREDIENTS, $get( Priniti_Meta::INGREDIENTS ) );
		$set( Priniti_Meta::STORAGE, $get( Priniti_Meta::STORAGE ) );
		$set( Priniti_Meta::SHIPPING_NOTE, $get( Priniti_Meta::SHIPPING_NOTE ) );
		$set( Priniti_Meta::HIGHLIGHTS, priniti_core_meta_from_lines( $get( Priniti_Meta::HIGHLIGHTS ), 'list' ) );
		$set( Priniti_Meta::NUTRITION, priniti_core_meta_from_lines( $get( Priniti_Meta::NUTRITION ), 'nutrition' ) );
		$set( Priniti_Meta::FAQS, priniti_core_meta_from_lines( $get( Priniti_Meta::FAQS ), 'faqs' ) );
		$set( Priniti_Meta::INTERNAL_NOTES, priniti_core_meta_from_lines( $get( Priniti_Meta::INTERNAL_NOTES ), 'list' ) );
	}
);

/* ---- Product categories ---- */

$priniti_core_cat_fields = static function ( $term = null ): void {
	$hide  = $term ? get_term_meta( $term->term_id, Priniti_Meta::HIDE_WHEN_EMPTY, true ) : '';
	$cover = $term ? get_term_meta( $term->term_id, Priniti_Meta::COVER_PRODUCT, true ) : '';
	wp_nonce_field( 'priniti_core_cat', '_priniti_core_cat' );
	?>
	<tr class="form-field"><th scope="row"><?php esc_html_e( 'Hide while empty', 'priniti-core' ); ?></th>
		<td><label><input type="checkbox" name="priniti_hide_when_empty" value="1" <?php checked( '1', $hide ); ?>> <?php esc_html_e( 'Keep out of navigation and lists until the category has products (its page shows "coming soon").', 'priniti-core' ); ?></label></td></tr>
	<tr class="form-field"><th scope="row"><label for="priniti_cover_product"><?php esc_html_e( 'Cover product (slug)', 'priniti-core' ); ?></label></th>
		<td><input id="priniti_cover_product" type="text" name="priniti_cover_product" value="<?php echo esc_attr( (string) $cover ); ?>"><p class="description"><?php esc_html_e( 'Used for the category picture when no thumbnail is set: the first image of this product.', 'priniti-core' ); ?></p></td></tr>
	<?php
};
add_action( 'product_cat_edit_form_fields', $priniti_core_cat_fields );
add_action(
	'product_cat_add_form_fields',
	static function () use ( $priniti_core_cat_fields ): void {
		echo '<table class="form-table">';
		$priniti_core_cat_fields();
		echo '</table>';
	}
);
$priniti_core_cat_save = static function ( int $term_id ): void {
	if ( ! isset( $_POST['_priniti_core_cat'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_priniti_core_cat'] ) ), 'priniti_core_cat' ) || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	update_term_meta( $term_id, Priniti_Meta::HIDE_WHEN_EMPTY, empty( $_POST['priniti_hide_when_empty'] ) ? '' : '1' );
	update_term_meta( $term_id, Priniti_Meta::COVER_PRODUCT, sanitize_title( wp_unslash( $_POST['priniti_cover_product'] ?? '' ) ) );
};
add_action( 'created_product_cat', $priniti_core_cat_save );
add_action( 'edited_product_cat', $priniti_core_cat_save );

/* ---- Settings > Priniti ---- */

add_action(
	'admin_init',
	static function (): void {
		register_setting( 'priniti_core', 'priniti_core_enquiry_email', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_email', 'default' => '' ) );
		add_settings_section( 'priniti_core_main', '', '__return_false', 'priniti-core' );
		add_settings_field(
			'priniti_core_enquiry_email',
			__( 'Enquiry email', 'priniti-core' ),
			static function (): void {
				printf( '<input type="email" class="regular-text" name="priniti_core_enquiry_email" value="%s" placeholder="%s"><p class="description">%s</p>', esc_attr( (string) get_option( 'priniti_core_enquiry_email', '' ) ), esc_attr( (string) get_option( 'admin_email' ) ), esc_html__( 'Contact-form enquiries are emailed here (and kept under Enquiries). Empty = the site admin email.', 'priniti-core' ) );
			},
			'priniti-core',
			'priniti_core_main'
		);
	}
);
add_action(
	'admin_menu',
	static function (): void {
		add_options_page(
			__( 'Priniti', 'priniti-core' ),
			__( 'Priniti', 'priniti-core' ),
			'manage_options',
			'priniti-core',
			static function (): void {
				echo '<div class="wrap"><h1>' . esc_html__( 'Priniti settings', 'priniti-core' ) . '</h1><form method="post" action="options.php">';
				settings_fields( 'priniti_core' );
				do_settings_sections( 'priniti-core' );
				submit_button();
				echo '</form></div>';
			}
		);
	}
);
