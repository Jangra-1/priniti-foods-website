<?php
/**
 * Homepage sections (reference/nextjs/components/sections/*). Copy comes from inc/data/home.php (generated
 * from data/home.ts); products are real catalog products chosen in inc/data/merchandising.php.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

function priniti_data( string $name ): array {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$cache[ $name ] = apply_filters( "priniti_data_{$name}", require PRINITI_DIR . "/inc/data/{$name}.php" );
	}
	return $cache[ $name ];
}

/** Resolves a design route used in data files ("/shop", "#categories", "/category/combos"). */
function priniti_href( string $href ): string {
	if ( str_starts_with( $href, '#' ) || preg_match( '#^[a-z]+:#i', $href ) ) {
		return $href;
	}
	if ( preg_match( '#^/category/([^/?]+)#', $href, $m ) ) {
		$cat = priniti_get_category( $m[1] );
		if ( $cat && $cat['href'] ) {
			return $cat['href'];
		}
	}
	[ $path, $query ] = array_pad( explode( '?', $href, 2 ), 2, '' );
	return priniti_url( $path ) . ( $query ? '?' . $query : '' );
}

function priniti_section_hero( array $products, array $categories ): void {
	$hero  = priniti_data( 'home' )['hero'];
	$parts = preg_split( '/(?<=,)\s+/', $hero['headline'] );
	$line1 = array_shift( $parts );
	$line2 = implode( ' ', $parts );
	$free  = priniti_site_config()['commerce']['free_shipping_threshold'];

	$tag = static function ( array $p, string $class ): void {
		?>
		<a href="<?php echo esc_url( $p['href'] ); ?>" class="<?php echo esc_attr( priniti_cx( 'absolute z-10 hidden max-w-[9.5rem] rounded-xl bg-surface px-3 py-2 shadow-lift transition-transform hover:-translate-y-0.5 sm:block', $class ) ); ?>">
			<span class="block text-[10px] font-bold uppercase tracking-wide text-brand"><?php echo esc_html( $p['categoryName'] ); ?></span>
			<span class="mt-0.5 block font-display text-xs font-semibold leading-snug"><?php echo esc_html( $p['name'] ); ?></span>
		</a>
		<?php
	};
	?>
	<section aria-labelledby="hero-heading" class="relative overflow-hidden bg-linear-to-br from-brand-tint via-blush to-surface">
		<?php priniti_container_open( 'grid items-center gap-6 py-8 sm:py-10 lg:grid-cols-[1.1fr_1fr] lg:gap-6 lg:py-10' ); ?>
			<div class="max-w-xl">
				<p class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3.5 py-1.5 text-[13px] font-semibold shadow-card">
					<?php priniti_the_icon( 'sparkles', 'size-4 text-brand' ); ?>
					Swad Mein No.1
				</p>
				<h1 id="hero-heading" class="mt-4 font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-5xl xl:text-[3.25rem]">
					<span class="block"><?php echo esc_html( $line1 ); ?></span>
					<?php if ( $line2 ) : ?>
						<span class="block text-brand"><?php echo esc_html( $line2 ); ?></span>
					<?php endif; ?>
				</h1>
				<p class="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg"><?php echo esc_html( $hero['subcopy'] ); ?></p>
				<div class="mt-6 flex flex-col gap-3 sm:flex-row">
					<?php
					priniti_button_link( priniti_href( $hero['primaryCta']['href'] ), priniti_icon( 'shopping-cart', 'size-5' ) . esc_html( $hero['primaryCta']['label'] ) . priniti_icon( 'arrow-right', 'size-4' ), 'primary', 'md', 'h-12 px-6 text-[15px]' );
					priniti_button_link( priniti_href( $hero['secondaryCta']['href'] ), esc_html( $hero['secondaryCta']['label'] ), 'outline', 'md', 'h-12 px-6 text-[15px]' );
					?>
				</div>
				<ul role="list" class="mt-5 flex flex-wrap gap-2" aria-label="Browse categories">
					<?php foreach ( array_slice( $categories, 0, 5 ) as $c ) : ?>
						<li><a href="<?php echo esc_url( $c['href'] ); ?>" class="inline-flex min-h-8 items-center rounded-full bg-surface px-3 text-xs font-semibold shadow-card ring-1 ring-line/70 transition-colors hover:text-brand hover:ring-brand/50"><?php echo esc_html( $c['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $free ) : ?>
					<p class="mt-5 text-sm font-medium text-ink-soft"><?php echo esc_html( 'Free shipping on orders above ' . priniti_format_inr( (float) $free ) ); ?></p>
				<?php endif; ?>
			</div>

			<div class="relative mx-auto aspect-square w-full max-w-[26rem] xl:max-w-[29rem]">
				<div aria-hidden="true" class="absolute inset-0 rounded-full border-2 border-dashed border-brand/25"></div>
				<div aria-hidden="true" class="absolute inset-[5%] overflow-hidden rounded-full bg-brand">
					<div class="absolute -right-[12%] -top-[12%] size-[62%] rounded-full bg-navy"></div>
					<div class="absolute -bottom-[18%] -left-[12%] size-[52%] rounded-full bg-brand-dark"></div>
				</div>
				<?php priniti_pack_fan( $products, 'absolute -inset-x-[3%] bottom-[18%] top-[10%] size-auto', true ); ?>
				<?php
				if ( isset( $products[0] ) ) {
					$tag( $products[0], '-left-1 top-[12%] lg:-left-6' );
				}
				if ( isset( $products[3] ) ) {
					$tag( $products[3], '-right-1 bottom-[12%] lg:-right-6' );
				}
				?>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_categories( array $categories, array $counts ): void {
	?>
	<section id="categories" aria-labelledby="categories-heading" class="scroll-mt-24 bg-surface py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<?php
			priniti_section_heading(
				array(
					'id'      => 'categories-heading',
					'eyebrow' => 'Browse by type',
					'title'   => 'Shop Categories',
					'align'   => 'center',
					'class'   => 'mb-5 lg:mb-6',
				)
			);
			priniti_category_card_list( $categories, $counts );
			?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/** FeaturedProducts: category tabs over product cards; filtering is visual only (interactions.ts). */
function priniti_section_featured_products( string $eyebrow, string $title, string $href, array $products ): void {
	$tabs = array();
	foreach ( $products as $p ) {
		$tabs[ $p['categorySlug'] ] = $p['categoryName'];
	}
	$tabs = array_slice( $tabs, 0, 4, true );
	$pill = static fn ( bool $on ) => priniti_cx( 'inline-flex min-h-8 items-center rounded-full px-3.5 text-[13px] font-semibold transition-colors', $on ? 'bg-brand text-white' : 'bg-surface text-ink ring-1 ring-line hover:text-brand hover:ring-brand/50' );
	?>
	<section aria-labelledby="featured-products-heading" class="bg-canvas py-8 lg:py-10" data-priniti-tabs data-on-class="bg-brand text-white" data-off-class="bg-surface text-ink ring-1 ring-line hover:text-brand hover:ring-brand/50">
		<?php priniti_container_open(); ?>
			<div class="mb-5 flex flex-col gap-3 lg:mb-6 lg:flex-row lg:items-end lg:justify-between">
				<div>
					<?php priniti_eyebrow( $eyebrow, 'mb-2' ); ?>
					<h2 id="featured-products-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $title ); ?></h2>
				</div>
				<div class="flex flex-wrap items-center gap-2">
					<div role="group" aria-label="Filter featured products by category" class="flex flex-wrap gap-2">
						<button type="button" data-tab="all" aria-pressed="true" class="<?php echo esc_attr( $pill( true ) ); ?>">All</button>
						<?php foreach ( $tabs as $slug => $name ) : ?>
							<button type="button" data-tab="<?php echo esc_attr( $slug ); ?>" aria-pressed="false" class="<?php echo esc_attr( $pill( false ) ); ?>"><?php echo esc_html( $name ); ?></button>
						<?php endforeach; ?>
					</div>
					<a href="<?php echo esc_url( $href ); ?>" class="ml-1 inline-flex items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-dark">View All<?php priniti_the_icon( 'arrow-right', 'size-4' ); ?></a>
				</div>
			</div>
			<ul role="list" aria-live="polite" class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 lg:gap-4">
				<?php foreach ( $products as $p ) : ?>
					<li data-tab-item="<?php echo esc_attr( $p['categorySlug'] ); ?>"><?php priniti_product_card( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_why(): void {
	$why   = priniti_data( 'home' )['why'];
	$icons = array( 'wheat' => 'wheat', 'smile' => 'smile', 'sparkles' => 'sparkles', 'handshake' => 'handshake', 'grid' => 'layout-grid' );
	?>
	<section aria-labelledby="why-heading" class="bg-ink py-10 text-white lg:py-12">
		<?php priniti_container_open(); ?>
			<div class="flex flex-col items-center text-center">
				<?php priniti_eyebrow( 'Why choose us', 'mb-2', 'lime' ); ?>
				<h2 id="why-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $why['heading'] ); ?></h2>
			</div>
			<ul role="list" class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-5 lg:gap-4">
				<?php foreach ( $why['items'] as $item ) : ?>
					<li class="rounded-xl border border-white/10 bg-white/5 p-4 text-center transition-colors hover:bg-white/10">
						<span class="mx-auto flex size-10 items-center justify-center rounded-lg bg-lime/15 text-lime"><?php priniti_the_icon( $icons[ $item['icon'] ] ?? 'sparkles', 'size-5' ); ?></span>
						<h3 class="mt-3 font-display text-sm font-semibold sm:text-base"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="mt-1 text-xs leading-relaxed text-white/70"><?php echo esc_html( $item['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_promo_banner( ?string $badge, array $combos, array $visual ): void {
	$promo = priniti_data( 'home' )['promo'];
	$parts = preg_split( '/(?<=\.)\s+/', $promo['headline'] );
	$first = array_shift( $parts );
	?>
	<section aria-labelledby="promo-heading" class="bg-canvas py-4 lg:py-6">
		<?php priniti_container_open(); ?>
			<div class="relative isolate grid items-center gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:px-8 lg:grid-cols-[1.1fr_1fr] lg:px-12 lg:py-8">
				<div aria-hidden="true" class="absolute -right-24 -top-28 -z-10 size-80 rounded-full bg-white/10 sm:size-[26rem]"></div>
				<div aria-hidden="true" class="absolute -bottom-32 -left-20 -z-10 size-72 rounded-full bg-brand-dark/60"></div>
				<div class="max-w-xl">
					<?php if ( $badge ) : ?>
						<span class="mb-3 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
					<h2 id="promo-heading" class="text-balance font-display text-3xl font-extrabold leading-[1.08] tracking-tight sm:text-4xl lg:text-5xl">
						<span class="block"><?php echo esc_html( $first ); ?></span>
						<?php if ( $parts ) : ?>
							<span class="block text-white/80"><?php echo esc_html( implode( ' ', $parts ) ); ?></span>
						<?php endif; ?>
					</h2>
					<p class="mt-3 max-w-md text-base text-white/90"><?php echo esc_html( $promo['description'] ); ?></p>
					<?php if ( $combos ) : ?>
						<ul role="list" class="mt-6 flex flex-wrap gap-2">
							<?php
							foreach ( $combos as $p ) :
								$v = priniti_default_variant( $p );
								?>
								<li><a href="<?php echo esc_url( $p['href'] ); ?>" class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-medium transition-colors hover:bg-white/25"><?php echo esc_html( $p['name'] ); ?><?php if ( null !== ( $v['price'] ?? null ) ) : ?><span class="font-semibold"><?php echo esc_html( priniti_format_inr( (float) $v['price'] ) ); ?></span><?php endif; ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php priniti_button_link( priniti_href( $promo['cta']['href'] ), esc_html( $promo['cta']['label'] ), 'light', 'md', 'mt-5 h-12 px-6' ); ?>
				</div>
				<?php if ( $visual ) : ?>
					<div class="relative mx-auto aspect-[16/9] w-full max-w-sm lg:max-w-none">
						<div aria-hidden="true" class="absolute inset-x-[10%] inset-y-0 rounded-full bg-navy"></div>
						<?php priniti_pack_fan( $visual, 'absolute inset-x-[2%] bottom-[10%] top-[16%] size-auto' ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/**
 * @param array<int, array{eyebrow:string, title:string, href:string, cta:string, tone:string, products:array}> $tiles
 */
function priniti_section_promo_tiles( array $tiles ): void {
	$shown = array_filter( $tiles, static fn ( $t ) => $t['products'] );
	if ( ! $shown ) {
		return;
	}
	$tones = array( 'navy' => 'bg-navy', 'leaf' => 'bg-leaf' );
	?>
	<section aria-label="Shop sweets and bakery" class="bg-canvas py-4 lg:py-6">
		<?php priniti_container_open(); ?>
			<ul role="list" class="grid gap-3 md:grid-cols-2 md:gap-4">
				<?php foreach ( $shown as $t ) : ?>
					<li>
						<div class="<?php echo esc_attr( priniti_cx( 'relative isolate flex h-full min-h-44 overflow-hidden rounded-3xl p-5 text-white sm:p-6', $tones[ $t['tone'] ] ) ); ?>">
							<div aria-hidden="true" class="absolute -right-16 -top-20 -z-10 size-64 rounded-full bg-white/10"></div>
							<div class="relative z-10 flex max-w-[62%] flex-col justify-between gap-4 sm:max-w-[58%]">
								<div>
									<p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/75"><?php echo esc_html( $t['eyebrow'] ); ?></p>
									<h3 class="mt-1 font-display text-xl font-extrabold leading-tight sm:text-2xl"><?php echo esc_html( $t['title'] ); ?></h3>
									<p class="mt-1 text-xs leading-relaxed text-white/85 sm:text-sm"><?php echo esc_html( implode( ', ', array_column( $t['products'], 'name' ) ) . ' and more.' ); ?></p>
								</div>
								<a href="<?php echo esc_url( $t['href'] ); ?>" class="inline-flex w-fit items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-xs font-semibold sm:text-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-ink"><?php echo esc_html( $t['cta'] ); ?><?php priniti_the_icon( 'arrow-right', 'size-4' ); ?></a>
							</div>
							<div class="absolute bottom-0 right-3 aspect-[3/4] w-[32%] max-w-32 translate-y-[6%] drop-shadow-xl sm:right-6">
								<?php priniti_product_image( $t['products'][0]['images'][0] ?? null, $t['products'][0]['name'], '(min-width:768px) 20vw, 38vw', 'bg-transparent p-0' ); ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_featured_product( array $product ): void {
	$variant = priniti_purchasable_variant( $product );
	?>
	<section aria-labelledby="featured-heading" class="bg-surface py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<div class="grid items-center gap-5 overflow-hidden rounded-3xl bg-blush p-4 sm:p-6 md:grid-cols-2 lg:gap-10 lg:p-8">
				<div class="relative aspect-[5/4] overflow-hidden rounded-2xl bg-surface">
					<?php priniti_product_image( $product['images'][0] ?? null, $product['name'], '(min-width:768px) 44vw, 90vw' ); ?>
				</div>
				<div>
					<div class="flex flex-wrap items-center gap-2"><span class="<?php echo esc_attr( priniti_badge_classes( 'brand' ) ); ?>">Featured</span></div>
					<h2 id="featured-heading" class="mt-3 font-display text-2xl font-extrabold sm:text-3xl"><?php echo esc_html( $product['name'] ); ?></h2>
					<p class="mt-1 text-sm text-ink-soft"><?php echo esc_html( $product['categoryName'] ); ?></p>
					<?php
					if ( $product['reviewCount'] && null !== $product['rating'] ) {
						priniti_rating_stars( (float) $product['rating'], (int) $product['reviewCount'], 'md', 'mt-4' );
					}
					?>
					<?php if ( $product['description'] ) : ?>
						<p class="mt-4 max-w-md text-ink-soft"><?php echo esc_html( $product['description'] ); ?></p>
					<?php endif; ?>
					<?php
					if ( $variant ) {
						priniti_price_display( (float) ( $variant['mrp'] ?? $variant['price'] ), (float) $variant['price'], 'lg', true, 'mt-5' );
					}
					?>
					<div class="mt-5 flex flex-col gap-3 sm:flex-row">
						<?php priniti_add_to_cart_button( $product, $variant, array( 'class' => 'h-12 px-6' ) ); ?>
						<?php priniti_button_link( $product['href'], 'View details', 'secondary', 'md', 'h-12 px-6' ); ?>
					</div>
				</div>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/**
 * Full-size AddToCartButton (not icon-only): "Price coming soon" (disabled) when the variant has no real price.
 *
 * @param array{size?:string, full_width?:bool, class?:string, open_cart?:bool, label?:string} $opts
 */
function priniti_add_to_cart_button( array $product, ?array $variant, array $opts = array() ): void {
	$size = $opts['size'] ?? 'md';
	$full = $opts['full_width'] ?? false;
	if ( ! $variant || ! priniti_is_purchasable( $variant ) ) {
		printf( '<button type="button" disabled class="%s">%s</button>', esc_attr( priniti_button_classes( 'secondary', $size, $full, $opts['class'] ?? '' ) ), esc_html__( 'Price coming soon', 'priniti' ) );
		return;
	}
	?>
	<button type="button" data-priniti-add="<?php echo esc_attr( priniti_cart_payload( $product, $variant ) ); ?>" <?php echo ! empty( $opts['open_cart'] ) ? 'data-open-cart' : ''; ?> class="<?php echo esc_attr( priniti_button_classes( 'primary', $size, $full, $opts['class'] ?? '' ) ); ?>">
		<span data-when="idle" class="inline-flex items-center gap-2"><?php priniti_the_icon( 'shopping-bag', 'size-4' ); ?><?php echo esc_html( $opts['label'] ?? 'Add to cart' ); ?></span>
		<span data-when="added" hidden class="inline-flex items-center gap-2"><?php priniti_the_icon( 'check', 'size-4' ); ?>Added</span>
	</button>
	<?php
}

function priniti_section_reviews( array $reviews ): void {
	$copy = priniti_data( 'home' )['reviews'];
	?>
	<section aria-labelledby="reviews-heading" class="bg-surface py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<?php priniti_section_heading( array( 'id' => 'reviews-heading', 'eyebrow' => 'Customer love', 'title' => $copy['heading'], 'align' => 'center', 'class' => 'mb-5' ) ); ?>
			<?php if ( ! $reviews ) : ?>
				<div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-line bg-canvas px-6 py-8 text-center">
					<span class="flex size-11 items-center justify-center rounded-full bg-navy-tint text-navy"><?php priniti_the_icon( 'message-square-text', 'size-6' ); ?></span>
					<div>
						<p class="font-display text-lg font-semibold"><?php echo esc_html( $copy['emptyTitle'] ); ?></p>
						<p class="mt-1 max-w-md text-ink-soft"><?php echo esc_html( $copy['emptyText'] ); ?></p>
					</div>
					<?php priniti_button_link( priniti_url( '/shop' ), 'Browse products', 'secondary' ); ?>
				</div>
			<?php else : ?>
				<ul role="list" class="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-3 md:gap-5 md:overflow-visible md:px-0">
					<?php foreach ( $reviews as $r ) : ?>
						<li class="w-[85%] shrink-0 snap-start sm:w-[60%] md:w-auto"><?php priniti_review_card( $r ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/**
 * @param array<int, array{value:string, label:string}> $stats
 */
function priniti_section_stats( string $title, string $description, array $stats ): void {
	?>
	<section aria-labelledby="stats-heading" class="relative isolate overflow-hidden bg-brand py-9 text-white lg:py-10">
		<div aria-hidden="true" class="absolute -right-20 -top-24 -z-10 size-80 rounded-full bg-navy"></div>
		<div aria-hidden="true" class="absolute -bottom-28 left-10 -z-10 size-64 rounded-full bg-brand-dark/60"></div>
		<?php priniti_container_open(); ?>
			<div class="text-center">
				<h2 id="stats-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $title ); ?></h2>
				<p class="mt-1 text-sm text-white/85"><?php echo esc_html( $description ); ?></p>
			</div>
			<dl class="mt-6 grid grid-cols-3 gap-4">
				<?php foreach ( $stats as $s ) : ?>
					<div class="text-center">
						<dt class="order-2 mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/80 sm:text-xs"><?php echo esc_html( $s['label'] ); ?></dt>
						<dd class="font-display text-3xl font-extrabold tracking-tight sm:text-5xl"><?php echo esc_html( $s['value'] ); ?></dd>
						<span aria-hidden="true" class="mx-auto mt-2 block h-1 w-8 rounded-full bg-white/50"></span>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_brand_story( array $products ): void {
	$story = priniti_data( 'home' )['brandStory'];
	?>
	<section aria-labelledby="story-heading" class="bg-canvas py-8 lg:py-12">
		<?php priniti_container_open( 'grid items-center gap-6 lg:grid-cols-2 lg:gap-12' ); ?>
			<div class="max-w-lg">
				<?php priniti_eyebrow( 'Our story', 'mb-3' ); ?>
				<h2 id="story-heading" class="text-balance font-display text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl"><?php echo esc_html( $story['heading'] ); ?></h2>
				<div class="mt-3 flex flex-col gap-3 text-base leading-relaxed text-ink-soft">
					<?php foreach ( $story['paragraphs'] as $p ) : ?>
						<p><?php echo esc_html( $p ); ?></p>
					<?php endforeach; ?>
				</div>
				<?php priniti_button_link( priniti_href( $story['cta']['href'] ), esc_html( $story['cta']['label'] ), 'dark', 'md', 'mt-5 h-12 px-6' ); ?>
			</div>
			<div class="relative isolate aspect-[16/11] overflow-hidden rounded-3xl bg-navy">
				<div aria-hidden="true" class="absolute -left-[12%] -top-[15%] -z-10 size-[60%] rounded-full bg-navy-dark"></div>
				<div aria-hidden="true" class="absolute -bottom-[25%] -right-[10%] -z-10 size-[58%] rounded-full bg-brand"></div>
				<?php priniti_pack_fan( $products, 'px-[4%] pt-[10%]' ); ?>
				<p class="absolute right-4 top-4 rounded-xl bg-brand px-3 py-1.5 text-center font-display text-xs font-bold text-white shadow-lift">Swad Mein No.1</p>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_newsletter(): void {
	$copy   = priniti_data( 'home' )['newsletter'];
	$result = function_exists( 'priniti_core_form_result' ) ? priniti_core_form_result( 'newsletter' ) : null;
	?>
	<section id="newsletter" aria-labelledby="newsletter-heading" class="scroll-mt-24 bg-blush py-9 lg:py-12">
		<?php priniti_container_open(); ?>
			<div class="mx-auto flex max-w-2xl flex-col items-center text-center">
				<span class="flex size-11 items-center justify-center rounded-xl bg-brand text-white shadow-soft"><?php priniti_the_icon( 'mail', 'size-5' ); ?></span>
				<h2 id="newsletter-heading" class="mt-4 font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $copy['headline'] ); ?></h2>
				<p class="mt-1 text-base text-ink-soft"><?php echo esc_html( $copy['copy'] ); ?></p>
				<form method="post" action="<?php echo esc_url( priniti_url( '/' ) . '#newsletter' ); ?>" novalidate data-priniti-form class="mt-5 flex w-full flex-col gap-2">
					<?php priniti_form_hidden_fields( 'newsletter' ); ?>
					<div class="flex flex-col gap-3 sm:flex-row sm:items-start">
						<div class="flex-1 text-left">
							<?php
							priniti_input(
								array(
									'label'      => 'Email address',
									'hide_label' => true,
									'type'       => 'email',
									'name'       => 'email',
									'value'      => $result['values']['email'] ?? '',
									'error'      => $result['errors']['email'] ?? '',
									'class'      => 'h-12 bg-surface shadow-card',
									'validate'   => 'email',
									'attrs'      => array( 'autocomplete' => 'email', 'placeholder' => 'Enter your email address' ),
								)
							);
							?>
						</div>
						<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', false, 'h-12 px-6 sm:shrink-0' ) ); ?>"><?php priniti_the_icon( 'bell', 'size-4' ); ?>Subscribe</button>
					</div>
					<p role="status" class="text-sm text-ink-soft"><?php echo esc_html( $result['message'] ?? 'No spam: new launches and offers only.' ); ?></p>
				</form>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

function priniti_section_social( array $products ): void {
	$copy  = priniti_data( 'home' )['social'];
	$tiles = array_slice( array_values( array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) ) ), 0, (int) $copy['tileCount'] );
	$links = array_filter( priniti_site_config()['social'], static fn ( $s ) => ! empty( $s['href'] ) );
	?>
	<section aria-labelledby="social-heading" class="bg-surface py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<?php priniti_section_heading( array( 'id' => 'social-heading', 'eyebrow' => 'Stay connected', 'title' => $copy['heading'], 'description' => $copy['description'], 'align' => 'center', 'class' => 'mb-5' ) ); ?>
			<ul role="list" aria-hidden="true" class="grid grid-cols-3 gap-2 sm:gap-3 lg:grid-cols-6">
				<?php foreach ( $tiles as $p ) : ?>
					<li><div class="relative aspect-square overflow-hidden rounded-xl bg-blush ring-1 ring-line/60"><img src="<?php echo esc_url( $p['images'][0]['src'] ); ?>" alt="" loading="lazy" decoding="async" class="absolute inset-0 size-full object-contain p-3"></div></li>
				<?php endforeach; ?>
			</ul>
			<div class="mt-5 flex flex-wrap items-center justify-center gap-3">
				<?php if ( $links ) : ?>
					<?php foreach ( $links as $s ) : ?>
						<?php priniti_button_link( $s['href'], priniti_icon( 'instagram', 'size-4' ) . esc_html( $s['label'] ), 'outline', 'md', '', array( 'target' => '_blank', 'rel' => 'noopener noreferrer' ) ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<p class="text-sm text-ink-soft">Social profile links are coming soon.</p>
				<?php endif; ?>
			</div>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}
