<?php
/**
 * Catalog data layer. Ports reference/nextjs/lib/api/{products,categories}.ts and lib/catalog.ts.
 *
 * Every template works with plain arrays shaped like the reference types (Product, PackVariant, Category),
 * built from WooCommerce here. The local preview (tools/dev) can supply the same arrays through the
 * `priniti_pre_catalog` / `priniti_pre_categories` filters, so templates never touch WooCommerce directly.
 *
 * Rules carried over from the reference: never invent data; a variant is purchasable only with a real price
 * and not out of stock; filters and sorts without data are hidden.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

// 12 fills whole rows in every grid (2, 3 and 4 columns); the reference used 8 (data/site.ts catalog.pageSize).
const PRINITI_PAGE_SIZE = 12;

/* -------------------------------------------------------------------------
 * Products
 * ---------------------------------------------------------------------- */

/**
 * Turns a WooCommerce product into the reference Product shape.
 *
 * @return array<string, mixed>
 */
function priniti_product_view( WC_Product $product ): array {
	$id       = $product->get_id();
	$terms    = get_the_terms( $id, 'product_cat' );
	$category = ( is_array( $terms ) && $terms ) ? $terms[0] : null;
	$meta     = static fn ( string $key ) => function_exists( 'priniti_core_get_product_field' ) ? priniti_core_get_product_field( $id, $key ) : null;

	$images = array();
	foreach ( array_filter( array_merge( array( (int) $product->get_image_id() ), array_map( 'intval', $product->get_gallery_image_ids() ) ) ) as $image_id ) {
		$src = wp_get_attachment_image_url( $image_id, 'large' );
		if ( $src ) {
			$alt      = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
			$images[] = array(
				'src'    => $src,
				'thumb'  => (string) wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ),
				'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'large' ),
				'alt'    => '' !== $alt ? $alt : sprintf( '%s pack by Priniti Foods', $product->get_name() ),
			);
		}
	}

	$stock = static function ( WC_Product $p ): ?bool {
		if ( $p->managing_stock() ) {
			return $p->is_in_stock();
		}
		return 'outofstock' === $p->get_stock_status() ? false : null; // null = no stock data
	};
	$money = static fn ( $v ): ?float => ( '' === $v || null === $v ) ? null : (float) $v;
	// Sellable-unit data from the e-commerce item list (priniti-core pack-pricing.php): pieces per unit and MRP per piece.
	$pack_data = static function ( int $post_id ) use ( $money ): array {
		$pcs = get_post_meta( $post_id, '_priniti_pcs', true );
		return array(
			'pcs'     => '' === $pcs ? null : max( 1, (int) $pcs ),
			'unitMrp' => $money( get_post_meta( $post_id, '_priniti_mrp', true ) ),
			'weight'  => (string) get_post_meta( $post_id, '_priniti_weight', true ),
		);
	};

	$variants = array();
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );
			if ( ! $variation instanceof WC_Product_Variation || 'publish' !== $variation->get_status() ) {
				continue;
			}
			$attributes = $variation->get_attributes();
			$labels     = array();
			$store_attr = array();
			foreach ( $attributes as $taxonomy => $value ) {
				$term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $value, $taxonomy ) : null;
				$labels[] = $term ? $term->name : $value;
				$store_attr[] = array( 'attribute' => wc_attribute_label( $taxonomy ), 'value' => $term ? $term->name : $value );
			}
			$price      = $money( $variation->get_price() );
			$variants[] = array(
				'id'         => $child_id,
				'label'      => implode( ' · ', array_filter( $labels ) ),
				'source'     => (string) get_post_meta( $child_id, '_priniti_pack_source', true ),
				'price'      => $price,
				'mrp'        => $money( $variation->get_regular_price() ) ?? $price,
				'inStock'    => $stock( $variation ),
				'sku'        => $variation->get_sku(),
				'attributes' => $store_attr,
				'purchasable' => null !== $price && false !== $stock( $variation ) && $variation->is_purchasable(),
			) + $pack_data( $child_id );
		}
	} else {
		$pack  = (string) ( $meta( '_priniti_pack_size' ) ?? '' );
		$price = $money( $product->get_price() );
		if ( '' !== $pack || null !== $price ) {
			$variants[] = array(
				'id'          => $id,
				'label'       => $pack,
				'source'      => (string) get_post_meta( $id, '_priniti_pack_source', true ),
				'price'       => $price,
				'mrp'         => $money( $product->get_regular_price() ) ?? $price,
				'inStock'     => $stock( $product ),
				'sku'         => $product->get_sku(),
				'attributes'  => array(),
				'purchasable' => null !== $price && false !== $stock( $product ) && $product->is_purchasable(),
			) + $pack_data( $id );
		}
	}

	$tags   = wp_get_post_terms( $id, 'product_tag', array( 'fields' => 'slugs' ) );
	$badges = array_values( array_intersect( array( 'bestseller', 'new' ), is_array( $tags ) ? $tags : array() ) );
	$count  = (int) $product->get_review_count();

	return array(
		'id'           => $id,
		'slug'         => $product->get_slug(),
		'name'         => html_entity_decode( $product->get_name(), ENT_QUOTES, 'UTF-8' ),
		'href'         => (string) $product->get_permalink(),
		'categorySlug' => $category ? $category->slug : '',
		'categoryName' => $category ? html_entity_decode( $category->name, ENT_QUOTES, 'UTF-8' ) : '',
		'categoryHref' => $category ? (string) get_term_link( $category ) : '',
		'categoryOrder' => $category ? (int) get_term_meta( $category->term_id, 'order', true ) : 0,
		'menuOrder'    => (int) $product->get_menu_order(),
		'images'       => $images,
		'variants'     => $variants,
		'rating'       => $count > 0 ? (float) $product->get_average_rating() : null,
		'reviewCount'  => $count,
		'badges'       => $badges,
		'featured'     => $product->is_featured(),
		'date'         => $product->get_date_created() ? $product->get_date_created()->getTimestamp() : 0,
		'description'  => wp_strip_all_tags( (string) $product->get_description() ) ?: null,
		'shortDescription' => wp_strip_all_tags( (string) $product->get_short_description() ) ?: null,
		'highlights'   => $meta( '_priniti_highlights' ),
		'ingredients'  => $meta( '_priniti_ingredients' ),
		'nutrition'    => $meta( '_priniti_nutrition' ),
		'storage'      => $meta( '_priniti_storage' ),
		'shippingNote' => $meta( '_priniti_shipping_note' ),
		'faqs'         => $meta( '_priniti_faqs' ),
	);
}

