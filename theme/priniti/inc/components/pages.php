<?php
/**
 * Components for the content pages: About (JourneyTimeline), Contact (ContactCards, ReachPanel, LocationCards),
 * Track Order (OrderJourney, TrackHelp), auth (AuthShell), policy pages (PolicyPage) and the shared CTA band.
 * Facts come only from inc/data/company.php (verified, generated from data/company.ts).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/** Theme image URL (assets/images). */
function priniti_asset( string $path ): string {
	return PRINITI_URI . '/assets/images/' . ltrim( $path, '/' );
}

/** Company image from data/company.ts `images` (src /images/about/x.webp -> theme asset). */
function priniti_company_image( string $key ): array {
	$img = priniti_data( 'company' )['images'][ $key ];
	return array(
		'src' => priniti_asset( 'about/' . basename( $img['src'] ) ),
		'alt' => $img['alt'],
	);
}

/** Hidden fields every priniti-core form needs (action, nonce, honeypot). Rendered by the plugin when active. */
function priniti_form_hidden_fields( string $form ): void {
	if ( function_exists( 'priniti_core_form_fields' ) ) {
		priniti_core_form_fields( $form );
	}
}

/** The hero shared by About and Contact (brand gradient + navy pack panel). */
function priniti_page_hero( string $eyebrow, callable $title, string $text, array $ctas, array $packs, string $badge = '' ): void {
	?>
	<section class="relative overflow-hidden bg-linear-to-br from-brand-tint via-blush to-surface">
		<?php priniti_container_open( 'grid items-center gap-6 py-8 lg:grid-cols-[1fr_1.05fr] lg:gap-8 lg:py-10' ); ?>
			<div class="max-w-xl">
				<?php priniti_eyebrow( $eyebrow, 'mb-3' ); ?>
				<h1 class="font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl xl:text-[3.25rem]"><?php $title(); ?></h1>
				<p class="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg"><?php echo esc_html( $text ); ?></p>
				<div class="mt-5 flex flex-col gap-3 sm:flex-row">
					<?php
					foreach ( $ctas as $i => $cta ) {
						priniti_button_link( $cta[1], $cta[0], 0 === $i ? 'primary' : 'outline', 'md', 'h-12 px-6 text-[15px]' );
					}
					?>
				</div>
			</div>
			<div class="relative isolate aspect-[16/10] overflow-hidden rounded-3xl bg-navy">
				<div aria-hidden="true" class="absolute -right-[8%] -top-[22%] -z-10 size-[62%] rounded-full bg-navy-dark"></div>
				<div aria-hidden="true" class="absolute -bottom-[30%] -left-[8%] -z-10 size-[58%] rounded-full bg-brand"></div>
				<?php priniti_pack_fan( $packs, 'absolute -inset-x-[2%] bottom-[8%] top-[14%] size-auto', true ); ?>
				<?php if ( $badge ) : ?>
					<p class="absolute left-4 top-4 rounded-xl bg-white px-3 py-1.5 font-display text-xs font-bold text-brand shadow-lift"><?php echo esc_html( $badge ); ?></p>
				<?php endif; ?>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/** Final red CTA band (About and Contact). */
function priniti_cta_band( string $title, string $text, array $primary, array $secondary ): void {
	?>
	<section class="bg-canvas py-6 lg:py-8">
		<?php priniti_container_open(); ?>
			<div class="relative isolate flex flex-col items-start justify-between gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:flex-row sm:items-center sm:px-10">
				<div aria-hidden="true" class="absolute -right-10 -top-16 -z-10 size-52 rounded-full bg-navy"></div>
				<div>
					<h2 class="font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html( $title ); ?></h2>
					<p class="mt-1 text-sm text-white/90"><?php echo esc_html( $text ); ?></p>
				</div>
				<div class="flex flex-col gap-3 sm:flex-row">
					<?php priniti_button_link( $primary[1], esc_html( $primary[0] ), 'light', 'md', 'h-12 px-6' ); ?>
					<?php priniti_button_link( $secondary[1], esc_html( $secondary[0] ), 'outline', 'md', 'h-12 border-white px-6 text-white hover:bg-white hover:text-brand' ); ?>
				</div>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/** JourneyTimeline (official milestones only). */
function priniti_journey_timeline(): void {
	$items = priniti_data( 'company' )['milestones'];
	$last  = count( $items ) - 1;
	?>
	<ol class="relative grid gap-5 lg:grid-cols-7 lg:gap-3">
		<div aria-hidden="true" class="absolute bottom-6 left-7 top-6 border-l-2 border-dashed border-brand/40 lg:bottom-auto lg:left-8 lg:right-8 lg:top-7 lg:border-l-0 lg:border-t-2"></div>
		<?php foreach ( $items as $i => $m ) : ?>
			<li class="relative flex gap-4 lg:flex-col lg:items-center lg:gap-3 lg:text-center">
				<span class="<?php echo esc_attr( priniti_cx( 'relative z-10 flex size-14 shrink-0 items-center justify-center rounded-full font-display text-sm font-extrabold text-white shadow-lift ring-4 ring-surface', $i === $last ? 'bg-navy' : 'bg-brand' ) ); ?>"><?php echo esc_html( $m['year'] ); ?></span>
				<div class="pt-1 lg:pt-0">
					<h3 class="font-display text-sm font-bold leading-snug sm:text-[15px]"><?php echo esc_html( $m['title'] ); ?></h3>
					<p class="mt-1 text-xs leading-relaxed text-ink-soft"><?php echo esc_html( $m['text'] ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
}

function priniti_contact_row( string $href, string $icon, string $text ): void {
	?>
	<a href="<?php echo esc_url( $href ); ?>" class="group inline-flex w-full items-center gap-2.5 rounded-xl bg-canvas px-3 py-2 text-sm font-semibold text-ink transition-colors hover:bg-brand-tint hover:text-brand">
		<span class="text-brand"><?php priniti_the_icon( $icon, 'size-4' ); ?></span>
		<span class="min-w-0 break-words"><?php echo esc_html( $text ); ?></span>
	</a>
	<?php
}

/** ContactCards. */
function priniti_contact_cards(): void {
	$c     = priniti_data( 'company' )['contact'];
	$cards = array(
		array( 'bg-brand', 'bg-brand-tint text-brand', 'headphones', 'Customer Care', 'Feedback / Consumer Complaint', array( array( 'tel:' . $c['customerCare']['tel'], 'phone', $c['customerCare']['phone'] ), array( 'mailto:' . $c['customerCare']['email'], 'mail', $c['customerCare']['email'] ) ) ),
		array( 'bg-navy', 'bg-navy-tint text-navy', 'phone', 'Sales Department', 'Sales', array( array( 'tel:' . $c['sales']['tel'], 'phone', $c['sales']['phone'] ) ) ),
		array( 'bg-leaf', 'bg-leaf-tint text-leaf', 'globe', 'Export Enquiry', 'Export', array( array( 'tel:' . $c['export']['tel'], 'phone', $c['export']['phone'] ), array( 'mailto:' . $c['export']['email'], 'mail', $c['export']['email'] ) ) ),
	);
	?>
	<ul role="list" class="grid gap-3 md:grid-cols-3 md:gap-4">
		<?php foreach ( $cards as [ $accent, $tint, $icon, $title, $tag, $rows ] ) : ?>
			<li class="relative overflow-hidden rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70 transition-shadow hover:shadow-soft">
				<span aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'absolute inset-x-0 top-0 h-1', $accent ) ); ?>"></span>
				<div class="flex items-center gap-3">
					<span class="<?php echo esc_attr( priniti_cx( 'flex size-10 shrink-0 items-center justify-center rounded-xl', $tint ) ); ?>"><?php priniti_the_icon( $icon, 'size-5' ); ?></span>
					<div class="min-w-0">
						<h3 class="font-display text-base font-bold leading-tight"><?php echo esc_html( $title ); ?></h3>
						<p class="mt-0.5 text-[11px] font-bold uppercase tracking-wide text-ink-soft"><?php echo esc_html( $tag ); ?></p>
					</div>
				</div>
				<div class="mt-3 flex flex-col gap-1.5">
					<?php
					foreach ( $rows as $r ) {
						priniti_contact_row( ...$r );
					}
					?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/** ReachPanel. */
function priniti_reach_panel(): void {
	$c    = priniti_data( 'company' )['contact'];
	$link = 'inline-flex items-center gap-2 text-sm font-semibold text-white transition-colors hover:text-lime';
	$a    = static function ( string $href, string $icon, string $text, string $extra = '' ) use ( $link ): void {
		printf( '<a href="%s" class="%s">%s %s</a>', esc_url( $href ), esc_attr( priniti_cx( $link, $extra ) ), priniti_icon( $icon, 'size-4 shrink-0' ), esc_html( $text ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	};
	?>
	<div class="relative isolate h-full overflow-hidden rounded-3xl bg-navy p-5 text-white shadow-soft sm:p-6">
		<div aria-hidden="true" class="absolute -right-16 -top-16 -z-10 size-56 rounded-full bg-navy-dark"></div>
		<div aria-hidden="true" class="absolute -bottom-20 -left-10 -z-10 size-52 rounded-full bg-brand/80"></div>
		<h2 class="font-display text-xl font-extrabold">Reach Priniti Foods</h2>
		<p class="mt-1 text-sm text-white/75">Call or email the right team directly.</p>
		<dl class="mt-5 flex flex-col gap-4">
			<div>
				<dt class="text-[11px] font-bold uppercase tracking-wide text-lime">Customer Care</dt>
				<dd class="mt-1.5 flex flex-col gap-1"><?php $a( 'tel:' . $c['customerCare']['tel'], 'phone', $c['customerCare']['phone'] ); ?><?php $a( 'mailto:' . $c['customerCare']['email'], 'mail', $c['customerCare']['email'] ); ?></dd>
			</div>
			<div>
				<dt class="text-[11px] font-bold uppercase tracking-wide text-lime">Sales</dt>
				<dd class="mt-1.5"><?php $a( 'tel:' . $c['sales']['tel'], 'phone', $c['sales']['phone'] ); ?></dd>
			</div>
			<div>
				<dt class="text-[11px] font-bold uppercase tracking-wide text-lime">Export</dt>
				<dd class="mt-1.5 flex flex-col gap-1"><?php $a( 'tel:' . $c['export']['tel'], 'phone', $c['export']['phone'] ); ?><?php $a( 'mailto:' . $c['export']['email'], 'mail', $c['export']['email'], 'break-all' ); ?></dd>
			</div>
		</dl>
	</div>
	<?php
}

/** LocationCards: "View Location" is a map SEARCH on the verified address (no coordinates claimed). */
function priniti_location_cards(): void {
	?>
	<ul role="list" class="grid gap-3 md:grid-cols-2 md:gap-4">
		<?php
		foreach ( priniti_data( 'company' )['units'] as $i => $u ) :
			[ $city, $state ] = array_pad( explode( ', ', $u['place'] ), 2, '' );
			?>
			<li class="<?php echo esc_attr( priniti_cx( 'relative isolate overflow-hidden rounded-3xl p-6 text-white shadow-soft sm:p-7', 0 === $i ? 'bg-navy' : 'bg-brand' ) ); ?>">
				<div aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'absolute -right-14 -top-14 -z-10 size-52 rounded-full', 0 === $i ? 'bg-navy-dark' : 'bg-brand-dark/70' ) ); ?>"></div>
				<span aria-hidden="true" class="pointer-events-none absolute -bottom-3 right-3 -z-10 select-none font-display text-6xl font-extrabold uppercase tracking-tight text-white/10 sm:text-7xl"><?php echo esc_html( $city ); ?></span>
				<p class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-wide"><?php priniti_the_icon( 'map-pin', 'size-3.5' ); ?> <?php echo esc_html( $u['name'] . ' · Manufacturing unit' ); ?></p>
				<h3 class="mt-3 font-display text-3xl font-extrabold leading-tight"><?php echo esc_html( $city ); ?></h3>
				<p class="text-sm font-semibold text-white/80"><?php echo esc_html( $state ); ?></p>
				<address class="mt-3 text-sm not-italic leading-relaxed text-white/90">
					<?php foreach ( $u['lines'] as $l ) : ?>
						<span class="block"><?php echo esc_html( $l ); ?></span>
					<?php endforeach; ?>
				</address>
				<a href="<?php echo esc_url( 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( implode( ', ', $u['lines'] ) ) ); ?>" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-lime">
					View Location <?php priniti_the_icon( 'arrow-up-right', 'size-4' ); ?>
					<span class="sr-only"><?php echo esc_html( 'for ' . $u['name'] . ' on a map (opens in a new tab)' ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * OrderJourney. Without an order it is illustrative (no stage marked); with a tracked order the stages up to
 * $current are marked complete and the current one is highlighted.
 */
function priniti_order_journey( int $current = 0 ): void {
	$stages = array(
		array( 'Order Placed', 'shopping-bag' ),
		array( 'Confirmed', 'badge-check' ),
		array( 'Packed', 'package' ),
		array( 'Shipped', 'truck' ),
		array( 'Delivered', 'package-check' ),
	);
	?>
	<div>
		<ol class="relative grid gap-4 md:grid-cols-5 md:gap-2">
			<div aria-hidden="true" class="absolute bottom-6 left-6 top-6 border-l-2 border-dashed border-line md:bottom-auto md:left-10 md:right-10 md:top-6 md:border-l-0 md:border-t-2"></div>
			<?php
			foreach ( $stages as $i => [ $label, $icon ] ) :
				$step  = $i + 1;
				$done  = $current > 0 && $step < $current;
				$now   = $current > 0 && $step === $current;
				$state = $done ? 'bg-leaf text-white' : ( $now ? 'bg-brand text-white' : 'bg-canvas text-ink-soft' );
				?>
				<li class="relative flex items-center gap-4 md:flex-col md:gap-2.5 md:text-center" <?php echo $now ? 'aria-current="step"' : ''; ?>>
					<span class="<?php echo esc_attr( priniti_cx( 'relative z-10 flex size-12 shrink-0 items-center justify-center rounded-full ring-4 ring-surface', $state ) ); ?>">
						<?php if ( ! $done && ! $now ) : ?>
							<span class="absolute inset-0 rounded-full border-2 border-dashed border-line" aria-hidden="true"></span>
						<?php endif; ?>
						<?php priniti_the_icon( $done ? 'check' : $icon, 'size-5' ); ?>
					</span>
					<div>
						<p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft"><?php echo esc_html( "Step {$step}" ); ?><?php echo $done ? ' · Done' : ( $now ? ' · Current' : '' ); ?></p>
						<p class="font-display text-sm font-bold leading-tight"><?php echo esc_html( $label ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( 0 === $current ) : ?>
			<p class="mt-4 text-center text-xs text-ink-soft">Enter your order details above to see where your order is.</p>
		<?php endif; ?>
	</div>
	<?php
}

/** TrackHelp. */
function priniti_track_help(): void {
	$care = priniti_data( 'company' )['contact']['customerCare'];
	$row  = 'inline-flex items-center gap-2 text-sm font-semibold transition-colors hover:text-brand';
	?>
	<div class="grid gap-3 md:grid-cols-2 md:gap-4">
		<div class="relative overflow-hidden rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70">
			<span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-brand"></span>
			<div class="flex items-center gap-3">
				<span class="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand"><?php priniti_the_icon( 'headphones', 'size-5' ); ?></span>
				<div>
					<h2 class="font-display text-base font-bold leading-tight">Need help with an order?</h2>
					<p class="text-xs text-ink-soft">Customer Care</p>
				</div>
			</div>
			<div class="mt-3 flex flex-col gap-1.5">
				<a href="<?php echo esc_url( 'tel:' . $care['tel'] ); ?>" class="<?php echo esc_attr( $row ); ?>"><?php priniti_the_icon( 'phone', 'size-4 text-brand' ); ?> <?php echo esc_html( $care['phone'] ); ?></a>
				<a href="<?php echo esc_url( 'mailto:' . $care['email'] ); ?>" class="<?php echo esc_attr( $row . ' break-all' ); ?>"><?php priniti_the_icon( 'mail', 'size-4 shrink-0 text-brand' ); ?> <?php echo esc_html( $care['email'] ); ?></a>
			</div>
		</div>
		<div class="relative isolate flex flex-col justify-between gap-4 overflow-hidden rounded-2xl bg-navy p-5 text-white shadow-card">
			<div aria-hidden="true" class="absolute -right-10 -top-12 -z-10 size-40 rounded-full bg-navy-dark"></div>
			<div>
				<h2 class="font-display text-lg font-extrabold">Hungry for more?</h2>
				<p class="mt-1 text-sm text-white/80">Browse the Priniti Foods range of snacks, sweets and bakery products.</p>
			</div>
			<?php priniti_button_link( priniti_url( '/shop' ), 'Continue Shopping', 'light', 'md', 'h-12 w-fit px-6' ); ?>
		</div>
	</div>
	<?php
}

/**
 * AuthShell: branded two-column layout used by /login and /signup.
 *
 * @param callable $form Echoes the form.
 */
function priniti_auth_shell( string $title, string $subtitle, string $panel_title, string $panel_text, array $products, callable $form ): void {
	?>
	<section class="bg-canvas py-5 lg:py-10">
		<?php priniti_container_open(); ?>
			<div class="mx-auto grid max-w-5xl overflow-hidden rounded-3xl bg-surface shadow-soft ring-1 ring-line/70 lg:grid-cols-[0.95fr_1.05fr]">
				<div class="relative isolate h-32 overflow-hidden bg-navy sm:h-40 lg:hidden">
					<div aria-hidden="true" class="absolute -right-8 -top-16 -z-10 size-44 rounded-full bg-navy-dark"></div>
					<div aria-hidden="true" class="absolute -bottom-20 -left-6 -z-10 size-44 rounded-full bg-brand"></div>
					<?php priniti_pack_fan( array_slice( $products, 0, 3 ), 'absolute inset-x-[22%] bottom-0 top-3 size-auto sm:inset-x-[30%]' ); ?>
					<p class="absolute left-3 top-3 rounded-lg bg-white px-2.5 py-1 font-display text-[11px] font-bold text-brand shadow-card">Swad Mein No.1</p>
				</div>
				<div class="relative isolate hidden overflow-hidden bg-navy p-8 text-white lg:flex lg:flex-col lg:justify-between">
					<div aria-hidden="true" class="absolute -right-16 -top-16 -z-10 size-60 rounded-full bg-navy-dark"></div>
					<div aria-hidden="true" class="absolute -bottom-24 -left-12 -z-10 size-64 rounded-full bg-brand"></div>
					<div>
						<p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">Priniti Foods</p>
						<h2 class="mt-4 font-display text-3xl font-extrabold leading-tight"><?php echo esc_html( $panel_title ); ?></h2>
						<p class="mt-2 max-w-xs text-sm leading-relaxed text-white/80"><?php echo esc_html( $panel_text ); ?></p>
					</div>
					<div class="relative mt-8 aspect-[4/3] w-full"><?php priniti_pack_fan( $products, 'absolute -inset-x-[3%] bottom-0 top-[6%] size-auto' ); ?></div>
				</div>
				<div class="p-5 sm:p-8 lg:p-10">
					<h1 class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $title ); ?></h1>
					<p class="mt-1 text-sm text-ink-soft sm:text-base"><?php echo esc_html( $subtitle ); ?></p>
					<div class="mt-5"><?php $form(); ?></div>
				</div>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/** PolicyPage prose. */
function priniti_policy_prose( array $paragraphs = array(), array $bullets = array() ): void {
	foreach ( $paragraphs as $p ) {
		echo '<p class="text-[15px] leading-relaxed text-ink-soft">' . esc_html( $p ) . '</p>';
	}
	if ( $bullets ) {
		echo '<ul role="list" class="flex flex-col gap-1.5 text-[15px] leading-relaxed text-ink-soft">';
		foreach ( $bullets as $b ) {
			echo '<li class="flex gap-2.5"><span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rounded-full bg-brand"></span><span>' . esc_html( $b ) . '</span></li>';
		}
		echo '</ul>';
	}
}
