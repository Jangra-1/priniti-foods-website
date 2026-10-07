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
 * Hero stage
 * ---------------------------------------------------------------------- */

/**
 * The hero's right-hand composition: real Priniti packs on a podium in front of a red and navy arch,
 * with floating food accents, a rotating "Swad Mein No.1" stamp and product / count badges.
 * Built from product cutouts and vectors (no stock or generated imagery).
 */
function priniti_hero_stage( array $products, array $categories ): void {
	$packs = array_values( array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) ) );
	$total = array_sum( priniti_category_counts() );
	// Centre-out order: hero pack in the middle, then alternating sides.
	$order  = array( 2, 1, 3, 0, 4 );
	$layout = array(
		0 => array( 1, 22, 17, -14, 1, true ),
		1 => array( 14, 27, 13, -7, 2, false ),
		2 => array( 31, 38, 11, 0, 4, false ),
		3 => array( 59, 27, 13, 7, 2, false ),
		4 => array( 77, 22, 17, 14, 1, true ),
	);
	$slots = array_slice( $order, 0, min( 5, count( $packs ) ) );
	sort( $slots );
	?>
	<div class="relative mx-auto aspect-square w-full max-w-[30rem] xl:max-w-[34rem]">
		<div aria-hidden="true">
			<?php
			priniti_decor( 'ring', 'inset-[2%] size-[96%] text-brand/25' );
			priniti_decor( 'arch', 'bottom-[16%] left-[12%] h-[80%] w-[76%] text-brand' );
			priniti_decor( 'arch', 'bottom-[16%] left-[24%] h-[60%] w-[52%] text-navy' );
			priniti_decor( 'dots', 'right-[4%] top-[18%] size-20 text-white/30' );
			?>
			<div class="absolute bottom-[16%] left-[12%] h-[80%] w-[76%] overflow-hidden rounded-t-full">
				<div class="absolute -left-[20%] top-[8%] size-[60%] rounded-full bg-white/10 blur-2xl"></div>
			</div>
			<div class="absolute bottom-[7%] left-[4%] h-[15%] w-[92%] rounded-[50%] bg-cream shadow-[0_24px_40px_-18px_rgb(21_26_46/0.45)]"></div>
			<?php
			priniti_decor( 'chili', 'left-[2%] top-[30%] size-10 -rotate-12 text-brand motion-safe:animate-float sm:size-12' );
			priniti_decor( 'leaf', 'right-[1%] top-[44%] size-9 rotate-12 text-leaf motion-safe:animate-float-delayed sm:size-11' );
			priniti_decor( 'grain', 'left-[10%] top-[8%] h-14 w-7 -rotate-12 text-lime motion-safe:animate-float-delayed' );
			priniti_decor( 'sparkle', 'right-[20%] top-[4%] size-5 text-brand motion-safe:animate-float' );
			priniti_decor( 'sparkle', 'left-[6%] bottom-[30%] size-3 text-navy/60' );
			?>
		</div>

		<?php foreach ( $slots as $n => $slot ) : ?>
			<?php
			$p                                                   = $packs[ $n ];
			[ $left, $width, $bottom, $rotate, $z, $hide_mobile ] = $layout[ $slot ];
			?>
			<div aria-hidden="true" class="<?php echo esc_attr( priniti_cx( 'absolute aspect-[3/4] drop-shadow-[0_18px_20px_rgb(21_26_46/0.32)]', $hide_mobile ? 'hidden sm:block' : '' ) ); ?>" style="<?php echo esc_attr( "left:{$left}%;width:{$width}%;bottom:{$bottom}%;z-index:{$z};transform:rotate({$rotate}deg)" ); ?>">
				<img src="<?php echo esc_url( $p['images'][0]['src'] ); ?>" <?php echo ! empty( $p['images'][0]['srcset'] ) ? 'srcset="' . esc_attr( $p['images'][0]['srcset'] ) . '"' : ''; ?> sizes="(min-width:1024px) 14vw, 32vw" alt="" <?php echo 2 === $slot ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async" class="absolute inset-0 size-full object-contain">
			</div>
		<?php endforeach; ?>

		<?php priniti_decor_stamp( 'Swad Mein No.1 • Priniti Foods • ', 'right-[2%] top-[2%] z-10 size-20 sm:size-24' ); ?>

		<?php if ( isset( $packs[0] ) ) : ?>
			<a href="<?php echo esc_url( $packs[0]['href'] ); ?>" class="absolute -left-1 top-[14%] z-10 hidden max-w-[10rem] rounded-xl bg-surface px-3 py-2 shadow-lift transition-transform hover:-translate-y-0.5 sm:block lg:-left-4">
				<span class="block text-[10px] font-bold uppercase tracking-wide text-brand"><?php echo esc_html( $packs[0]['categoryName'] ); ?></span>
				<span class="mt-0.5 block font-display text-xs font-semibold leading-snug"><?php echo esc_html( $packs[0]['name'] ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $total ) : ?>
			<p class="absolute -right-1 bottom-[2%] z-10 flex items-center gap-2 rounded-2xl bg-surface px-3 py-2 shadow-lift sm:right-[4%]">
				<span class="flex size-8 items-center justify-center rounded-full bg-brand text-white"><?php priniti_the_icon( 'shopping-bag', 'size-4' ); ?></span>
				<span class="leading-tight"><span class="block font-display text-sm font-extrabold tabular-nums"><?php echo esc_html( (string) $total ); ?></span><span class="block text-[11px] text-ink-soft">snacks online</span></span>
			</p>
		<?php endif; ?>
		<p class="absolute -left-1 bottom-[2%] z-10 flex items-center gap-2 rounded-2xl bg-surface px-3 py-2 shadow-lift sm:left-[4%]">
			<span class="flex size-8 items-center justify-center rounded-full bg-navy text-white"><?php priniti_the_icon( 'layout-grid', 'size-4' ); ?></span>
			<span class="leading-tight"><span class="block font-display text-sm font-extrabold tabular-nums"><?php echo esc_html( (string) count( $categories ) ); ?></span><span class="block text-[11px] text-ink-soft">categories</span></span>
		</p>
	</div>
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
