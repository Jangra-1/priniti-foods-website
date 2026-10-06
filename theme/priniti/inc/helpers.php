<?php
/**
 * Template helpers: class joining, icons, URLs and the shared UI class recipes
 * (ports of reference/nextjs/components/ui/Button.tsx and IconButton.tsx).
 *
 * The Next.js app resolves conflicting Tailwind classes with tailwind-merge. PHP has no equivalent, so the
 * recipes below take the conflicting parts (size, variant) as arguments instead of appending overrides.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * Joins truthy class names (clsx equivalent).
 *
 * @param string|false|null ...$parts Class strings; falsy values are skipped.
 */
function priniti_cx( ...$parts ): string {
	return trim( implode( ' ', array_filter( array_map( 'trim', array_filter( $parts, 'is_string' ) ) ) ) );
}

/**
 * Site URL for one of the design's routes (e.g. '/shop'), following the site's trailing-slash setting.
 */
function priniti_url( string $path = '/' ): string {
	$path = '/' . ltrim( $path, '/' );
	if ( '/' === $path ) {
		return home_url( '/' );
	}
	return home_url( user_trailingslashit( untrailingslashit( $path ) ) );
}

/**
 * True when the current request is for the given design route.
 */
function priniti_is_current_path( string $path ): bool {
	$request = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$home    = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$current = '/' . trim( substr( $request, strlen( rtrim( $home, '/' ) ) ), '/' );
	return untrailingslashit( $current ) === untrailingslashit( '/' . ltrim( $path, '/' ) ) || ( '/' === $path && '/' === $current );
}

/**
 * Inline Lucide icon (same icon set and version as lucide-react in the reference app).
 * Decorative by default (aria-hidden); pass a label for a meaningful standalone icon.
 */
function priniti_icon( string $name, string $class = '', string $label = '' ): string {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file           = PRINITI_DIR . '/assets/icons/' . sanitize_file_name( $name ) . '.svg';
		$svg            = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg            = (string) preg_replace( '/<!--.*?-->/s', '', $svg );
		$svg            = (string) preg_replace( '/\s*class="[^"]*"/', '', $svg, 1 );
		$cache[ $name ] = trim( (string) preg_replace( '/\s+/', ' ', $svg ) );
	}
	if ( '' === $cache[ $name ] ) {
		return '';
	}
	$attrs = sprintf( ' class="%s"', esc_attr( priniti_cx( 'lucide', 'lucide-' . $name, $class ) ) );
	$attrs .= $label ? sprintf( ' role="img" aria-label="%s"', esc_attr( $label ) ) : ' aria-hidden="true" focusable="false"';
	return (string) preg_replace( '/^<svg/', '<svg' . $attrs, $cache[ $name ], 1 );
}

/**
 * Echoes an icon. The SVG comes from the theme's own files, never from user input.
 */
function priniti_the_icon( string $name, string $class = '', string $label = '' ): void {
	echo priniti_icon( $name, $class, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * IconButton styles (components/ui/IconButton.tsx). Default size is size-11; pass the size to override it.
 */
function priniti_icon_button_classes( string $size = 'size-11', string $extra = '' ): string {
	return priniti_cx( 'relative inline-flex shrink-0 items-center justify-center rounded-full text-ink transition duration-200 hover:bg-ink/5 active:scale-95', $size, $extra );
}

/**
 * Button styles (components/ui/Button.tsx: buttonStyles()).
 *
 * @param string $variant primary|secondary|outline|light|ghost|dark.
 * @param string $size    sm|md|lg.
 */
function priniti_button_classes( string $variant = 'primary', string $size = 'md', bool $full_width = false, string $extra = '' ): string {
	$variants = array(
		'primary'   => 'bg-brand text-white hover:bg-brand-dark hover:shadow-[0_10px_24px_-10px_rgb(227_0_22/0.7)]',
		'secondary' => 'border border-ink/20 bg-surface text-ink hover:border-ink hover:bg-canvas',
		'outline'   => 'border-2 border-ink bg-transparent text-ink hover:bg-ink hover:text-white',
		'light'     => 'bg-white text-brand hover:bg-white/90',
		'ghost'     => 'text-ink hover:bg-ink/5',
		'dark'      => 'bg-ink text-white hover:bg-ink/85',
	);
	$sizes    = array(
		'sm' => 'h-9 px-4 text-sm',
		'md' => 'h-11 px-5 text-sm',
		'lg' => 'h-14 px-8 text-base',
	);
	return priniti_cx(
		'inline-flex select-none items-center justify-center gap-2 rounded-xl font-semibold shadow-none transition duration-200 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50',
		$variants[ $variant ] ?? $variants['primary'],
		$sizes[ $size ] ?? $sizes['md'],
		$full_width ? 'w-full' : '',
		$extra
	);
}

/**
 * Container wrapper classes (components/layout/Container.tsx).
 */
function priniti_container_classes( string $extra = '' ): string {
	return priniti_cx( 'mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8', $extra );
}

/**
 * Renders a template part with arguments (thin wrapper so templates read like the React components).
 *
 * @param array<string, mixed> $args Arguments available as $args in the part.
 */
function priniti_part( string $slug, array $args = array() ): void {
	get_template_part( 'template-parts/' . $slug, null, $args );
}
