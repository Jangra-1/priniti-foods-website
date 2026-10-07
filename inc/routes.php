<?php
/**
 * Routes: keeps the design's URLs (reference/nextjs/app/*) and picks the template for each.
 *
 * - Content routes (/about, /contact, /login, /signup, /track-order, /search and the five policy pages) are
 *   theme routes, so no WordPress pages have to be created for them.
 * - Product categories live at /category/<slug> (the blog's category base moves to /blog-category/).
 * - /shop, /product/<slug>, /cart, /checkout and /my-account are WooCommerce's own pages, rendered with
 *   the design's templates.
 *
 * Rewrite rules are flushed automatically when PRINITI_REWRITE_VERSION changes (deploys) and on activation.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

const PRINITI_REWRITE_VERSION = '3';

/**
 * Theme routes: slug => template file in templates/.
 *
 * @return array<string, string>
 */
function priniti_routes(): array {
	return array(
		'about'           => 'about',
		'contact'         => 'contact',
		'login'           => 'login',
		'signup'          => 'signup',
		'track-order'     => 'track-order',
		'search'          => 'search',
		'privacy-policy'       => 'policy',
		'terms-and-conditions' => 'policy',
		'shipping-policy'      => 'policy',
		'return-refund-policy' => 'policy',
		'cookie-policy'        => 'policy',
	);
}

/**
 * Former policy URLs, permanently redirected to their canonical routes.
 *
 * @return array<string, string>
 */
function priniti_route_aliases(): array {
	return array(
		'terms'         => '/terms-and-conditions',
		'return-policy' => '/return-refund-policy',
	);
}

/** The current theme route slug, or '' when the request is not a theme route. */
function priniti_route(): string {
	$route = (string) get_query_var( 'priniti_route' );
	return isset( priniti_routes()[ $route ] ) ? $route : '';
}

add_filter(
	'query_vars',
	static function ( array $vars ): array {
		$vars[] = 'priniti_route';
		return $vars;
	}
);

add_action(
	'init',
	static function (): void {
		foreach ( array_merge( array_keys( priniti_routes() ), array_keys( priniti_route_aliases() ) ) as $slug ) {
			add_rewrite_rule( '^' . preg_quote( $slug, '/' ) . '/?$', 'index.php?priniti_route=' . $slug, 'top' );
		}
	}
);

/** Product categories at /category/<slug> (design route); blog categories move out of the way. */
add_filter(
	'woocommerce_taxonomy_args_product_cat',
	static function ( array $args ): array {
		$args['rewrite'] = array(
			'slug'         => 'category',
			'with_front'   => false,
			'hierarchical' => true,
		);
		return $args;
	}
);
add_filter(
	'register_taxonomy_args',
	static function ( array $args, string $taxonomy ): array {
		if ( 'category' === $taxonomy ) {
			$args['rewrite'] = array_merge( is_array( $args['rewrite'] ?? null ) ? $args['rewrite'] : array(), array( 'slug' => 'blog-category' ) );
		}
		return $args;
	},
	10,
	2
);

/** Flush rewrite rules once per route-table version (runs after every deploy that changes routes). */
add_action(
	'init',
	static function (): void {
		if ( get_option( 'priniti_rewrite_version' ) !== PRINITI_REWRITE_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'priniti_rewrite_version', PRINITI_REWRITE_VERSION, true );
		}
	},
	99
);
add_action( 'after_switch_theme', static fn () => delete_option( 'priniti_rewrite_version' ) );

/** A theme route is a real page: never the blog home, never a 404. */
add_action(
	'parse_query',
	static function ( WP_Query $q ): void {
		if ( $q->is_main_query() && $q->get( 'priniti_route' ) ) {
			$q->is_home     = false;
			$q->is_404      = false;
			$q->is_singular = false;
			$q->is_archive  = false;
		}
	}
);
/**
 * The design's ?page=N belongs to the catalog (priniti_parse_filters reads it from $_GET). Keep WordPress from
 * also reading it as "page N of this post's content", which turns shop/category/search page 2+ into a 404.
 */