/**
 * The storefront catalog: every published, visible product, in the default ("featured") order:
 * category order, then product menu order, then creation order (the reference's data order).
 * Cached; flushed whenever a product or category changes.
 *
 * @return array<int, array<string, mixed>>
 */
function priniti_catalog(): array {
	static $catalog = null;
	if ( null !== $catalog ) {
		return $catalog;
	}
	$pre = apply_filters( 'priniti_pre_catalog', null );
	if ( is_array( $pre ) ) {
		return $catalog = $pre;
	}
	if ( ! function_exists( 'wc_get_products' ) ) {
		return $catalog = array();
	}
	$cached = get_transient( 'priniti_catalog_v2' );
	if ( is_array( $cached ) ) {
		return $catalog = $cached;
	}

	$catalog = array();
	foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => -1, 'visibility' => 'catalog', 'orderby' => 'ID', 'order' => 'ASC' ) ) as $product ) {
		$catalog[] = priniti_product_view( $product );
	}
	usort(
		$catalog,
		static fn ( array $a, array $b ): int => array( $a['categoryOrder'], $a['menuOrder'], $a['id'] ) <=> array( $b['categoryOrder'], $b['menuOrder'], $b['id'] )
	);
	set_transient( 'priniti_catalog_v2', $catalog, HOUR_IN_SECONDS * 6 );
	return $catalog;
}

/**
 * One product by slug (published catalog only).
 *
 * @return array<string, mixed>|null
 */
function priniti_get_product( string $slug ): ?array {
	foreach ( priniti_catalog() as $p ) {
		if ( $p['slug'] === $slug ) {
			return $p;
		}
	}
	return null;
}

