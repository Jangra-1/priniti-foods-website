<?php
/**
 * Campaign components for the homepage: the running promo strip, the hero "stage" composition,
 * sample testimonials and the Instagram feed.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Promo strip
 * ---------------------------------------------------------------------- */

/**
 * Messages for the running strip above the homepage hero. Display only: no coupon or discount exists behind it,
 * so switch it off (return an empty array from the filter) or change the copy before launch unless a matching
 * WooCommerce coupon is set up.
 */
function priniti_promo_strip_messages(): array {
	return (array) apply_filters( 'priniti_promo_strip_messages', array( 'Get 10% off', 'Limited time offer', 'Shop Priniti Foods' ) );
}

function priniti_promo_strip(): void {
	$messages = priniti_promo_strip_messages();
	if ( ! $messages ) {
		return;
	}
	$run = static function () use ( $messages ): void {
		foreach ( array_merge( $messages, $messages ) as $m ) {
			echo '<span class="inline-flex items-center gap-4 px-4">' . esc_html( $m ) . '<span aria-hidden="true" class="text-white/60">&bull;</span></span>';
		}
	};
	?>
	<a href="<?php echo esc_url( priniti_url( '/shop' ) ); ?>" class="group block overflow-hidden bg-brand text-white" data-promo-strip>
		<span class="sr-only"><?php echo esc_html( implode( '. ', $messages ) . '.' ); ?></span>
		<span aria-hidden="true" class="flex h-9 w-max items-center whitespace-nowrap text-[12px] font-bold uppercase tracking-[0.14em] motion-safe:animate-marquee group-hover:[animation-play-state:paused] motion-reduce:w-full motion-reduce:justify-center sm:text-[13px]">
			<span class="flex shrink-0"><?php $run(); ?></span>
			<span class="flex shrink-0 motion-reduce:hidden"><?php $run(); ?></span>
		</span>
	</a>
	<?php
}

/* -------------------------------------------------------------------------
 * Hero slider
 * ---------------------------------------------------------------------- */

/**
 * The three homepage hero slides. Products are real catalog products (slugs); the CTA links to their category.
 * `background` is an optional image URL (for example an AI-generated lifestyle scene WITHOUT any packaging): it is
 * drawn behind the real pack cutouts, which always come from the official product images. Filterable.
 */
function priniti_hero_slides(): array {
	return (array) apply_filters(
		'priniti_hero_slides',
		array(
			array(
				'eyebrow'    => 'Indian traditional namkeen',
				'title'      => array( 'Every Bite,', 'Full of Happiness.' ),
				'copy'       => 'Bhujia, Aloo Bhujia, Bombay Mix and more: classic Priniti namkeen for every chai break.',
				'category'   => 'indian-traditional-namkeen',
				'cta'        => 'Shop Namkeen',
				'products'   => array( 'aloo-bhujia', 'bhujia', 'bombay-mix' ),
				'theme'      => array( 'bg' => 'from-cream via-blush to-brand-tint', 'arch' => 'text-brand', 'inner' => 'text-navy', 'accents' => array( 'chili', 'leaf', 'grain' ) ),
				'background' => null,
			),
			array(
				'eyebrow'    => 'Potato chips & CharChare',
				'title'      => array( 'Crunch Time,', 'Every Time.' ),
				'copy'       => 'Classic Salted, Cream \'n\' Onion and CharChare Mast Masala in bold Priniti flavours.',
				'category'   => 'potato-chips',
				'cta'        => 'Shop Chips',
				'products'   => array( 'chips-cream-n-onion', 'chips-classic-salted', 'charchare-mast-masala' ),
				'theme'      => array( 'bg' => 'from-lime-tint via-cream to-blush', 'arch' => 'text-navy', 'inner' => 'text-brand', 'accents' => array( 'burst', 'zigzag', 'sparkle' ) ),
				'background' => null,
			),
			array(
				'eyebrow'    => 'Cookies & donut cakes',
				'title'      => array( 'Tea-Time,', 'Baked by Priniti.' ),
				'copy'       => 'Jeera and Ajwain cookies, plus soft Choco Vanilla Donut Cakes for your tea-time and tiffin.',
				'category'   => 'cookies',
				'cta'        => 'Shop Cookies',
				'products'   => array( 'ajwain-cookies', 'jeera-cookies', 'choco-vanilla-donut-cake' ),
				'theme'      => array( 'bg' => 'from-navy-tint via-cream to-lime-tint', 'arch' => 'text-leaf', 'inner' => 'text-lime', 'accents' => array( 'cookie', 'grain', 'sparkle' ) ),
				'background' => null,
			),
		)
	);
}

