<?php
/**
 * Decorative vector shapes for the design system: hero, banner and gallery backdrops.
 * Inline SVG with no image requests. Every shape is aria-hidden and uses currentColor,
 * so callers colour them with text-* utilities and position them with absolute utilities.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints one decorative shape.
 *
 * @param string $kind  blob | dots | ring | wave | sparkle | leaf | grain | squiggle.
 * @param string $class Positioning / colour utilities.
 */
function priniti_decor( string $kind, string $class = '' ): void {
	$shapes = array(
		'blob'     => '<svg viewBox="0 0 200 200"><path fill="currentColor" d="M41.3-61.7C54.6-50.5 66.4-39.1 71.6-25 76.9-10.9 75.6 5.9 70.2 21.5 64.8 37.1 55.3 51.5 41.9 61.4 28.5 71.3 11.2 76.7-6.4 75.6-24.1 74.5-42.1 66.9-54.6 54.3-67.2 41.7-74.4 24.1-76.1 6.1-77.8-11.9-74.1-30.3-63.4-42.6-52.7-54.9-35.1-61.1-18.9-71.3-2.6-81.5 12.3-95.8 25.4-91.7 38.6-87.6 28-72.9 41.3-61.7Z" transform="translate(100 100)"/></svg>',
		'dots'     => '<svg viewBox="0 0 120 120"><defs><pattern id="pd" width="15" height="15" patternUnits="userSpaceOnUse"><circle cx="2.5" cy="2.5" r="2.5" fill="currentColor"/></pattern></defs><rect width="120" height="120" fill="url(#pd)"/></svg>',
		'ring'     => '<svg viewBox="0 0 200 200" fill="none"><circle cx="100" cy="100" r="96" stroke="currentColor" stroke-width="2.5" stroke-dasharray="6 9" stroke-linecap="round"/></svg>',
		'wave'     => '<svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path fill="currentColor" d="M0 40c120-26 240-26 360 0s240 26 360 0 240-26 360 0 240 26 360 0v40H0Z"/></svg>',
		'sparkle'  => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 0c.6 6.4 5.6 11.4 12 12-6.4.6-11.4 5.6-12 12-.6-6.4-5.6-11.4-12-12C6.4 11.4 11.4 6.4 12 0Z"/></svg>',
		'leaf'     => '<svg viewBox="0 0 48 48" fill="none"><path fill="currentColor" d="M42 6C24 6 8 14 8 30c0 4 1.4 8 4 11 2-10 9-18 20-23-9 6-14 13-16 24 2 .6 4 1 6 1 14 0 20-14 20-37Z"/></svg>',
		'grain'    => '<svg viewBox="0 0 32 64" fill="none"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M16 62V6"/><g fill="currentColor"><ellipse cx="10" cy="14" rx="4" ry="7" transform="rotate(-25 10 14)"/><ellipse cx="22" cy="14" rx="4" ry="7" transform="rotate(25 22 14)"/><ellipse cx="10" cy="28" rx="4" ry="7" transform="rotate(-25 10 28)"/><ellipse cx="22" cy="28" rx="4" ry="7" transform="rotate(25 22 28)"/><ellipse cx="10" cy="42" rx="4" ry="7" transform="rotate(-25 10 42)"/><ellipse cx="22" cy="42" rx="4" ry="7" transform="rotate(25 22 42)"/><ellipse cx="16" cy="5" rx="3.5" ry="6"/></g></svg>',
		'squiggle' => '<svg viewBox="0 0 120 24" fill="none"><path stroke="currentColor" stroke-width="4" stroke-linecap="round" d="M3 12c9-12 18-12 27 0s18 12 27 0 18-12 27 0 18 12 27 0"/></svg>',
	);
	if ( ! isset( $shapes[ $kind ] ) ) {
		return;
	}
	// Unique pattern ids so several dot grids can share a page.
	static $n = 0;
	$svg = 'dots' === $kind ? str_replace( array( 'id="pd"', 'url(#pd)' ), array( 'id="pd' . ( ++$n ) . '"', 'url(#pd' . $n . ')' ), $shapes[ $kind ] ) : $shapes[ $kind ];
	$svg = preg_replace( '/^<svg /', '<svg aria-hidden="true" focusable="false" class="' . esc_attr( priniti_cx( 'pointer-events-none absolute', $class ) ) . '" ', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup above.
}

/**
 * A soft decorative backdrop: a tinted blob, a dot grid and a sparkle or two. Used behind product imagery.
 *
 * @param string $tone brand | navy | leaf | lime.
 */
function priniti_decor_backdrop( string $tone = 'brand', string $class = '' ): void {
	$tint = array(
		'brand' => array( 'text-brand/10', 'text-brand/25', 'text-brand' ),
		'navy'  => array( 'text-navy/10', 'text-navy/20', 'text-navy' ),
		'leaf'  => array( 'text-leaf/12', 'text-leaf/25', 'text-leaf' ),
		'lime'  => array( 'text-lime/20', 'text-lime/40', 'text-leaf' ),
	)[ $tone ] ?? array( 'text-brand/10', 'text-brand/25', 'text-brand' );
	?>
	<div aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'pointer-events-none absolute inset-0 overflow-hidden', $class ) ); ?>">
		<?php
		priniti_decor( 'blob', priniti_cx( 'left-1/2 top-1/2 size-[85%] -translate-x-1/2 -translate-y-1/2', $tint[0] ) );
		priniti_decor( 'dots', priniti_cx( 'right-[6%] top-[6%] size-20 sm:size-24', $tint[1] ) );
		priniti_decor( 'ring', priniti_cx( 'bottom-[5%] left-[5%] size-24 sm:size-28', $tint[1] ) );
		priniti_decor( 'sparkle', priniti_cx( 'left-[12%] top-[14%] size-4 opacity-60', $tint[2] ) );
		priniti_decor( 'sparkle', priniti_cx( 'bottom-[18%] right-[12%] size-3 opacity-50', $tint[2] ) );
		?>
	</div>
	<?php
}

/** Tone name for a category index, matching priniti_tint_for_index() so each category keeps one accent. */
function priniti_tone_for_index( int $index ): string {
	$tones = array( 'navy', 'brand', 'leaf', 'lime' );
	return $tones[ $index % count( $tones ) ];
}