/**
 * Products by slug, in the order given; unknown slugs are skipped (getProductsBySlugs).
 *
 * @param string[] $slugs Slugs.
 * @return array<int, array<string, mixed>>
 */
function priniti_get_products_by_slugs( array $slugs ): array {
	$out = array();
	foreach ( $slugs as $slug ) {
		$p = priniti_get_product( (string) $slug );
		if ( $p ) {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * getProducts(): optional category / collection / limit.
 *
 * @return array<int, array<string, mixed>>
 */
function priniti_get_products( array $query = array() ): array {
	$list = priniti_catalog();
	if ( ! empty( $query['category'] ) ) {
		$list = array_filter( $list, static fn ( $p ) => $p['categorySlug'] === $query['category'] );
	}
	$collection = $query['collection'] ?? '';
	if ( 'best-sellers' === $collection ) {
		$list = array_filter( $list, static fn ( $p ) => in_array( 'bestseller', $p['badges'], true ) );
	} elseif ( 'new-arrivals' === $collection ) {
		$list = array_filter( $list, static fn ( $p ) => in_array( 'new', $p['badges'], true ) );
	} elseif ( 'featured' === $collection ) {
		$list = array_filter( $list, static fn ( $p ) => $p['featured'] );
	}
	$list = array_values( $list );
	return empty( $query['limit'] ) ? $list : array_slice( $list, 0, (int) $query['limit'] );
}

/**
 * Product counts per category slug (getCategoryProductCounts).
 *
 * @return array<string, int>
 */
function priniti_category_counts(): array {
	$counts = array();
	foreach ( priniti_catalog() as $p ) {
		$counts[ $p['categorySlug'] ] = ( $counts[ $p['categorySlug'] ] ?? 0 ) + 1;
	}
	return $counts;
}

/**
 * Same-category siblings first, then other products, never the product itself (getRelatedProducts).
 *
 * @return array<int, array<string, mixed>>
 */
function priniti_related_products( array $product, int $limit = 4 ): array {
	$siblings = array();
	$others   = array();
	foreach ( priniti_catalog() as $p ) {
		if ( $p['id'] === $product['id'] ) {
			continue;
		}
		if ( $p['categorySlug'] === $product['categorySlug'] ) {
			$siblings[] = $p;
		} else {
			$others[] = $p;
		}
	}
	return array_slice( array_merge( $siblings, $others ), 0, $limit );
}

/* -------------------------------------------------------------------------
 * Variant helpers (lib/product.ts)
 * ---------------------------------------------------------------------- */

function priniti_is_purchasable( ?array $variant ): bool {
	return $variant && ! empty( $variant['purchasable'] );
}

/** First purchasable variant, if any. */
function priniti_purchasable_variant( array $product ): ?array {
	foreach ( $product['variants'] as $v ) {
		if ( priniti_is_purchasable( $v ) ) {
			return $v;
		}
	}
	return null;
}

/** Variant to show on cards: the first purchasable one, otherwise the first verified pack size. */
function priniti_default_variant( array $product ): ?array {
	return priniti_purchasable_variant( $product ) ?? ( $product['variants'][0] ?? null );
}

function priniti_discount( ?array $variant ): int {
	if ( ! $variant || null === $variant['price'] || null === $variant['mrp'] || $variant['mrp'] <= 0 || $variant['price'] >= $variant['mrp'] ) {
		return 0;
	}
	return (int) round( ( $variant['mrp'] - $variant['price'] ) / $variant['mrp'] * 100 );
}

/** Discount on 2 and 3 single packs; mirrors priniti-core's PRINITI_CORE_MULTIPACK_DISCOUNT. */
const PRINITI_MULTIPACK_DISCOUNT = 0.12;

/** True when a variant is a single pack sold with the 1 / 2 / 3 pack selector. */
function priniti_is_multipack( ?array $variant ): bool {
	return $variant && 1 === ( $variant['pcs'] ?? null ) && null !== ( $variant['unitMrp'] ?? null );
}

/** True when a variant is a predefined "Pack of X" (Pcs > 1). */
function priniti_is_pack_of( ?array $variant ): bool {
	return $variant && ( $variant['pcs'] ?? 0 ) > 1;
}

/**
 * The 1 / 2 / 3 pack options of a single pack: total price, MRP equivalent and discount.
 *
 * @return array<int, array{packs:int, total:float, mrp:float, off:int}>
 */
function priniti_multipack_options( array $variant ): array {
	$mrp = (float) $variant['unitMrp'];
	$out = array();
	foreach ( array( 1, 2, 3 ) as $n ) {
		$total = 1 === $n ? $mrp : round( $mrp * $n * ( 1 - PRINITI_MULTIPACK_DISCOUNT ), 2 );
		$out[] = array(
			'packs' => $n,
			'total' => $total,
			'mrp'   => $mrp * $n,
			'off'   => 1 === $n ? 0 : (int) round( PRINITI_MULTIPACK_DISCOUNT * 100 ),
		);
	}
	return $out;
}

/** Verified pack-size labels (no placeholders). */
function priniti_pack_labels( array $product ): array {
	return array_values( array_filter( array_map( static fn ( $v ) => (string) $v['label'], $product['variants'] ) ) );
}

const PRINITI_BADGE_LABELS = array(
	'bestseller' => 'Best seller',
	'new'        => 'New',
	'combo'      => 'Combo',
);

/* -------------------------------------------------------------------------
 * Search (lib/search.ts)
 * ---------------------------------------------------------------------- */

/** Lower-case, strip punctuation: "Cream 'n' Onion" and "cream n onion" match. */
function priniti_normalize( string $s ): string {
	$s = strtolower( html_entity_decode( $s, ENT_QUOTES, 'UTF-8' ) );
	$s = (string) preg_replace( '/[^a-z0-9\s]/', ' ', $s );
	return trim( (string) preg_replace( '/\s+/', ' ', $s ) );
}

/** Every query word must appear somewhere in the haystack. */
function priniti_matches_query( string $haystack, string $query ): bool {
	$hay    = priniti_normalize( $haystack );
	$tokens = array_filter( explode( ' ', priniti_normalize( $query ) ) );
	if ( ! $tokens ) {
		return false;
	}
	foreach ( $tokens as $t ) {
		if ( ! str_contains( $hay, $t ) ) {
			return false;
		}
	}
	return true;
}

/* -------------------------------------------------------------------------
 * Filters, sorting, pagination (lib/catalog.ts + getProductList)
 * ---------------------------------------------------------------------- */

function priniti_sort_options(): array {
	return array(
		'featured'   => 'Default order',
		'name-asc'   => 'Name: A to Z',
		'newest'     => 'Newest first',
		'price-asc'  => 'Price: low to high',
		'price-desc' => 'Price: high to low',
		'rating'     => 'Top rated',
	);
}

function priniti_format_inr( float $amount ): string {
	// en-IN grouping (12,34,567); paise only when present (₹30, ₹96.80), like assets/src/js/lib/format.ts.
	$cents = (int) round( abs( $amount ) * 100 );
	$n     = (string) intdiv( $cents, 100 );
	$paise = $cents % 100;
	$last3 = substr( $n, -3 );
	$rest  = substr( $n, 0, -3 );
	if ( '' !== $rest ) {
		$rest = (string) preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest ) . ',';
	}
	return ( $amount < 0 && $cents ? '-' : '' ) . '₹' . $rest . $last3 . ( $paise ? sprintf( '.%02d', $paise ) : '' );
}

function priniti_price_ranges(): array {
	return array(
		'0-50'    => 'Under ' . priniti_format_inr( 50 ),
		'50-100'  => priniti_format_inr( 50 ) . ' to ' . priniti_format_inr( 100 ),
		'100-250' => priniti_format_inr( 100 ) . ' to ' . priniti_format_inr( 250 ),
		'250-'    => 'Over ' . priniti_format_inr( 250 ),
	);
}

const PRINITI_RATING_OPTIONS = array( 4, 3 );

/**
 * Filters from the query string (parseFilters). The URL is the single source of truth.
 *
 * @return array{q:string, categories:string[], price:?string, rating:?int, inStock:bool, sort:string, collection:?string, page:int}
 */
function priniti_parse_filters( array $src ): array {
	$first = static fn ( $v ) => is_array( $v ) ? ( $v[0] ?? '' ) : (string) ( $v ?? '' );
	$get   = static fn ( string $k ) => isset( $src[ $k ] ) ? sanitize_text_field( wp_unslash( $first( $src[ $k ] ) ) ) : '';

	$sort       = $get( 'sort' );
	$collection = $get( 'collection' );
	$price      = $get( 'price' );
	$rating     = (int) $get( 'rating' );
	$page       = (int) floor( (float) $get( 'page' ) );

	return array(
		'q'          => mb_substr( trim( $get( 'q' ) ), 0, 80 ),
		'categories' => array_values( array_filter( array_map( 'sanitize_title', explode( ',', $get( 'category' ) ) ) ) ),
		'price'      => isset( priniti_price_ranges()[ $price ] ) ? $price : null,
		'rating'     => in_array( $rating, PRINITI_RATING_OPTIONS, true ) ? $rating : null,
		'inStock'    => '1' === $get( 'stock' ),
		'sort'       => isset( priniti_sort_options()[ $sort ] ) ? $sort : 'featured',
		'collection' => in_array( $collection, array( 'best-sellers', 'new-arrivals' ), true ) ? $collection : null,
		'page'       => $page >= 1 ? $page : 1,
	);
}

/** "" or "?a=b&c=d"; defaults omitted so URLs stay clean (toQueryString). */
function priniti_query_string( array $f ): string {
	$p = array();
	if ( '' !== $f['q'] ) {
		$p['q'] = $f['q'];
	}
	if ( $f['collection'] ) {
		$p['collection'] = $f['collection'];
	}
	if ( $f['categories'] ) {
		$p['category'] = implode( ',', $f['categories'] );
	}
	if ( $f['price'] ) {
		$p['price'] = $f['price'];
	}
	if ( $f['rating'] ) {
		$p['rating'] = (string) $f['rating'];
	}
	if ( $f['inStock'] ) {
		$p['stock'] = '1';
	}
	if ( 'featured' !== $f['sort'] ) {
		$p['sort'] = $f['sort'];
	}
	if ( $f['page'] > 1 ) {
		$p['page'] = (string) $f['page'];
	}
	return $p ? '?' . http_build_query( $p, '', '&', PHP_QUERY_RFC3986 ) : '';
}

function priniti_count_active_filters( array $f, bool $ignore_categories = false ): int {
	return ( $ignore_categories ? 0 : count( $f['categories'] ) ) + ( $f['price'] ? 1 : 0 ) + ( $f['rating'] ? 1 : 0 ) + ( $f['inStock'] ? 1 : 0 );
}

function priniti_shop_heading( array $f ): string {
	if ( '' !== $f['q'] ) {
		return sprintf( 'Results for “%s”', $f['q'] );
	}
	if ( 'best-sellers' === $f['collection'] ) {
		return 'Best sellers';
	}
	if ( 'new-arrivals' === $f['collection'] ) {
		return 'New arrivals';
	}
	return 'All snacks';
}

/**
 * Which filters/sorts the data can support (getCatalogCapabilities). The UI hides the rest instead of faking them.
 *
 * @return array{prices:bool, ratings:bool, stock:bool, newFlags:bool}
 */
function priniti_catalog_capabilities(): array {
	$caps = array( 'prices' => false, 'ratings' => false, 'stock' => false, 'newFlags' => false );
	foreach ( priniti_catalog() as $p ) {
		foreach ( $p['variants'] as $v ) {
			$caps['prices'] = $caps['prices'] || null !== $v['price'];
			$caps['stock']  = $caps['stock'] || null !== $v['inStock'];
		}
		$caps['ratings']  = $caps['ratings'] || $p['reviewCount'] > 0;
		$caps['newFlags'] = $caps['newFlags'] || in_array( 'new', $p['badges'], true );
	}
	return $caps;
}

/** Sort options the current data can honour. */
function priniti_available_sort_options( array $caps ): array {
	return array_filter(
		priniti_sort_options(),
		static function ( string $key ) use ( $caps ): bool {
			if ( 'price-asc' === $key || 'price-desc' === $key ) {
				return $caps['prices'];
			}
			if ( 'rating' === $key ) {
				return $caps['ratings'];
			}
			if ( 'newest' === $key ) {
				return $caps['newFlags'];
			}
			return true;
		},
		ARRAY_FILTER_USE_KEY
	);
}

function priniti_has_filter_groups( array $caps, bool $show_categories ): bool {
	return $show_categories || $caps['prices'] || $caps['ratings'] || $caps['stock'];
}

/**
 * Filter, sort and paginate (getProductList).
 *
 * @return array{items:array, total:int, page:int, pageCount:int}
 */
function priniti_product_list( array $f, string $locked_category = '' ): array {
	$list     = priniti_catalog();
	$price_of = static fn ( array $p ): ?float => priniti_default_variant( $p )['price'] ?? null;

	if ( $locked_category ) {
		$list = array_filter( $list, static fn ( $p ) => $p['categorySlug'] === $locked_category );
	} elseif ( $f['categories'] ) {
		$list = array_filter( $list, static fn ( $p ) => in_array( $p['categorySlug'], $f['categories'], true ) );
	}
	if ( 'best-sellers' === $f['collection'] ) {
		$list = array_filter( $list, static fn ( $p ) => in_array( 'bestseller', $p['badges'], true ) );
	}
	if ( 'new-arrivals' === $f['collection'] ) {
		$list = array_filter( $list, static fn ( $p ) => in_array( 'new', $p['badges'], true ) );
	}
	if ( '' !== $f['q'] ) {
		$list = array_filter( $list, static fn ( $p ) => priniti_matches_query( $p['name'] . ' ' . $p['categoryName'], $f['q'] ) );
	}
	if ( $f['price'] ) {
		[ $min, $max ] = array_pad( explode( '-', $f['price'] ), 2, '' );
		$min           = (float) $min;
		$max           = '' === $max ? INF : (float) $max;
		$list          = array_filter(
			$list,
			static function ( $p ) use ( $price_of, $min, $max ) {
				$price = $price_of( $p );
				return null !== $price && $price >= $min && $price < $max;
			}
		);
	}
	if ( $f['rating'] ) {
		$list = array_filter( $list, static fn ( $p ) => ( $p['rating'] ?? 0 ) >= $f['rating'] );
	}
	if ( $f['inStock'] ) {
		$list = array_filter( $list, static fn ( $p ) => (bool) array_filter( $p['variants'], static fn ( $v ) => true === $v['inStock'] ) );
	}
	$list = array_values( $list );

	switch ( $f['sort'] ) {
		case 'name-asc':
			usort( $list, static fn ( $a, $b ) => strcasecmp( $a['name'], $b['name'] ) );
			break;
		case 'price-asc':
			usort( $list, static fn ( $a, $b ) => ( $price_of( $a ) ?? INF ) <=> ( $price_of( $b ) ?? INF ) );
			break;
		case 'price-desc':
			usort( $list, static fn ( $a, $b ) => ( $price_of( $b ) ?? -INF ) <=> ( $price_of( $a ) ?? -INF ) );
			break;
		case 'rating':
			usort( $list, static fn ( $a, $b ) => ( $b['rating'] ?? 0 ) <=> ( $a['rating'] ?? 0 ) );
			break;
		case 'newest':
			usort( $list, static fn ( $a, $b ) => (int) in_array( 'new', $b['badges'], true ) <=> (int) in_array( 'new', $a['badges'], true ) );
			break;
	}

	$total      = count( $list );
	$page_count = max( 1, (int) ceil( $total / PRINITI_PAGE_SIZE ) );
	$page       = min( $f['page'], $page_count );
	return array(
		'items'     => array_slice( $list, ( $page - 1 ) * PRINITI_PAGE_SIZE, PRINITI_PAGE_SIZE ),
		'total'     => $total,
		'page'      => $page,
		'pageCount' => $page_count,
	);
}

/* -------------------------------------------------------------------------
 * Categories
 * ---------------------------------------------------------------------- */

/**
 * Published product categories in the store's order (getCategories), with cover image and href.
 *
 * Hidden: the default "Uncategorized" category, and categories flagged `priniti_hide_when_empty` (Combos)
 * while they have no products (data/categories.ts `published`).
 *
 * @return array<int, array{id:int, slug:string, name:string, description:string, href:string, image:?array, published:bool}>
 */
function priniti_get_categories( bool $include_unpublished = false ): array {
	static $all = null;
	if ( null === $all ) {
		$pre = apply_filters( 'priniti_pre_categories', null );
		$all = array_map( 'priniti_category_with_image', is_array( $pre ) ? $pre : priniti_load_categories() );
	}
	return $include_unpublished ? $all : array_values( array_filter( $all, static fn ( $c ) => $c['published'] ) );
}

/**
 * Representative products per category (product slugs, best first): the category's cover image in menus and cards,
 * and the first picks for its hero. Every slug must belong to that category; unknown slugs are skipped.
 */
function priniti_category_showcase( string $category_slug ): array {
	$map = array(
		'indian-traditional-namkeen' => array( 'bhujia', 'aloo-bhujia', 'bombay-mix', 'cornflakes-mixture', 'navratan-mixture', 'kaju-mixture' ),
		'potato-chips'               => array( 'chips-classic-salted', 'chips-cream-n-onion', 'chips-masala-punch', 'potato-chips-spicy-masti', 'chips-tomato-punch' ),
		'charchare-sticks'           => array( 'charchare-mast-masala', 'charchare-tangy-tomato' ),
		'popcorn'                    => array( 'popcorn-butter-salted' ),
		'puffs-fryums'               => array( 'puff-hot-spicy', 'chiji-noodles', 'puffcorn', 'roll-n-roll', 'veg-biryani', 'tomato-katori', 'manchurian-fried-rice', 'chilli-storm' ),
		'ringo-star-rings'           => array( 'ringo-star-tangy-tomato' ),
		'rusk'                       => array( 'rusk' ),
		'sweets'                     => array( 'soan-papdi', 'gulab-jamun', 'rasgulla' ),
		'cookies'                    => array( 'jeera-cookies', 'ajwain-cookies', 'badam-cookies', 'kaju-cookies' ),
		'donut-cakes'                => array( 'choco-vanilla-donut-cake', 'strawberry-vanilla-donut-cake' ),
	);
	return (array) apply_filters( 'priniti_category_showcase', $map[ $category_slug ] ?? array(), $category_slug );
}

/** Showcase products of a category that exist, belong to it and have an image, topped up with its other imaged products. */
function priniti_category_showcase_products( string $category_slug, int $limit = 4 ): array {
	$picked = array();
	foreach ( priniti_category_showcase( $category_slug ) as $slug ) {
		$p = priniti_get_product( $slug );
		if ( $p && $p['categorySlug'] === $category_slug && ! empty( $p['images'] ) ) {
			$picked[ $p['id'] ] = $p;
		}
	}
	foreach ( priniti_catalog() as $p ) {
		if ( count( $picked ) >= $limit ) {
			break;
		}
		if ( $p['categorySlug'] === $category_slug && ! empty( $p['images'] ) ) {
			$picked[ $p['id'] ] ??= $p;
		}
	}
	return array_slice( array_values( $picked ), 0, $limit );
}

/**
 * Category-level images bundled with the theme (assets/images/categories), used when the WooCommerce category has no
 * thumbnail. Official Priniti artwork only, resized: donut-cakes is the official Choco Vanilla Donut Cake pack
 * (www.prinitifoods.com/images/choco-vanilla-donut.png). Filterable.
 */
function priniti_category_images(): array {
	return (array) apply_filters(
		'priniti_category_images',
		array(
			'donut-cakes' => array(
				'src'   => PRINITI_URI . '/assets/images/categories/donut-cakes.webp',
				'thumb' => PRINITI_URI . '/assets/images/categories/donut-cakes-thumb.webp',
				'alt'   => 'Priniti Choco Vanilla Donut Cake pack',
			),
		)
	);
}

/**
 * A category's image: its WooCommerce thumbnail; else the theme's category image (priniti_category_images); else its
 * first showcase product. Menus and cards never show "Image pending".
 */
function priniti_category_with_image( array $category ): array {
	if ( empty( $category['image'] ) ) {
		$own = priniti_category_images()[ (string) $category['slug'] ] ?? null;
		if ( $own ) {
			$category['image'] = $own;
			return $category;
		}
		$p = priniti_category_showcase_products( (string) $category['slug'], 1 )[0] ?? null;
		if ( $p ) {
			$category['image'] = array(
				'src'   => $p['images'][0]['src'],
				'thumb' => $p['images'][0]['thumb'] ?? $p['images'][0]['src'],
				'alt'   => $category['name'],
			);
		}
	}
	return $category;
}

function priniti_load_categories(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$counts = priniti_category_counts();
	$out    = array();
	foreach ( $terms as $term ) {
		$count    = $counts[ $term->slug ] ?? 0;
		$image    = null;
		$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumb_id && wp_get_attachment_image_url( $thumb_id, 'medium_large' ) ) {
			$alt   = (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
			$image = array( 'src' => (string) wp_get_attachment_image_url( $thumb_id, 'medium_large' ), 'alt' => '' !== $alt ? $alt : $term->name );
		} else {
			$cover = get_term_meta( $term->term_id, 'priniti_cover_product', true );
			$p     = $cover ? priniti_get_product( (string) $cover ) : null;
			$image = $p['images'][0] ?? null;
		}
		$link  = get_term_link( $term );
		$out[] = array(
			'id'          => (int) $term->term_id,
			'slug'        => $term->slug,
			'name'        => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'description' => wp_strip_all_tags( (string) $term->description ),
			'href'        => is_wp_error( $link ) ? '' : $link,
			'image'       => $image,
			'order'       => (int) get_term_meta( $term->term_id, 'order', true ),
			'published'   => ! ( '1' === get_term_meta( $term->term_id, 'priniti_hide_when_empty', true ) && 0 === $count ),
		);
	}
	usort( $out, static fn ( $a, $b ) => array( $a['order'], $a['name'] ) <=> array( $b['order'], $b['name'] ) );
	return $out;
}

function priniti_get_category( string $slug ): ?array {
	foreach ( priniti_get_categories( true ) as $c ) {
		if ( $c['slug'] === $slug ) {
			return $c;
		}
	}
	return null;
}

/** Rotating flat tint per category (lib/category-style.ts). */
function priniti_tint_for_index( int $i ): string {
	$tints = array( 'bg-navy-tint', 'bg-brand-tint', 'bg-leaf-tint', 'bg-lime-tint' );
	return $tints[ $i % count( $tints ) ];
}

/* -------------------------------------------------------------------------
 * Search index for the header dialog
 * ---------------------------------------------------------------------- */

/**
 * @return array<int, array{name:string, href:string, categoryName:string, image:string}>
 */
function priniti_get_search_index(): array {
	return array_map(
		static fn ( array $p ): array => array(
			'name'         => $p['name'],
			'href'         => $p['href'],
			'categoryName' => $p['categoryName'],
			'image'        => $p['images'][0]['thumb'] ?? ( $p['images'][0]['src'] ?? '' ),
		),
		priniti_catalog()
	);
}

/* -------------------------------------------------------------------------
 * Cache invalidation
 * ---------------------------------------------------------------------- */

function priniti_flush_catalog_cache(): void {
	delete_transient( 'priniti_catalog_v2' );
}
foreach ( array( 'save_post_product', 'save_post_product_variation', 'deleted_post', 'woocommerce_update_product', 'woocommerce_update_product_variation', 'woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status', 'created_product_cat', 'edited_product_cat', 'delete_product_cat', 'comment_post', 'wp_set_comment_status', 'edited_term', 'woocommerce_after_product_ordering' ) as $priniti_hook ) {
	add_action( $priniti_hook, 'priniti_flush_catalog_cache' );
}