/**
 * Homepage hero: three product slides. All slides share one grid cell, so the hero's height never jumps; slide 1 is
 * visible without JavaScript, and the other slides' images load only after the page has loaded (interactions.ts).
 */
function priniti_section_hero_slider(): void {
	$slides = array();
	foreach ( priniti_hero_slides() as $s ) {
		$packs = array_values( array_filter( array_map( 'priniti_get_product', $s['products'] ), static fn ( $p ) => $p && ! empty( $p['images'] ) ) );
		if ( ! $packs ) {
			continue;
		}
		$category = priniti_get_category( $s['category'] );
		$prices   = array();
		foreach ( $packs as $p ) {
			foreach ( $p['variants'] as $v ) {
				if ( priniti_is_purchasable( $v ) ) {
					$prices[] = (float) $v['price'];
				}
			}
		}
		$slides[] = $s + array(
			'packs' => array_slice( $packs, 0, 3 ),
			'href'  => $category['href'] ?? priniti_url( '/shop' ),
			'label' => $category['name'] ?? 'Products',
			'from'  => $prices ? min( $prices ) : null,
		);
	}
	if ( ! $slides ) {
		return;
	}
	$count = count( $slides );
	// [ left %, width %, bottom %, rotate deg, z ] for up to three packs: centre pack first.
	$layout = array( array( 30, 40, 13, 0, 3 ), array( 4, 32, 16, -10, 2 ), array( 64, 32, 16, 10, 2 ) );
	$colour = array(
		'chili'   => 'text-brand',
		'leaf'    => 'text-leaf',
		'grain'   => 'text-[#c9a23a]',
		'burst'   => 'text-[#f5b301]',
		'zigzag'  => 'text-brand/60',
		'sparkle' => 'text-brand',
		'cookie'  => 'text-[#d9a25f]',
	);
	$spots  = array( 'left-[2%] top-[28%] size-10 -rotate-12 motion-safe:animate-float', 'right-[2%] top-[40%] size-11 rotate-12 motion-safe:animate-float-delayed', 'left-[12%] top-[6%] size-8 motion-safe:animate-float-delayed' );
	?>
	<section aria-roledescription="carousel" aria-label="Featured Priniti products" class="relative touch-pan-y" data-hero-slider data-interval="3000">
		<div class="grid">
			<?php foreach ( $slides as $i => $s ) : ?>
				<?php
				$first   = 0 === $i;
				$heading = $first ? 'h1' : 'h2';
				$img     = static function ( array $image, string $sizes, bool $eager, bool $priority ) use ( $first ): string {
					$src    = esc_url( $image['src'] );
					$srcset = ! empty( $image['srcset'] ) ? esc_attr( $image['srcset'] ) : '';
					// Slides 2 and 3: real URLs in data-* attributes, swapped in by interactions.ts after load.
					return $first
						? sprintf( '<img src="%s"%s sizes="%s" alt="" %s decoding="async" class="absolute inset-0 size-full object-contain">', $src, $srcset ? ' srcset="' . $srcset . '"' : '', esc_attr( $sizes ), $priority ? 'fetchpriority="high"' : ( $eager ? '' : 'loading="lazy"' ) )
						: sprintf( '<img data-hero-src="%s"%s sizes="%s" alt="" decoding="async" class="absolute inset-0 size-full object-contain">', $src, $srcset ? ' data-hero-srcset="' . $srcset . '"' : '', esc_attr( $sizes ) );
				};
				?>
				<div role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%d of %d: %s', $i + 1, $count, $s['label'] ) ); ?>" data-hero-slide class="<?php echo esc_attr( priniti_cx( 'relative overflow-hidden bg-linear-to-br transition-opacity duration-700 ease-out [grid-area:1/1]', $s['theme']['bg'], $first ? 'opacity-100' : 'pointer-events-none opacity-0' ) ); ?>" <?php echo $first ? '' : 'aria-hidden="true" inert'; ?>>
					<?php if ( ! empty( $s['background'] ) ) : ?>
						<img <?php echo $first ? 'src' : 'data-hero-src'; ?>="<?php echo esc_url( $s['background'] ); ?>" alt="" decoding="async" class="absolute inset-0 size-full object-cover opacity-90">
						<div aria-hidden="true" class="absolute inset-0 bg-linear-to-r from-cream/95 via-cream/70 to-transparent"></div>
					<?php endif; ?>
					<?php
					priniti_decor( 'dots', 'left-[38%] top-8 hidden size-28 text-brand/15 lg:block' );
					priniti_decor( 'sparkle', 'left-[46%] top-[22%] hidden size-5 text-brand/60 lg:block' );
					priniti_decor( 'wave', 'inset-x-0 bottom-0 h-6 w-full text-surface sm:h-8' );
					?>
					<?php priniti_container_open( 'relative grid items-center gap-4 pb-12 pt-8 sm:pb-14 sm:pt-10 lg:grid-cols-[1fr_1.05fr] lg:gap-8 lg:py-12' ); ?>
						<div class="max-w-xl">
							<p class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3.5 py-1.5 text-[13px] font-semibold shadow-card">
								<?php priniti_the_icon( 'sparkles', 'size-4 text-brand' ); ?>
								<?php echo esc_html( $s['eyebrow'] ); ?>
							</p>
							<<?php echo esc_html( $heading ); ?> <?php echo $first ? 'id="hero-heading"' : ''; ?> class="mt-4 font-display text-[2.25rem] font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-[3.4rem] xl:text-[3.9rem]">
								<span class="block"><?php echo esc_html( $s['title'][0] ); ?></span>
								<span class="block text-brand"><?php echo esc_html( $s['title'][1] ); ?></span>
							</<?php echo esc_html( $heading ); ?>>
							<p class="mt-3 max-w-md text-base leading-relaxed text-ink-soft sm:text-lg"><?php echo esc_html( $s['copy'] ); ?></p>
							<?php if ( null !== $s['from'] ) : ?>
								<p class="mt-3 text-sm font-semibold text-ink"><?php echo esc_html( 'Starting at ' . priniti_format_inr( $s['from'] ) ); ?></p>
							<?php endif; ?>
							<div class="mt-5 flex flex-col gap-3 sm:flex-row">
								<?php
								priniti_button_link( $s['href'], priniti_icon( 'shopping-cart', 'size-5' ) . esc_html( $s['cta'] ?? 'Shop ' . $s['label'] ) . priniti_icon( 'arrow-right', 'size-4' ), 'primary', 'md', 'h-12 px-6 text-[15px]' );
								priniti_button_link( priniti_url( '/shop' ), 'Explore Products', 'outline', 'md', 'h-12 px-6 text-[15px]' );
								?>
							</div>
						</div>

						<div class="relative mx-auto aspect-[5/4] w-full max-w-[26rem] sm:max-w-[30rem] lg:max-w-[34rem]">
							<div aria-hidden="true">
								<?php
								priniti_decor( 'ring', 'inset-[4%] size-[92%] text-brand/20' );
								priniti_decor( 'arch', priniti_cx( 'bottom-[16%] left-[16%] h-[80%] w-[68%]', $s['theme']['arch'] ) );
								priniti_decor( 'arch', priniti_cx( 'bottom-[16%] left-[28%] h-[58%] w-[44%] opacity-90', $s['theme']['inner'] ) );
								?>
								<div class="absolute bottom-[8%] left-[4%] h-[15%] w-[92%] rounded-[50%] bg-cream shadow-[0_24px_40px_-18px_rgb(21_26_46/0.45)]"></div>
								<?php
								foreach ( $s['theme']['accents'] as $n => $kind ) {
									priniti_decor( $kind, priniti_cx( $spots[ $n ] ?? '', in_array( $kind, array( 'zigzag' ), true ) ? 'h-5 w-16' : '', 'grain' === $kind ? 'h-14 w-7' : '', $colour[ $kind ] ?? 'text-brand' ) );
								}
								?>
							</div>
							<?php foreach ( array_slice( $s['packs'], 0, 3 ) as $n => $p ) : ?>
								<?php [ $left, $width, $bottom, $rotate, $z ] = $layout[ $n ]; ?>
								<a href="<?php echo esc_url( $p['href'] ); ?>" <?php echo $first ? '' : 'tabindex="-1"'; ?> class="group absolute block aspect-[3/4] drop-shadow-[0_18px_20px_rgb(21_26_46/0.32)] transition-transform duration-300 hover:-translate-y-1" style="<?php echo esc_attr( "left:{$left}%;width:{$width}%;bottom:{$bottom}%;z-index:{$z};transform:rotate({$rotate}deg)" ); ?>">
									<?php echo $img( $p['images'][0], '(min-width:1024px) 15vw, 34vw', $first, $first && 0 === $n ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $img. ?>
									<span class="sr-only"><?php echo esc_html( $p['name'] ); ?></span>
								</a>
							<?php endforeach; ?>
							<?php priniti_decor_stamp( 'Swad Mein No.1 • Priniti Foods • ', 'right-[1%] top-[1%] z-10 size-16 sm:size-20' ); ?>
						</div>
					<?php priniti_container_close(); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $count > 1 ) : ?>
			<div class="pointer-events-none absolute inset-x-0 bottom-7 z-10 sm:bottom-9">
				<?php priniti_container_open( 'flex items-center justify-center gap-3 lg:justify-start' ); ?>
					<button type="button" data-hero-prev aria-label="Previous slide" class="pointer-events-auto flex size-9 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card ring-1 ring-line/70 transition hover:bg-ink hover:text-white"><?php priniti_the_icon( 'chevron-left', 'size-4' ); ?></button>
					<div class="pointer-events-auto flex items-center gap-1.5" role="group" aria-label="Choose slide">
						<?php foreach ( $slides as $i => $s ) : ?>
							<button type="button" data-hero-dot="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Show slide %d: %s', $i + 1, $s['label'] ) ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>" class="<?php echo esc_attr( priniti_cx( 'h-2 rounded-full transition-all', 0 === $i ? 'w-6 bg-brand' : 'w-2 bg-ink/25 hover:bg-ink/50' ) ); ?>"></button>
						<?php endforeach; ?>
					</div>
					<button type="button" data-hero-next aria-label="Next slide" class="pointer-events-auto flex size-9 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card ring-1 ring-line/70 transition hover:bg-ink hover:text-white"><?php priniti_the_icon( 'chevron-right', 'size-4' ); ?></button>
				<?php priniti_container_close(); ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/* -------------------------------------------------------------------------
 * Testimonials
 * ---------------------------------------------------------------------- */

/**
 * Sample testimonials shown while the store has no real reviews. They are placeholders, labelled as such in the UI,
 * with no product names, purchase claims or verified badges. Replace or remove through the filter; real WooCommerce
 * reviews (priniti_featured_reviews()) take over automatically as soon as any exist.
 */
function priniti_sample_reviews(): array {
	return (array) apply_filters(
		'priniti_sample_reviews',
		array(
			array( 'author' => 'Sample Customer', 'rating' => 5, 'body' => 'Great taste and neat packaging. Looking forward to ordering again.' ),
			array( 'author' => 'Demo Reviewer', 'rating' => 5, 'body' => 'A good mix of namkeen and sweets for family tea-time.' ),
			array( 'author' => 'Sample Customer', 'rating' => 4, 'body' => 'Easy to find my favourite snacks and the packs look lovely.' ),
			array( 'author' => 'Demo Reviewer', 'rating' => 5, 'body' => 'Perfect for sharing with friends on movie night.' ),
		)
	);
}

/**
 * Customer reviews section: real reviews when they exist, otherwise clearly labelled sample cards.
 *
 * @param array $reviews Real reviews from priniti_featured_reviews().
 */
function priniti_section_testimonials( array $reviews ): void {
	$sample = ! $reviews;
	$items  = $sample ? priniti_sample_reviews() : $reviews;
	if ( ! $items ) {
		return;
	}
	?>
	<section aria-labelledby="reviews-heading" class="relative overflow-hidden bg-surface py-10 lg:py-14" data-reveal>
		<?php
		priniti_decor( 'blob', '-left-24 top-10 size-72 text-blush' );
		priniti_decor( 'dots', 'right-[6%] top-8 hidden size-24 text-brand/10 md:block' );
		?>
		<?php priniti_container_open( 'relative' ); ?>
			<?php
			priniti_section_heading(
				array(
					'id'          => 'reviews-heading',
					'eyebrow'     => $sample ? 'Sample reviews' : 'Customer love',
					'title'       => $sample ? 'Customer Stories, Coming Soon' : 'What Our Customers Say',
					'description' => $sample ? 'These are sample reviews that show how this section will look. Reviews from real customers will replace them once orders begin.' : '',
					'align'       => 'center',
					'class'       => 'mb-6 lg:mb-8',
				)
			);
			?>
			<ul role="list" class="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-2 md:gap-4 md:overflow-visible md:px-0 lg:grid-cols-4">
				<?php foreach ( $items as $r ) : ?>
					<li class="w-[82%] shrink-0 snap-start sm:w-[55%] md:w-auto">
						<figure class="relative flex h-full flex-col gap-4 rounded-[1.5rem] bg-surface p-6 shadow-card ring-1 ring-line/70">
							<span aria-hidden="true" class="absolute bottom-16 right-5 font-display text-7xl leading-none text-brand/10">&rdquo;</span>
							<div class="flex items-center justify-between gap-2">
								<?php priniti_rating_stars( (float) $r['rating'], null, 'md' ); ?>
								<?php if ( $sample ) : ?>
									<span class="<?php echo esc_attr( priniti_badge_classes( 'neutral', 'px-2 py-1 text-[10px] uppercase tracking-wide' ) ); ?>">Sample</span>
								<?php endif; ?>
							</div>
							<blockquote class="flex-1 leading-relaxed text-ink"><p><?php echo esc_html( $r['body'] ); ?></p></blockquote>
							<figcaption class="flex items-center gap-3 border-t border-line pt-4">
								<span aria-hidden="true" class="flex size-10 items-center justify-center rounded-full bg-brand-tint font-display text-sm font-bold text-brand"><?php echo esc_html( mb_substr( $r['author'], 0, 1 ) ); ?></span>
								<span class="text-sm">
									<span class="block font-semibold"><?php echo esc_html( $r['author'] ); ?></span>
									<span class="block text-ink-soft"><?php echo esc_html( $sample ? 'Sample review' : ( $r['productName'] ?? '' ) ); ?></span>
								</span>
							</figcaption>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}

/* -------------------------------------------------------------------------
 * Instagram feed
 * ---------------------------------------------------------------------- */

/**
 * Recent Instagram posts through the official Instagram API (Instagram API with Instagram login, graph.instagram.com).
 *
 * Connect it by defining a long-lived access token for the brand's professional account in wp-config.php:
 *     define( 'PRINITI_INSTAGRAM_TOKEN', '...' );
 * (or return one from the `priniti_instagram_token` filter). Long-lived tokens last 60 days; refresh them with
 * GET https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=... .
 * The token is never printed or exposed to the browser. Responses are cached for an hour; failures for 15 minutes.
 *
 * @return array<int, array{id:string, image:string, caption:string, permalink:string, type:string}>
 */
function priniti_instagram_posts( int $limit = 6 ): array {
	$pre = apply_filters( 'priniti_pre_instagram_posts', null, $limit );
	if ( is_array( $pre ) ) {
		return array_slice( $pre, 0, $limit );
	}
	$token = (string) apply_filters( 'priniti_instagram_token', defined( 'PRINITI_INSTAGRAM_TOKEN' ) ? PRINITI_INSTAGRAM_TOKEN : '' );
	if ( '' === $token ) {
		return array();
	}
	$cache_key = 'priniti_instagram_feed_v1';
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return array_slice( $cached, 0, $limit );
	}
	$response = wp_remote_get(
		add_query_arg(
			array(
				'fields'       => 'id,caption,media_type,media_url,thumbnail_url,permalink',
				'limit'        => 12,
				'access_token' => $token,
			),
			'https://graph.instagram.com/me/media'
		),
		array( 'timeout' => 6 )
	);
	$posts = array();
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		foreach ( (array) ( $body['data'] ?? array() ) as $m ) {
			$image = 'VIDEO' === ( $m['media_type'] ?? '' ) ? ( $m['thumbnail_url'] ?? '' ) : ( $m['media_url'] ?? '' );
			if ( ! $image || empty( $m['permalink'] ) ) {
				continue;
			}
			$posts[] = array(
				'id'        => (string) $m['id'],
				'image'     => (string) $image,
				'caption'   => wp_strip_all_tags( (string) ( $m['caption'] ?? '' ) ),
				'permalink' => (string) $m['permalink'],
				'type'      => (string) ( $m['media_type'] ?? 'IMAGE' ),
			);
		}
	}
	set_transient( $cache_key, $posts, $posts ? HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS );
	return array_slice( $posts, 0, $limit );
}

