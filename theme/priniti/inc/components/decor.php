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
 * @param string $kind  blob | dots | ring | wave | sparkle | leaf | grain | squiggle | chili | burst | confetti | flower | cookie | zigzag | arch.
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
		'chili'    => '<svg viewBox="0 0 64 64"><path fill="currentColor" d="M50 14c-4 0-6 3-7 6-6 1-11 5-15 11-6 9-11 18-21 24-2 1-1 4 1 4 14 0 26-6 34-16 5-6 8-12 8-18 3-1 5-4 5-7 0-2-2-4-5-4Z"/><path fill="#009035" d="M44 19c1-5 5-8 10-8l1 3c-4 0-6 2-7 6Z"/></svg>',
		'burst'    => '<svg viewBox="0 0 100 100"><path fill="currentColor" d="m50 2 8 20 19-11-3 22 22-2-14 17 18 12-21 6 10 20-21-5-2 22-15-16-15 16-2-22-21 5 10-20-21-6 18-12L4 31l22 2-3-22 19 11Z"/></svg>',
		'confetti' => '<svg viewBox="0 0 120 120"><g fill="currentColor"><circle cx="12" cy="18" r="5"/><circle cx="96" cy="12" r="4"/><circle cx="60" cy="60" r="3"/><circle cx="20" cy="96" r="4"/><circle cx="104" cy="88" r="6"/><rect x="44" y="10" width="10" height="4" rx="2" transform="rotate(30 49 12)"/><rect x="80" y="50" width="12" height="4" rx="2" transform="rotate(-35 86 52)"/><rect x="24" y="56" width="12" height="4" rx="2" transform="rotate(60 30 58)"/><rect x="62" y="98" width="12" height="4" rx="2" transform="rotate(-15 68 100)"/></g></svg>',
		'flower'   => '<svg viewBox="0 0 100 100"><g fill="currentColor"><ellipse cx="50" cy="22" rx="11" ry="20"/><ellipse cx="50" cy="78" rx="11" ry="20"/><ellipse cx="22" cy="50" rx="20" ry="11"/><ellipse cx="78" cy="50" rx="20" ry="11"/><ellipse cx="30" cy="30" rx="9" ry="17" transform="rotate(-45 30 30)"/><ellipse cx="70" cy="70" rx="9" ry="17" transform="rotate(-45 70 70)"/><ellipse cx="70" cy="30" rx="9" ry="17" transform="rotate(45 70 30)"/><ellipse cx="30" cy="70" rx="9" ry="17" transform="rotate(45 30 70)"/></g><circle cx="50" cy="50" r="10" fill="#fff" opacity=".7"/></svg>',
		'cookie'   => '<svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="28" fill="currentColor"/><g fill="#000" opacity=".18"><circle cx="22" cy="22" r="4"/><circle cx="40" cy="18" r="3"/><circle cx="44" cy="36" r="4"/><circle cx="26" cy="42" r="3.5"/><circle cx="34" cy="30" r="2.5"/></g></svg>',
		'zigzag'   => '<svg viewBox="0 0 120 24" fill="none"><path stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" d="m3 18 13-12 13 12 13-12 13 12 13-12 13 12 13-12 13 12"/></svg>',
		'arch'     => '<svg viewBox="0 0 200 240" preserveAspectRatio="none"><path fill="currentColor" d="M0 240V100C0 45 45 0 100 0s100 45 100 100v140Z"/></svg>',
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

/**
 * Rotating circular stamp ("Swad Mein No.1"), text on a circle path. Decorative: the text is also on the page elsewhere.
 */
