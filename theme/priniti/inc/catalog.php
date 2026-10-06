<?php
/**
 * Catalog data access for templates (port of reference/nextjs/lib/api/categories.ts).
 *
 * WooCommerce is the source of truth. Every function degrades to an empty result when WooCommerce
 * is not active, so the shell still renders (no invented fallback content).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published product categories, in the store's category order (navigation, footer, search, filters).
 *
 * Hidden: WooCommerce's default "Uncategorized" category, and categories flagged
 * `priniti_hide_when_empty` (e.g. Combos) while they have no products (data/categories.ts `published`).
 *
 * @return array<int, array{id:int, slug:string, name:string, description:string, href:string, count:int}>
 */
function priniti_get_categories(): array {
	static $categories = null;
	if ( null !== $categories ) {
		return $categories;
	}
	$categories = array();
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $categories;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $categories;
	}

	foreach ( $terms as $term ) {
		if ( 0 !== (int) $term->parent ) {
			continue; // Top-level categories only (the design has no subcategory navigation).
		}
		if ( '1' === get_term_meta( $term->term_id, 'priniti_hide_when_empty', true ) && 0 === (int) $term->count ) {
			continue;
		}
		$link         = get_term_link( $term );
		$categories[] = array(
			'id'          => (int) $term->term_id,
			'slug'        => $term->slug,
			'name'        => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'description' => wp_strip_all_tags( (string) $term->description ),
			'href'        => is_wp_error( $link ) ? '' : $link,
			'count'       => (int) $term->count,
			'order'       => (int) get_term_meta( $term->term_id, 'order', true ),
		);
	}

	// WooCommerce stores the drag-and-drop category order in the `order` term meta.
	usort( $categories, static fn ( array $a, array $b ): int => array( $a['order'], $a['name'] ) <=> array( $b['order'], $b['name'] ) );

	return $categories;
}

/**
 * Lightweight published-product entries for header-search suggestions (types/catalog.ts SearchIndexItem).
 * Cached in a transient; flushed whenever a product or category changes.
 *
 * @return array<int, array{name:string, href:string, categoryName:string, image:string}>
 */
function priniti_get_search_index(): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	$cached = get_transient( 'priniti_search_index' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$index    = array();
	$products = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'search',
			'limit'      => 500,
			'orderby'    => 'title',
			'order'      => 'ASC',
		)
	);
	foreach ( $products as $product ) {
		$terms    = get_the_terms( $product->get_id(), 'product_cat' );
		$category = ( is_array( $terms ) && $terms ) ? html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' ) : '';
		$image_id = (int) $product->get_image_id();
		$index[]  = array(
			'name'         => html_entity_decode( $product->get_name(), ENT_QUOTES, 'UTF-8' ),
			'href'         => (string) $product->get_permalink(),
			'categoryName' => $category,
			'image'        => $image_id ? (string) wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
		);
	}

	set_transient( 'priniti_search_index', $index, DAY_IN_SECONDS );
	return $index;
}

/**
 * Flush the search index cache whenever catalog data changes.
 */
function priniti_flush_search_index(): void {
	delete_transient( 'priniti_search_index' );
}
add_action( 'save_post_product', 'priniti_flush_search_index' );
add_action( 'deleted_post', 'priniti_flush_search_index' );
add_action( 'woocommerce_update_product', 'priniti_flush_search_index' );
add_action( 'created_product_cat', 'priniti_flush_search_index' );
add_action( 'edited_product_cat', 'priniti_flush_search_index' );
add_action( 'delete_product_cat', 'priniti_flush_search_index' );