/** "Follow the snacking": the live Instagram feed, or a clearly marked placeholder until it is connected. */
function priniti_section_instagram(): void {
	$posts   = priniti_instagram_posts( 6 );
	$profile = null;
	foreach ( priniti_site_config()['social'] as $s ) {
		if ( 'instagram' === $s['icon'] && ! empty( $s['href'] ) ) {
			$profile = $s['href'];
		}
	}
	$tiles = array( 'from-brand-tint to-blush', 'from-navy-tint to-surface', 'from-lime-tint to-surface', 'from-blush to-brand-tint', 'from-leaf-tint to-surface', 'from-navy-tint to-blush' );
	?>
	<section aria-labelledby="social-heading" class="bg-surface py-10 lg:py-14" data-reveal>
		<?php priniti_container_open(); ?>
			<div class="mb-6 flex flex-col items-center gap-3 text-center lg:mb-8">
				<span class="flex size-12 items-center justify-center rounded-2xl bg-linear-to-br from-[#f58529] via-[#dd2a7b] to-[#8134af] text-white shadow-soft"><?php priniti_the_icon( 'instagram', 'size-6' ); ?></span>
				<h2 id="social-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Follow the snacking</h2>
				<p class="text-sm text-ink-soft sm:text-base">See what&rsquo;s new from Priniti Foods.</p>
			</div>

			<?php if ( $posts ) : ?>
				<ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
					<?php foreach ( $posts as $post ) : ?>
						<li>
							<a href="<?php echo esc_url( $post['permalink'] ); ?>" target="_blank" rel="noopener noreferrer" class="group relative block aspect-square overflow-hidden rounded-2xl bg-canvas ring-1 ring-line/70">
								<img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['caption'] ? wp_trim_words( $post['caption'], 14 ) : 'Priniti Foods on Instagram' ); ?>" loading="lazy" decoding="async" class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105">
								<span class="absolute inset-0 flex items-center justify-center bg-ink/0 text-white opacity-0 transition group-hover:bg-ink/40 group-hover:opacity-100"><?php priniti_the_icon( 'instagram', 'size-7' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="relative">
					<ul role="list" aria-hidden="true" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
						<?php foreach ( $tiles as $i => $tone ) : ?>
							<li class="<?php echo esc_attr( $i >= 4 ? 'hidden sm:block' : '' ); ?>">
								<div class="<?php echo esc_attr( priniti_cx( 'relative flex aspect-square flex-col justify-between overflow-hidden rounded-2xl bg-linear-to-br p-4 ring-1 ring-line/60', $tone ) ); ?>">
									<?php priniti_decor( 0 === $i % 2 ? 'blob' : 'ring', '-right-8 -top-8 size-28 text-surface/70' ); ?>
									<span class="relative flex size-8 items-center justify-center rounded-full bg-surface/80 text-ink-soft"><?php priniti_the_icon( 'instagram', 'size-4' ); ?></span>
									<span class="relative flex flex-col gap-1.5">
										<span class="h-2 w-3/4 rounded-full bg-surface/90"></span>
										<span class="h-2 w-1/2 rounded-full bg-surface/70"></span>
									</span>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="absolute inset-0 flex items-center justify-center p-4">
						<div class="flex max-w-sm flex-col items-center gap-2 rounded-2xl bg-surface/95 px-6 py-5 text-center shadow-lift ring-1 ring-line/70 backdrop-blur">
							<span class="<?php echo esc_attr( priniti_badge_classes( 'tint', 'px-2.5 py-1 text-[10px] uppercase tracking-wide' ) ); ?>">Feed coming soon</span>
							<p class="font-display text-base font-bold">Our Instagram feed will appear here</p>
							<p class="text-sm text-ink-soft">Recent posts from Priniti Foods will show in this space once the official account is connected.</p>
							<?php if ( $profile ) : ?>
								<?php priniti_button_link( $profile, priniti_icon( 'instagram', 'size-4' ) . 'Follow on Instagram', 'primary', 'md', 'mt-1', array( 'target' => '_blank', 'rel' => 'noopener noreferrer' ) ); ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $posts && $profile ) : ?>
				<div class="mt-6 flex justify-center">
					<?php priniti_button_link( $profile, priniti_icon( 'instagram', 'size-4' ) . 'Follow on Instagram', 'outline', 'md', '', array( 'target' => '_blank', 'rel' => 'noopener noreferrer' ) ); ?>
				</div>
			<?php endif; ?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
}
