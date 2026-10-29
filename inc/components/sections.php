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

function priniti_section_categories( array $categories, array $counts ): void {
	?>
	<section id="categories" aria-labelledby="categories-heading" class="scroll-mt-24 bg-surface py-8 lg:py-10" data-reveal>
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
	<section aria-labelledby="featured-products-heading" class="bg-canvas py-8 lg:py-10" data-priniti-tabs data-on-class="bg-brand text-white" data-off-class="bg-surface text-ink ring-1 ring-line hover:text-brand hover:ring-brand/50" data-reveal>
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
	<section aria-labelledby="why-heading" class="bg-ink py-10 text-white lg:py-12" data-reveal>
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
	<section aria-labelledby="promo-heading" class="bg-canvas py-4 lg:py-6" data-reveal>
		<?php priniti_container_open(); ?>
			<div class="relative isolate grid items-center gap-5 overflow-hidden rounded-3xl bg-brand px-6 py-8 text-white sm:px-8 lg:grid-cols-[1.1fr_1fr] lg:px-12 lg:py-8">
				<div aria-hidden="true" class="absolute -right-24 -top-28 -z-10 size-80 rounded-full bg-white/10 sm:size-[26rem]"></div>
				<div aria-hidden="true" class="absolute -bottom-32 -left-20 -z-10 size-72 rounded-full bg-brand-dark/60"></div>
				<?php
				priniti_decor( 'dots', 'left-[42%] top-6 -z-10 hidden size-24 text-white/15 md:block' );
				priniti_decor( 'burst', 'right-[44%] bottom-6 -z-10 hidden size-14 text-lime/70 motion-safe:animate-spin-slow lg:block' );
				priniti_decor( 'sparkle', 'left-6 top-6 -z-10 size-4 text-white/70' );
				priniti_decor( 'squiggle', 'bottom-5 left-8 -z-10 h-4 w-20 text-white/25' );
				?>
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
	<section aria-label="Shop sweets and bakery" class="bg-canvas py-4 lg:py-6" data-reveal>
		<?php priniti_container_open(); ?>
			<ul role="list" class="grid gap-3 md:grid-cols-2 md:gap-4">
				<?php foreach ( $shown as $t ) : ?>
					<li>
						<div class="<?php echo esc_attr( priniti_cx( 'relative isolate flex h-full min-h-52 overflow-hidden rounded-3xl p-5 text-white sm:min-h-56 sm:p-7', $tones[ $t['tone'] ] ) ); ?>">
							<div aria-hidden="true" class="absolute -right-16 -top-20 -z-10 size-64 rounded-full bg-white/10"></div>
							<?php priniti_decor( 'dots', 'left-[44%] top-4 -z-10 size-20 text-white/15' ); ?>
							<?php priniti_decor( 'navy' === $t['tone'] ? 'flower' : 'cookie', 'bottom-4 left-[46%] -z-10 size-10 text-white/15' ); ?>
							<div class="relative z-10 flex max-w-[62%] flex-col justify-between gap-4 sm:max-w-[58%]">
								<div>
									<p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/75"><?php echo esc_html( $t['eyebrow'] ); ?></p>
									<h3 class="mt-1 font-display text-xl font-extrabold leading-tight sm:text-2xl"><?php echo esc_html( $t['title'] ); ?></h3>
									<p class="mt-1 text-xs leading-relaxed text-white/85 sm:text-sm"><?php echo esc_html( implode( ', ', array_column( $t['products'], 'name' ) ) . ' and more.' ); ?></p>
								</div>
								<a href="<?php echo esc_url( $t['href'] ); ?>" class="inline-flex w-fit items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-xs font-semibold sm:text-sm backdrop-blur-sm transition-colors hover:bg-white hover:text-ink"><?php echo esc_html( $t['cta'] ); ?><?php priniti_the_icon( 'arrow-right', 'size-4' ); ?></a>
							</div>
							<div aria-hidden="true" class="absolute -bottom-3 -right-2 w-[52%] max-w-80 sm:right-0">
								<?php priniti_pack_stage( $t['products'], 'navy' === $t['tone'] ? 'lime' : 'brand', 'aspect-[6/5] [&>svg]:hidden' ); ?>
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
	<section aria-labelledby="featured-heading" class="bg-surface py-8 lg:py-10" data-reveal>
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
	<section aria-labelledby="reviews-heading" class="bg-surface py-8 lg:py-10" data-reveal>
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
	<section aria-labelledby="stats-heading" class="relative isolate overflow-hidden bg-brand py-9 text-white lg:py-10" data-reveal>
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
	<section aria-labelledby="story-heading" class="bg-canvas py-8 lg:py-12" data-reveal>
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
	<section id="newsletter" aria-labelledby="newsletter-heading" class="scroll-mt-24 bg-blush py-9 lg:py-12" data-reveal>
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
	<section aria-labelledby="social-heading" class="bg-surface py-8 lg:py-10" data-reveal>
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

/**
 * "Snacks for every mood": discovery tiles that route moods to existing categories. Moods with no
 * published category are skipped, so every tile leads somewhere real.
 */
function priniti_section_moods( array $categories, array $counts ): void {
	$by_slug = array_column( $categories, null, 'slug' );
	$moods   = array(
		array( 'title' => 'Tea-time', 'text' => 'Cookies and rusk for your chai', 'icon' => 'coffee', 'slugs' => array( 'cookies', 'rusk' ), 'tone' => 'bg-lime-tint text-leaf' ),
		array( 'title' => 'Movie night', 'text' => 'Popcorn and potato chips', 'icon' => 'popcorn', 'slugs' => array( 'popcorn', 'potato-chips' ), 'tone' => 'bg-brand-tint text-brand' ),
		array( 'title' => 'Spicy cravings', 'text' => 'Traditional namkeen', 'icon' => 'flame', 'slugs' => array( 'indian-traditional-namkeen', 'namkeen' ), 'tone' => 'bg-brand-tint text-brand-dark' ),
		array( 'title' => 'Festive treats', 'text' => 'Sweets for celebrations', 'icon' => 'gift', 'slugs' => array( 'sweets' ), 'tone' => 'bg-navy-tint text-navy' ),
		array( 'title' => 'Kids’ favourites', 'text' => 'Puffs, fryums and rings', 'icon' => 'smile', 'slugs' => array( 'puffs-fryums', 'ringo-star' ), 'tone' => 'bg-leaf-tint text-leaf' ),
		array( 'title' => 'Crunchy munchies', 'text' => 'CharChare sticks', 'icon' => 'zap', 'slugs' => array( 'charchare' ), 'tone' => 'bg-navy-tint text-navy' ),
	);
	$tiles = array();
	foreach ( $moods as $m ) {
		foreach ( $m['slugs'] as $slug ) {
			$match = $by_slug[ $slug ] ?? null;
			if ( ! $match ) {
				foreach ( $categories as $c ) {
					if ( str_starts_with( $c['slug'], $slug ) ) {
						$match = $c;
						break;
					}
				}
			}
			if ( $match && ! empty( $counts[ $match['slug'] ] ) ) {
				$tiles[] = $m + array( 'category' => $match );
				break;
			}
		}
	}
	if ( count( $tiles ) < 3 ) {
		return;
	}
	?>
	<section aria-labelledby="moods-heading" class="bg-surface py-10 lg:py-14" data-reveal>
		<?php priniti_container_open(); ?>
			<?php priniti_section_heading( array( 'id' => 'moods-heading', 'eyebrow' => 'Pick your craving', 'title' => 'Snacks for Every Mood', 'align' => 'center', 'class' => 'mb-6 lg:mb-8' ) ); ?>
			<ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 lg:gap-4">
				<?php foreach ( $tiles as $t ) : ?>
					<li>
						<a href="<?php echo esc_url( $t['category']['href'] ); ?>" class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-canvas p-4 ring-1 ring-line/70 transition duration-300 hover:-translate-y-1 hover:bg-surface hover:shadow-lift">
							<?php priniti_decor( 'blob', '-right-10 -top-10 size-28 text-surface transition-transform duration-500 group-hover:scale-110' ); ?>
							<span class="<?php echo esc_attr( priniti_cx( 'relative flex size-11 items-center justify-center rounded-xl', $t['tone'] ) ); ?>"><?php priniti_the_icon( $t['icon'], 'size-5' ); ?></span>
							<span class="relative mt-4 font-display text-base font-bold leading-tight"><?php echo esc_html( $t['title'] ); ?></span>
							<span class="relative mt-1 text-xs leading-snug text-ink-soft"><?php echo esc_html( $t['text'] ); ?></span>
							<span class="relative mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand"><?php echo esc_html( 'Shop ' . $t['category']['name'] ); ?><?php priniti_the_icon( 'arrow-right', 'size-3.5 transition-transform group-hover:translate-x-0.5' ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/**
 * "Shop Priniti snacks on the go": the online store works on any phone. A CSS phone frame shows real packs.
 * No app-store badges: there is no app.
 */
function priniti_section_on_the_go( array $products ): void {
	$packs = array_slice( array_values( array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) ) ), 0, 4 );
	?>
	<section aria-labelledby="otg-heading" class="relative overflow-hidden bg-navy py-10 text-white lg:py-14" data-reveal>
		<?php
		priniti_decor( 'blob', '-left-24 -top-24 size-96 text-navy-dark' );
		priniti_decor( 'dots', 'right-[8%] top-8 size-28 text-white/10' );
		priniti_decor( 'ring', 'bottom-[-4rem] right-[30%] size-56 text-white/10' );
		priniti_decor( 'sparkle', 'left-[46%] top-10 size-5 text-lime' );
		?>
		<?php priniti_container_open( 'relative grid items-center gap-10 md:grid-cols-[1.2fr_1fr]' ); ?>
			<div>
				<p class="text-xs font-bold uppercase tracking-[0.16em] text-lime">Order from your phone</p>
				<h2 id="otg-heading" class="mt-3 font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Shop Priniti Snacks<br class="hidden sm:block"> On The Go</h2>
				<p class="mt-3 max-w-md text-white/80">The Priniti store works on any phone: browse the range, add to cart and track your order wherever you are.</p>
				<ul role="list" class="mt-6 grid max-w-md gap-3 sm:grid-cols-2">
					<?php foreach ( array( array( 'search', 'Find snacks fast' ), array( 'shopping-bag', 'Quick cart & checkout' ), array( 'package-check', 'Track your order' ), array( 'heart', 'Save favourites' ) ) as $f ) : ?>
						<li class="flex items-center gap-2.5 text-sm font-medium"><span class="flex size-8 items-center justify-center rounded-lg bg-white/10"><?php priniti_the_icon( $f[0], 'size-4' ); ?></span><?php echo esc_html( $f[1] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="mt-7 flex flex-wrap gap-3">
					<?php
					priniti_button_link( priniti_url( '/shop' ), priniti_icon( 'shopping-cart', 'size-4' ) . 'Start shopping', 'primary', 'md', 'h-12 px-6' );
					priniti_button_link( priniti_url( '/track-order' ), 'Track an order', 'outline', 'md', 'h-12 border-white/40 bg-transparent px-6 text-white hover:border-white hover:bg-white/10' );
					?>
				</div>
			</div>
			<?php if ( $packs ) : ?>
				<div aria-hidden="true" class="relative mx-auto w-56 sm:w-64">
					<div class="absolute -inset-8 rounded-full bg-brand/30 blur-3xl"></div>
					<div class="relative rounded-[2.5rem] border-[6px] border-ink bg-ink p-1.5 shadow-lift">
						<div class="overflow-hidden rounded-[2rem] bg-canvas">
							<div class="flex items-center justify-between bg-surface px-4 py-3">
								<?php $logo = priniti_site_config()['logo']; ?>
								<?php if ( $logo ) : ?>
									<img src="<?php echo esc_url( $logo['src'] ); ?>" alt="" class="h-5 w-auto">
								<?php else : ?>
									<span class="font-display text-xs font-extrabold text-brand">Priniti</span>
								<?php endif; ?>
								<?php priniti_the_icon( 'shopping-bag', 'size-4 text-ink' ); ?>
							</div>
							<div class="grid grid-cols-2 gap-2 p-2.5">
								<?php foreach ( $packs as $p ) : ?>
									<div class="rounded-xl bg-surface p-1.5 shadow-card">
										<div class="relative aspect-square rounded-lg bg-blush"><img src="<?php echo esc_url( $p['images'][0]['thumb'] ?: $p['images'][0]['src'] ); ?>" alt="" loading="lazy" class="absolute inset-0 size-full object-contain p-1.5"></div>
										<p class="mt-1 truncate text-[9px] font-semibold text-ink"><?php echo esc_html( $p['name'] ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="px-2.5 pb-3"><div class="flex h-8 items-center justify-center rounded-full bg-brand text-[10px] font-semibold text-white">Add to cart</div></div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}