add_filter(
	'request',
	static function ( array $qv ): array {
		if ( ! isset( $qv['page'] ) ) {
			return $qv;
		}
		$shop_slug = function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ? get_post_field( 'post_name', wc_get_page_id( 'shop' ) ) : 'shop';
		$catalog   = isset( $qv['product_cat'] ) || isset( $qv['priniti_route'] ) || 'product' === ( $qv['post_type'] ?? '' ) || ( isset( $qv['pagename'] ) && $qv['pagename'] === $shop_slug );
		if ( $catalog ) {
			unset( $qv['page'] );
		}
		return $qv;
	}
);

/**
 * Catalog pagination uses the design's ?page=N. WordPress would canonical-redirect that to /page/N/ (or /N/ on
 * pages) and drop the filters, so canonical redirects are skipped for catalog and search URLs that carry ?page.
 */
add_filter(
	'redirect_canonical',
	static function ( $redirect ) {
		if ( isset( $_GET['page'] ) && ( priniti_route() || ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		return $redirect;
	}
);

/** Theme routes have no posts by design: never let WordPress turn them into a 404 (or core's /login redirect). */
add_filter( 'pre_handle_404', static fn ( $handled ) => priniti_route() ? true : $handled );

add_filter(
	'posts_pre_query',
	static function ( $posts, WP_Query $q ) {
		return ( $q->is_main_query() && $q->get( 'priniti_route' ) ) ? array() : $posts;
	},
	10,
	2
);

/**
 * Redirects: former policy URLs go to their canonical routes; WordPress search goes to the design's /search;
 * signed-in visitors skip /login and /signup;
 * signed-out visitors opening My Account land on /login.
 */
add_action(
	'template_redirect',
	static function (): void {
		if ( is_search() && ! is_admin() ) {
			wp_safe_redirect( add_query_arg( 'q', rawurlencode( get_search_query( false ) ), priniti_url( '/search' ) ) );
			exit;
		}
		$alias = priniti_route_aliases()[ (string) get_query_var( 'priniti_route' ) ] ?? '';
		if ( $alias ) {
			wp_safe_redirect( priniti_url( $alias ), 301 );
			exit;
		}
		$route = priniti_route();
		if ( in_array( $route, array( 'login', 'signup' ), true ) && is_user_logged_in() ) {
			wp_safe_redirect( priniti_account_url() );
			exit;
		}
		if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() && ! is_wc_endpoint_url( 'lost-password' ) && ! isset( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( priniti_url( '/login' ) );
			exit;
		}
		if ( $route ) {
			status_header( 200 );
		}
	}
);

/**
 * Template selection. Runs after WooCommerce's own template loader.
 */
add_filter(
	'template_include',
	static function ( string $template ): string {
		$route = priniti_route();
		if ( $route ) {
			return PRINITI_DIR . '/templates/' . priniti_routes()[ $route ] . '.php';
		}
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return $template;
		}
		if ( is_product() ) {
			return PRINITI_DIR . '/templates/product.php';
		}
		if ( is_product_category() ) {
			return PRINITI_DIR . '/templates/category.php';
		}
		if ( is_shop() || is_product_tag() || is_product_taxonomy() ) {
			return PRINITI_DIR . '/templates/shop.php';
		}
		if ( is_cart() ) {
			return PRINITI_DIR . '/templates/cart.php';
		}
		if ( is_checkout() ) {
			return PRINITI_DIR . '/templates/checkout.php';
		}
		if ( is_account_page() ) {
			return PRINITI_DIR . '/templates/account.php';
		}
		return $template;
	},
	99
);

/** Where the header's account icon points: My Account when signed in, otherwise /login (design). */
function priniti_account_url(): string {
	if ( is_user_logged_in() && function_exists( 'wc_get_page_permalink' ) ) {
		return (string) wc_get_page_permalink( 'myaccount' );
	}
	return priniti_url( '/login' );
}

/* -------------------------------------------------------------------------
 * Titles, descriptions, robots (reference page metadata)
 * ---------------------------------------------------------------------- */

/**
 * Page metadata for the current request: [title (without the " | Priniti Foods" suffix, or absolute), description, noindex].
 *
 * @return array{title:?string, absolute:bool, description:?string, noindex:bool}
 */
function priniti_page_meta(): array {
	$config = priniti_site_config();
	$meta   = array(
		'title'       => null,
		'absolute'    => false,
		'description' => null,
		'noindex'     => false,
	);
	$route  = priniti_route();
	$legal  = priniti_data( 'legal' )['policies'];

	switch ( $route ) {
		case 'about':
			return array( 'title' => 'About Priniti Foods', 'absolute' => false, 'description' => 'Priniti Foods Pvt. Ltd., founded in 2009, makes namkeen, chips, puffs, sweets, cookies, rusk and more at its units in Sonipat, Haryana and Kanpur, Uttar Pradesh.', 'noindex' => false );
		case 'contact':
			return array( 'title' => 'Contact Priniti Foods | Get in Touch', 'absolute' => true, 'description' => 'Contact Priniti Foods for customer care and feedback, sales and export enquiries. Phone and email details, plus our manufacturing units in Sonipat, Haryana and Kanpur Dehat, Uttar Pradesh.', 'noindex' => false );
		case 'login':
			return array( 'title' => 'Login | Priniti Foods', 'absolute' => true, 'description' => 'Log in to your Priniti Foods account to continue shopping for namkeen, chips, sweets, cookies and more.', 'noindex' => true );
		case 'signup':
			return array( 'title' => 'Create Account | Priniti Foods', 'absolute' => true, 'description' => 'Create a Priniti Foods account to make your shopping for snacks, sweets and bakery products easier.', 'noindex' => true );
		case 'track-order':
			return array( 'title' => 'Track Order | Priniti Foods', 'absolute' => true, 'description' => 'Track your Priniti Foods order with your order ID and the email or mobile number used at checkout.', 'noindex' => true );
		case 'search':
			return array( 'title' => 'Search', 'absolute' => false, 'description' => 'Search Priniti Foods snacks by product name or category.', 'noindex' => true );
		case '':
			break;
		default:
			if ( isset( $legal[ $route ] ) ) {
				return array( 'title' => $legal[ $route ]['metaTitle'], 'absolute' => true, 'description' => $legal[ $route ]['description'], 'noindex' => false );
			}
	}

	if ( is_front_page() ) {
		return array( 'title' => $config['name'] . ' | ' . $config['tagline'], 'absolute' => true, 'description' => $config['description'], 'noindex' => false );
	}
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_product() ) {
			$p = priniti_get_product( (string) get_queried_object()->post_name );
			if ( $p ) {
				return array( 'title' => $p['name'] . ' | ' . $p['categoryName'], 'absolute' => false, 'description' => $p['description'] ?? ( $p['name'] . ' by ' . $config['name'] . '.' ), 'noindex' => false );
			}
		}
		if ( is_product_category() ) {
			$term = get_queried_object();
			$c    = priniti_get_category( $term->slug );
			return array( 'title' => $term->name . ' snacks', 'absolute' => false, 'description' => ( $c['description'] ?? '' ) ?: ( $term->name . ' from ' . $config['name'] . '.' ), 'noindex' => $c && ! $c['published'] );
		}
		if ( is_shop() ) {
			return array( 'title' => 'Shop all snacks', 'absolute' => false, 'description' => 'Browse namkeen, chips, puffs, popcorn, sweets, cookies, rusk and combo packs from Priniti Foods.', 'noindex' => false );
		}
		if ( is_cart() ) {
			return array( 'title' => 'Your cart', 'absolute' => false, 'description' => null, 'noindex' => true );
		}
		if ( is_checkout() ) {
			return array( 'title' => is_order_received_page() ? 'Order received' : 'Checkout', 'absolute' => false, 'description' => null, 'noindex' => true );
		}
		if ( is_account_page() ) {
			return array( 'title' => 'My account', 'absolute' => false, 'description' => null, 'noindex' => true );
		}
	}
	return $meta;
}

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		$meta = priniti_page_meta();
		if ( ! $meta['title'] ) {
			return $title;
		}
		return $meta['absolute'] ? $meta['title'] : $meta['title'] . ' | ' . priniti_site_config()['name'];
	},
	20
);

add_action(
	'wp_head',
	static function (): void {
		$meta = priniti_page_meta();
		if ( $meta['description'] ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $meta['description'] ) );
		}
		// Policy pages: one canonical URL each (the former /terms and /return-policy redirect here).
		$route = priniti_route();
		if ( $route && 'policy' === priniti_routes()[ $route ] ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( priniti_url( '/' . $route ) ) );
		}
	},
	3
);

add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( priniti_page_meta()['noindex'] ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);