function priniti_decor_stamp( string $text, string $class = '' ): void {
	static $n = 0;
	$id = 'stamp-' . ( ++$n );
	?>
	<div aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'pointer-events-none absolute flex items-center justify-center rounded-full bg-surface shadow-lift', $class ) ); ?>">
		<svg viewBox="0 0 120 120" class="absolute inset-0 size-full motion-safe:animate-spin-slow">
			<defs><path id="<?php echo esc_attr( $id ); ?>" d="M60 60m-44 0a44 44 0 1 1 88 0a44 44 0 1 1-88 0"/></defs>
			<text class="fill-ink font-display text-[10px] font-bold uppercase tracking-[0.12em]"><textPath href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $text ); ?></textPath></text>
		</svg>
		<span class="relative flex size-[42%] items-center justify-center rounded-full bg-brand text-white"><?php priniti_the_icon( 'sparkles', 'size-1/2' ); ?></span>
	</div>
	<?php
}

/**
 * Pack "stage": 1-4 real product packs arranged on a podium in front of an arch, with soft shadows.
 * Used by the shop and category heroes. Packs are decorative (aria-hidden): the products are listed below.
 *
 * @param array  $products Catalog products (only those with an image are used).
 * @param string $tone     navy | brand | leaf | lime.
 */
function priniti_pack_stage( array $products, string $tone = 'brand', string $class = '', bool $priority = false ): void {
	$packs = array_slice( array_values( array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) ) ), 0, 4 );
	if ( ! $packs ) {
		return;
	}
	$arch = array(
		'navy'  => array( 'text-navy', 'text-brand' ),
		'brand' => array( 'text-brand', 'text-navy' ),
		'leaf'  => array( 'text-leaf', 'text-lime' ),
		'lime'  => array( 'text-lime', 'text-leaf' ),
	)[ $tone ] ?? array( 'text-brand', 'text-navy' );
	// [ left %, width %, bottom %, rotate deg, z ] per pack count; the first pack is always the hero (centre, largest).
	$layouts = array(
		1 => array( array( 26, 48, 9, 0, 3 ) ),
		2 => array( array( 34, 42, 9, 3, 3 ), array( 6, 36, 11, -9, 2 ) ),
		3 => array( array( 29, 42, 9, 0, 3 ), array( 2, 34, 11, -10, 2 ), array( 64, 34, 11, 10, 2 ) ),
		4 => array( array( 29, 42, 9, 0, 4 ), array( 1, 33, 11, -12, 2 ), array( 66, 33, 11, 12, 2 ), array( 16, 27, 30, -5, 1 ) ),
	);
	$layout = $layouts[ count( $packs ) ];
	?>
	<div aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'relative aspect-[5/4] w-full', $class ) ); ?>">
		<?php priniti_decor( 'arch', priniti_cx( 'bottom-[14%] left-[22%] h-[74%] w-[56%] opacity-95', $arch[0] ) ); ?>
		<?php priniti_decor( 'arch', priniti_cx( 'bottom-[14%] left-[33%] h-[52%] w-[34%] opacity-90', $arch[1] ) ); ?>
		<div class="absolute bottom-[6%] left-[8%] h-[16%] w-[84%] rounded-[50%] bg-surface shadow-[0_18px_30px_-14px_rgb(21_26_46/0.35)]"></div>
		<?php foreach ( $packs as $i => $p ) : ?>
			<?php [ $left, $width, $bottom, $rotate, $z ] = $layout[ $i ]; ?>
			<div class="absolute aspect-[3/4] drop-shadow-[0_16px_18px_rgb(21_26_46/0.28)] transition-transform duration-500" style="<?php echo esc_attr( "left:{$left}%;width:{$width}%;bottom:{$bottom}%;z-index:{$z};transform:rotate({$rotate}deg)" ); ?>">
				<img src="<?php echo esc_url( $p['images'][0]['src'] ); ?>" <?php echo ! empty( $p['images'][0]['srcset'] ) ? 'srcset="' . esc_attr( $p['images'][0]['srcset'] ) . '"' : ''; ?> sizes="(min-width:1024px) 14vw, 30vw" alt="" <?php echo $priority && 0 === $i ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async" class="absolute inset-0 size-full object-contain">
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
