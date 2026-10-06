<?php
/**
 * Theme supports, document title, robots and other global behaviour.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'priniti', PRINITI_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );
	}
);

/**
 * Document title: "Page | Priniti Foods", and "Priniti Foods | Snacks for every moment" on the home page
 * (reference/nextjs/app/layout.tsx metadata).
 */
add_filter( 'document_title_separator', static fn (): string => '|' );
add_filter(
	'document_title_parts',
	static function ( array $parts ): array {
		$config = priniti_site_config();
		if ( is_front_page() ) {
			return array(
				'title'   => $config['name'],
				'tagline' => $config['tagline'],
			);
		}
		$parts['site'] = $config['name'];
		unset( $parts['tagline'] );
		return $parts;
	}
);

/**
 * Keep the store out of search engines until launch (the reference app does the same).
 * Launch checklist: return false from this filter, e.g. add_filter( 'priniti_noindex', '__return_false' ).
 */
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( apply_filters( 'priniti_noindex', true ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}
);

/**
 * Theme colour (reference viewport.themeColor).
 */
add_action(
	'wp_head',
	static function (): void {
		echo '<meta name="theme-color" content="#f7f8fb">' . "\n";
	},
	1
);

/**
 * Body class hook used by the stylesheet.
 */
add_filter(
	'body_class',
	static function ( array $classes ): array {
		$classes[] = 'priniti';
		return $classes;
	}
);

/** WordPress emoji polyfill: not used by the design (saves a script and an inline style on every page). */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
