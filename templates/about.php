<?php
/**
 * /about (reference/nextjs/app/about/page.tsx). Verified facts and real photographs only.
 * Valued-partner logos are intentionally not shown (no verified logo assets).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$company    = priniti_data( 'company' );
$packs      = priniti_get_products_by_slugs( priniti_data( 'merchandising' )['heroSlugs'] );
$categories = priniti_get_categories();
$counts     = priniti_category_counts();
$card       = 'rounded-2xl bg-surface p-4 shadow-card ring-1 ring-line/70';
$section    = 'py-8 lg:py-10';
$img        = static fn ( string $k ) => priniti_company_image( $k );

get_header();

priniti_page_hero(
	'About Priniti Foods',
	static function () use ( $company ): void {
		echo '<span class="block">Good Food. Great Journey.</span><span class="block text-brand">' . esc_html( 'Since ' . $company['founded'] . '.' ) . '</span>';
	},
	$company['legalName'] . ' is an Indian snacks and FMCG business, making namkeen, chips, puffs, sweets, cookies, rusk and more. Swad Mein No.1.',
	array(
		array( 'Explore Products' . priniti_icon( 'arrow-right', 'size-4' ), priniti_url( '/shop' ) ),
		array( 'Contact Us', priniti_url( '/contact' ) ),
	),
	$packs,
	'Since ' . $company['founded']
);
?>

<section aria-labelledby="who-heading" class="<?php echo esc_attr( "bg-surface {$section}" ); ?>">
	<?php priniti_container_open( 'grid items-center gap-6 lg:grid-cols-[1fr_1.1fr] lg:gap-10' ); ?>
		<div class="relative aspect-[4/3] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70">
			<?php $f = $img( 'facility' ); ?>
			<img src="<?php echo esc_url( $f['src'] ); ?>" alt="<?php echo esc_attr( $f['alt'] ); ?>" loading="lazy" class="absolute inset-0 size-full object-cover">
			<p class="absolute bottom-3 left-3 rounded-xl bg-white/95 px-3 py-1.5 text-xs font-bold text-ink shadow-card">Our facility</p>
		</div>
		<div>
			<?php priniti_eyebrow( 'Who we are', 'mb-3' ); ?>
			<h2 id="who-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Snacks made for every moment</h2>
			<div class="mt-3 flex flex-col gap-3 text-[15px] leading-relaxed text-ink-soft">
				<?php foreach ( $company['intro'] as $p ) : ?>
					<p><?php echo esc_html( $p ); ?></p>
				<?php endforeach; ?>
			</div>
			<ul role="list" aria-label="Core values" class="mt-4 flex flex-wrap gap-2">
				<?php foreach ( $company['values'] as $v ) : ?>
					<li class="rounded-full bg-brand-tint px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide text-brand"><?php echo esc_html( $v ); ?></li>
				<?php endforeach; ?>
			</ul>
			<div class="mt-5 flex items-center gap-4 rounded-2xl bg-canvas p-3 ring-1 ring-line/70">
				<?php $md = $img( 'managingDirector' ); ?>
				<div class="relative size-20 shrink-0 overflow-hidden rounded-xl bg-white"><img src="<?php echo esc_url( $md['src'] ); ?>" alt="<?php echo esc_attr( $md['alt'] ); ?>" loading="lazy" class="absolute inset-0 size-full object-cover object-top"></div>
				<div>
					<p class="font-display text-lg font-bold leading-tight"><?php echo esc_html( $company['managingDirector']['name'] ); ?></p>
					<p class="text-sm font-semibold text-brand"><?php echo esc_html( $company['managingDirector']['title'] ); ?></p>
					<p class="mt-0.5 text-sm text-ink-soft"><?php echo esc_html( $company['managingDirector']['experience'] ); ?></p>
				</div>
			</div>
		</div>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="reach-heading" class="relative isolate overflow-hidden bg-brand py-9 text-white lg:py-11">
	<div aria-hidden="true" class="absolute -right-16 -top-24 -z-10 size-72 rounded-full bg-navy"></div>
	<div aria-hidden="true" class="absolute -bottom-28 left-10 -z-10 size-60 rounded-full bg-brand-dark/60"></div>
	<?php priniti_container_open(); ?>
		<div class="text-center">
			<p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/80">Our reach</p>
			<h2 id="reach-heading" class="mt-1 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">The scale of Priniti Foods</h2>
		</div>
		<dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 lg:grid-cols-6">
			<?php foreach ( $company['reach'] as $r ) : ?>
				<div class="text-center">
					<dd class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl"><?php echo esc_html( $r['value'] ); ?></dd>
					<span aria-hidden="true" class="mx-auto my-1.5 block h-1 w-8 rounded-full bg-white/50"></span>
					<dt class="text-[10px] font-bold uppercase tracking-[0.14em] text-white/85 sm:text-xs"><?php echo esc_html( $r['label'] ); ?></dt>
				</div>
			<?php endforeach; ?>
		</dl>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="journey-heading" class="<?php echo esc_attr( "bg-surface {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<?php priniti_section_heading( array( 'id' => 'journey-heading', 'eyebrow' => 'Our journey', 'title' => 'From 2009 to today', 'align' => 'center', 'class' => 'mb-6' ) ); ?>
		<?php priniti_journey_timeline(); ?>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="mfg-heading" class="<?php echo esc_attr( "bg-canvas {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<?php priniti_section_heading( array( 'id' => 'mfg-heading', 'eyebrow' => 'Manufacturing strength', 'title' => 'Two advanced units, one standard', 'description' => 'Two manufacturing units in Sonipat, Haryana and Kanpur, Uttar Pradesh.', 'align' => 'center', 'class' => 'mb-6' ) ); ?>
		<div class="grid gap-3 md:grid-cols-2 md:gap-4">
			<?php foreach ( array( array( 'facility', 'Our facility' ), array( 'production', 'Packaging and production' ) ) as [ $key, $caption ] ) : ?>
				<?php $p = $img( $key ); ?>
				<figure class="relative aspect-[16/9] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70">
					<img src="<?php echo esc_url( $p['src'] ); ?>" alt="<?php echo esc_attr( $p['alt'] ); ?>" loading="lazy" class="absolute inset-0 size-full object-cover">
					<figcaption class="absolute inset-x-0 bottom-0 bg-linear-to-t from-ink/80 to-transparent px-4 pb-3 pt-10 text-sm font-semibold text-white"><?php echo esc_html( $caption ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
		<div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 md:mt-4 md:gap-4">
			<?php foreach ( $company['units'] as $i => $u ) : ?>
				<div class="<?php echo esc_attr( $card ); ?>">
					<span class="flex size-10 items-center justify-center rounded-xl bg-brand-tint text-brand"><?php priniti_the_icon( 'map-pin', 'size-5' ); ?></span>
					<p class="mt-3 text-[11px] font-bold uppercase tracking-wide text-brand"><?php echo esc_html( $u['name'] ); ?></p>
					<p class="font-display text-lg font-semibold leading-tight"><?php echo esc_html( $u['place'] ); ?></p>
					<?php if ( 1 === $i ) : ?>
						<p class="mt-1 text-xs font-semibold text-navy">Established 2022 as our second manufacturing unit</p>
					<?php endif; ?>
					<address class="mt-1.5 text-xs not-italic leading-relaxed text-ink-soft">
						<?php foreach ( $u['lines'] as $l ) : ?>
							<span class="block"><?php echo esc_html( $l ); ?></span>
						<?php endforeach; ?>
					</address>
				</div>
			<?php endforeach; ?>
			<div class="<?php echo esc_attr( $card ); ?>">
				<span class="flex size-10 items-center justify-center rounded-xl bg-navy-tint text-navy"><?php priniti_the_icon( 'factory', 'size-5' ); ?></span>
				<p class="mt-3 text-[11px] font-bold uppercase tracking-wide text-ink-soft">Manufacturing capacity</p>
				<p class="font-display text-2xl font-extrabold leading-tight text-navy"><?php echo esc_html( $company['manufacturing']['capacity'] ); ?></p>
			</div>
			<div class="<?php echo esc_attr( $card ); ?>">
				<span class="flex size-10 items-center justify-center rounded-xl bg-navy-tint text-navy"><?php priniti_the_icon( 'warehouse', 'size-5' ); ?></span>
				<p class="mt-3 text-[11px] font-bold uppercase tracking-wide text-ink-soft">Warehouse availability</p>
				<p class="font-display text-2xl font-extrabold leading-tight text-navy"><?php echo esc_html( $company['manufacturing']['warehouse'] ); ?></p>
				<p class="mt-1 text-xs text-ink-soft">Ready to be shipped to our valued customers.</p>
			</div>
		</div>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="range-heading" class="<?php echo esc_attr( "bg-surface {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<?php
		priniti_section_heading(
			array(
				'id'          => 'range-heading',
				'eyebrow'     => 'Our product range',
				'title'       => sprintf( '%s categories, %s SKUs', $company['range']['categories'], $company['range']['skus'] ),
				'description' => sprintf( '%d products are available to browse in this online store today.', array_sum( $counts ) ),
				'href'        => priniti_url( '/shop' ),
				'link_label'  => 'Shop all',
				'class'       => 'mb-5',
			)
		);
		priniti_category_card_list( $categories, $counts );
		?>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="quality-heading" class="relative isolate overflow-hidden bg-navy py-10 text-white lg:py-12">
	<div aria-hidden="true" class="absolute inset-0 -z-10 opacity-[0.12]" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 22px 22px"></div>
	<div aria-hidden="true" class="absolute -right-20 -top-24 -z-10 size-72 rounded-full bg-navy-dark"></div>
	<?php priniti_container_open(); ?>
		<div class="flex flex-col items-center text-center">
			<?php priniti_eyebrow( 'Quality & certifications', 'mb-2', 'lime' ); ?>
			<h2 id="quality-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Quality at Every Step</h2>
			<p class="mt-1.5 max-w-xl text-sm text-white/75">Quality, integrity and customer-centricity guide every decision. Certifications and approvals as listed by Priniti Foods.</p>
		</div>
		<ul role="list" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
			<?php foreach ( $company['certifications'] as $c ) : ?>
				<li class="flex flex-col items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-3 py-4 text-center backdrop-blur-sm">
					<span class="flex size-10 items-center justify-center rounded-xl bg-lime/20 text-lime"><?php priniti_the_icon( 'shield-check', 'size-5' ); ?></span>
					<span class="font-display text-sm font-bold"><?php echo esc_html( $c ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php priniti_container_close(); ?>
</section>

<section aria-labelledby="team-heading" class="<?php echo esc_attr( "bg-surface {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<div class="grid items-end gap-5 lg:grid-cols-[1fr_1.4fr] lg:gap-10">
			<div>
				<?php priniti_eyebrow( 'Our team', 'mb-3' ); ?>
				<h2 id="team-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><span class="text-brand"><?php echo esc_html( $company['team']['total'] ); ?></span> professionals</h2>
				<p class="mt-2 text-[15px] leading-relaxed text-ink-soft"><?php echo esc_html( $company['team']['description'] ); ?></p>
			</div>
			<dl class="grid grid-cols-3 gap-2.5">
				<?php foreach ( $company['team']['groups'] as $g ) : ?>
					<div class="rounded-2xl bg-brand px-3 py-4 text-center text-white shadow-card">
						<dd class="font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html( $g['value'] ); ?></dd>
						<dt class="mt-1 text-[10px] font-bold uppercase tracking-wide text-white/90 sm:text-xs"><?php echo esc_html( $g['label'] ); ?></dt>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
		<div class="mt-5 grid gap-3 md:grid-cols-2 md:gap-4">
			<?php foreach ( array( 'team1', 'team2' ) as $key ) : ?>
				<?php $p = $img( $key ); ?>
				<div class="relative aspect-[3/2] overflow-hidden rounded-3xl bg-navy-tint shadow-soft ring-1 ring-line/70"><img src="<?php echo esc_url( $p['src'] ); ?>" alt="<?php echo esc_attr( $p['alt'] ); ?>" loading="lazy" class="absolute inset-0 size-full object-cover"></div>
			<?php endforeach; ?>
		</div>
		<p class="mt-3 flex items-center gap-2 text-xs text-ink-soft">
			<?php priniti_the_icon( 'users', 'size-4 text-brand' ); ?>
			<?php echo esc_html( implode( ' · ', array_map( static fn ( $g ) => $g['value'] . ' ' . strtolower( $g['label'] ), $company['team']['groups'] ) ) ); ?>
		</p>
	<?php priniti_container_close(); ?>
</section>

<?php
priniti_cta_band( 'Explore the Priniti Range', 'Discover snacks, sweets and bakery products from Priniti Foods.', array( 'Explore Products', priniti_url( '/shop' ) ), array( 'Contact Us', priniti_url( '/contact' ) ) );
get_footer();
