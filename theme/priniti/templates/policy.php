<?php
/**
 * /privacy-policy, /terms, /shipping-policy, /return-policy (reference/nextjs/components/legal/PolicyPage.tsx).
 * Text comes from inc/data/legal.php (generated from data/legal.ts: DRAFT, for business and legal review).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$legal   = priniti_data( 'legal' );
$slug    = priniti_route();
$policy  = $legal['policies'][ $slug ];
$meta    = $legal['meta'];
$company = priniti_data( 'company' );
$care    = $company['contact']['customerCare'];
$related = array(
	'privacy-policy'  => 'Privacy Policy',
	'terms'           => 'Terms & Conditions',
	'shipping-policy' => 'Shipping Policy',
	'return-policy'   => 'Returns & Refund Policy',
);

$toc = static function () use ( $policy ): void {
	echo '<ol class="flex flex-col gap-1 text-sm">';
	foreach ( $policy['sections'] as $i => $s ) {
		printf(
			'<li><a href="#%s" class="flex gap-2 rounded-lg px-2.5 py-1.5 text-ink-soft transition-colors hover:bg-brand-tint hover:text-brand"><span class="font-bold text-brand/80">%s</span>%s</a></li>',
			esc_attr( $s['id'] ),
			esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ),
			esc_html( $s['title'] )
		);
	}
	echo '</ol>';
};

get_header();
?>
<section class="bg-linear-to-br from-brand-tint via-blush to-surface">
	<?php priniti_container_open( 'py-7 lg:py-9' ); ?>
		<?php priniti_eyebrow( 'Priniti Foods', 'mb-2' ); ?>
		<h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl"><?php echo esc_html( $policy['title'] ); ?></h1>
		<p class="mt-2 max-w-2xl text-base leading-relaxed text-ink-soft"><?php echo esc_html( $policy['summary'] ); ?></p>
		<div class="mt-4">
			<ul role="list" class="flex flex-wrap gap-2 text-xs font-semibold">
				<li class="rounded-full bg-lime-tint px-3 py-1 text-ink"><?php echo esc_html( $meta['draftNote'] ); ?></li>
				<li class="rounded-full bg-surface px-3 py-1 text-ink-soft ring-1 ring-line"><?php echo esc_html( 'Last updated: ' . $meta['lastUpdated'] ); ?></li>
				<li class="rounded-full bg-surface px-3 py-1 text-ink-soft ring-1 ring-line"><?php echo esc_html( $policy['status'] ); ?></li>
			</ul>
		</div>
	<?php priniti_container_close(); ?>
</section>

<section class="bg-canvas py-6 lg:py-10">
	<?php priniti_container_open( 'grid gap-5 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-8' ); ?>
		<aside class="lg:sticky lg:top-24 lg:self-start">
			<nav aria-label="<?php echo esc_attr( $policy['title'] . ': on this page' ); ?>">
				<details class="rounded-2xl bg-surface p-3 shadow-card ring-1 ring-line/70 lg:hidden">
					<summary class="cursor-pointer list-none px-2 py-1 font-display text-sm font-bold [&::-webkit-details-marker]:hidden">On this page</summary>
					<div class="mt-2"><?php $toc(); ?></div>
				</details>
				<div class="hidden rounded-2xl bg-surface p-3 shadow-card ring-1 ring-line/70 lg:block">
					<p class="px-2.5 pb-2 text-[11px] font-bold uppercase tracking-wide text-ink-soft">On this page</p>
					<?php $toc(); ?>
				</div>
			</nav>
		</aside>

		<div class="flex min-w-0 flex-col gap-3 lg:gap-4">
			<?php foreach ( $policy['sections'] as $i => $s ) : ?>
				<section id="<?php echo esc_attr( $s['id'] ); ?>" aria-labelledby="<?php echo esc_attr( $s['id'] . '-title' ); ?>" class="scroll-mt-24 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6">
					<h2 id="<?php echo esc_attr( $s['id'] . '-title' ); ?>" class="flex items-baseline gap-2.5 font-display text-lg font-extrabold tracking-tight sm:text-xl">
						<span class="text-sm font-bold text-brand"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php echo esc_html( $s['title'] ); ?>
					</h2>
					<div class="mt-3 flex flex-col gap-3">
						<?php priniti_policy_prose( $s['paragraphs'] ?? array(), $s['bullets'] ?? array() ); ?>
						<?php foreach ( $s['subsections'] ?? array() as $sub ) : ?>
							<div class="flex flex-col gap-2 rounded-xl bg-canvas p-4">
								<h3 class="font-display text-base font-bold"><?php echo esc_html( $sub['title'] ); ?></h3>
								<?php priniti_policy_prose( $sub['paragraphs'] ?? array(), $sub['bullets'] ?? array() ); ?>
							</div>
						<?php endforeach; ?>
						<?php if ( ! empty( $s['pending'] ) ) : ?>
							<p class="flex items-start gap-2.5 rounded-xl border-l-4 border-lime bg-lime-tint px-4 py-3 text-sm leading-relaxed text-ink">
								<?php priniti_the_icon( 'info', 'mt-0.5 size-4 shrink-0 text-ink-soft' ); ?>
								<span><strong class="font-semibold"><?php echo esc_html( $meta['pendingLabel'] . '.' ); ?></strong> <?php echo esc_html( $s['pending'] ); ?></span>
							</p>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>

			<section aria-labelledby="policy-contact-title" class="relative overflow-hidden rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6">
				<span aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-brand"></span>
				<div class="flex items-center gap-3">
					<span class="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand"><?php priniti_the_icon( 'headphones', 'size-5' ); ?></span>
					<div>
						<h2 id="policy-contact-title" class="font-display text-lg font-extrabold leading-tight">Questions? Contact Customer Care</h2>
						<p class="text-xs text-ink-soft"><?php echo esc_html( $company['legalName'] . ' · Customer Care / Feedback / Consumer Complaint' ); ?></p>
					</div>
				</div>
				<div class="mt-4 flex flex-col gap-2 text-sm font-semibold sm:flex-row sm:flex-wrap sm:gap-x-6">
					<a href="<?php echo esc_url( 'tel:' . $care['tel'] ); ?>" class="inline-flex items-center gap-2 transition-colors hover:text-brand"><?php priniti_the_icon( 'phone', 'size-4 text-brand' ); ?> <?php echo esc_html( $care['phone'] ); ?></a>
					<a href="<?php echo esc_url( 'mailto:' . $care['email'] ); ?>" class="inline-flex items-center gap-2 break-all transition-colors hover:text-brand"><?php priniti_the_icon( 'mail', 'size-4 shrink-0 text-brand' ); ?> <?php echo esc_html( $care['email'] ); ?></a>
				</div>
				<ul role="list" aria-label="Manufacturing units" class="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-2">
					<?php foreach ( $company['units'] as $u ) : ?>
						<li class="flex gap-2.5 text-xs leading-relaxed text-ink-soft">
							<?php priniti_the_icon( 'map-pin', 'mt-0.5 size-4 shrink-0 text-brand' ); ?>
							<address class="not-italic">
								<span class="block font-bold text-ink"><?php echo esc_html( $u['name'] . ' (manufacturing unit)' ); ?></span>
								<?php foreach ( $u['lines'] as $l ) : ?>
									<span class="block"><?php echo esc_html( $l ); ?></span>
								<?php endforeach; ?>
							</address>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="mt-4 text-xs text-ink-soft">More ways to reach us are on the <a href="<?php echo esc_url( priniti_url( '/contact' ) ); ?>" class="font-semibold text-brand underline-offset-4 hover:underline">Contact page</a>.</p>
			</section>

			<nav aria-label="Other policies" class="rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70">
				<p class="text-[11px] font-bold uppercase tracking-wide text-ink-soft">Other policies</p>
				<ul role="list" class="mt-2 flex flex-wrap gap-2">
					<?php foreach ( $related as $r_slug => $label ) : ?>
						<?php if ( $r_slug !== $slug ) : ?>
							<li><a href="<?php echo esc_url( priniti_url( '/' . $r_slug ) ); ?>" class="inline-flex min-h-9 items-center rounded-full bg-canvas px-3.5 text-sm font-semibold ring-1 ring-line transition-colors hover:text-brand hover:ring-brand/50"><?php echo esc_html( $label ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
	<?php priniti_container_close(); ?>
</section>
<?php
get_footer();
