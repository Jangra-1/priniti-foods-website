<?php
/**
 * Commerce components (reference/nextjs/components/commerce/* and sections/PackFan, catalog/CategoryCard,
 * catalog/CategoryBanner). Interactive parts are plain markup with data-* hooks wired by
 * assets/src/js/interactions.ts (add to cart, wishlist, quick view), like the reference's client islands.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * ProductImage: fills its (relatively positioned) parent; a labelled placeholder when there is no image.
 *
 * @param array|null $image { src, alt, srcset? }.
 */
function priniti_product_image( ?array $image, string $name, string $sizes, string $class = '', bool $priority = false, string $placeholder_class = '' ): void {
	if ( ! $image ) {
		?>
		<div role="img" aria-label="<?php echo esc_attr( sprintf( '%s: product image coming soon', $name ) ); ?>" class="<?php echo esc_attr( priniti_cx( 'flex size-full flex-col items-center justify-center gap-1.5 bg-brand-tint p-2 text-center text-brand', $placeholder_class ) ); ?>">
			<?php priniti_the_icon( 'package', 'size-8 [stroke-width:1.5]' ); ?>
			<span class="text-xs font-medium leading-tight text-ink-soft"><?php esc_html_e( 'Image pending', 'priniti' ); ?></span>
		</div>
		<?php
		return;
	}
	printf(
		'<img src="%s"%s sizes="%s" alt="%s" %s decoding="async" class="%s">',
		esc_url( $image['src'] ),
		! empty( $image['srcset'] ) ? ' srcset="' . esc_attr( $image['srcset'] ) . '"' : '',
		esc_attr( $sizes ),
		esc_attr( $image['alt'] ?? '' ),
		$priority ? 'fetchpriority="high"' : 'loading="lazy"',
		esc_attr( priniti_cx( 'absolute inset-0 size-full object-contain p-4 transition-transform duration-500 ease-out group-hover:scale-105', $class ) )
	);
}

/**
 * PriceDisplay (TEST-price badge removed: WooCommerce prices are real).
 */
function priniti_price_display( float $mrp, float $price, string $size = 'md', bool $show_discount = true, string $class = '' ): void {
	$sizes = array( 'sm' => 'text-base', 'md' => 'text-xl', 'lg' => 'text-3xl' );
	$off   = ( $mrp > 0 && $price < $mrp ) ? (int) round( ( $mrp - $price ) / $mrp * 100 ) : 0;
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'flex flex-wrap items-baseline gap-x-2 gap-y-0.5', $class ) ); ?>">
		<span class="<?php echo esc_attr( priniti_cx( 'font-display font-bold tabular-nums text-ink', $sizes[ $size ] ?? $sizes['md'] ) ); ?>"><span class="sr-only">Price </span><?php echo esc_html( priniti_format_inr( $price ) ); ?></span>
		<?php if ( $off > 0 ) : ?>
			<span class="text-sm tabular-nums text-ink-soft"><span class="sr-only">MRP </span><del><?php echo esc_html( priniti_format_inr( $mrp ) ); ?></del></span>
			<?php if ( $show_discount ) : ?>
				<span class="text-sm font-semibold text-leaf"><?php echo esc_html( $off . '% off' ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/** RatingStars. */
function priniti_rating_stars( float $rating, ?int $count = null, string $size = 'sm', string $class = '' ): void {
	$pct = max( 0, min( 5, $rating ) ) / 5 * 100;
	$dim = 'sm' === $size ? 'size-3.5' : 'size-5';
	$row = str_repeat( priniti_icon( 'star', "shrink-0 fill-current {$dim}" ), 5 );
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'inline-flex items-center gap-1.5', $class ) ); ?>">
		<span role="img" aria-label="<?php echo esc_attr( sprintf( 'Rated %.1f out of 5', $rating ) ); ?>" class="relative inline-flex">
			<span class="flex text-line"><?php echo $row; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="absolute inset-y-0 left-0 flex overflow-hidden text-navy" style="width: <?php echo esc_attr( (string) $pct ); ?>%"><?php echo $row; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>
		<span class="text-xs tabular-nums text-ink-soft"><?php echo esc_html( sprintf( '%.1f', $rating ) . ( null !== $count ? " ({$count})" : '' ) ); ?></span>
	</div>
	<?php
}

/**
 * Data the browser needs to add a variant to the WooCommerce cart (Store API add-item).
 */
function priniti_cart_payload( array $product, array $variant, int $quantity = 1 ): string {
	return (string) wp_json_encode(
		array(
			'id'        => (int) $variant['id'],
			'quantity'  => $quantity,
			'variation' => $variant['attributes'],
			'name'      => $product['name'],
		)
	);
}

/** Public product data for the quick-view dialog and the product-page purchase panel. */
function priniti_product_payload( array $product ): array {
	return array(
		'id'           => $product['id'],
		'name'         => $product['name'],
		'href'         => $product['href'],
		'categoryName' => $product['categoryName'],
		'categoryHref' => $product['categoryHref'],
		'image'        => $product['images'][0] ?? null,
		'variants'     => array_map(
			static fn ( array $v ): array => array(
				'id'          => (int) $v['id'],
				'label'       => (string) $v['label'],
				'source'      => (string) $v['source'],
				'price'       => $v['price'],
				'mrp'         => $v['mrp'],
				'attributes'  => $v['attributes'],
				'purchasable' => (bool) $v['purchasable'],
			),
			$product['variants']
		),
	);
}

/** WishlistButton (state lives in the browser, like the reference). */
function priniti_wishlist_button( array $product, string $class = '' ): void {
	?>
	<button type="button" data-priniti-wishlist="<?php echo esc_attr( (string) $product['id'] ); ?>" data-name="<?php echo esc_attr( $product['name'] ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( 'Add ' . $product['name'] . ' to wishlist' ); ?>" class="<?php echo esc_attr( priniti_cx( 'flex size-10 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card transition duration-200 hover:scale-105 active:scale-95', $class ) ); ?>">
		<?php priniti_the_icon( 'heart', 'size-5 transition-colors' ); ?>
	</button>
	<?php
}

/** The round add-to-cart icon on product cards (AddToCartButton iconOnly). */
function priniti_card_add_button( array $product, ?array $variant ): void {
	$styles = 'inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand transition duration-200 hover:bg-brand hover:text-white active:scale-95 disabled:pointer-events-none disabled:opacity-50';
	if ( ! $variant ) {
		?>
		<button type="button" disabled aria-label="<?php echo esc_attr( $product['name'] . ': price coming soon' ); ?>" class="<?php echo esc_attr( $styles ); ?>"><?php priniti_the_icon( 'shopping-cart', 'size-4' ); ?></button>
		<?php
		return;
	}
	?>
	<button type="button" data-priniti-add="<?php echo esc_attr( priniti_cart_payload( $product, $variant ) ); ?>" aria-label="<?php echo esc_attr( 'Add ' . $product['name'] . ' to cart' ); ?>" class="<?php echo esc_attr( $styles ); ?>">
		<span data-when="idle"><?php priniti_the_icon( 'shopping-cart', 'size-4' ); ?></span>
		<span data-when="added" hidden><?php priniti_the_icon( 'check', 'size-4' ); ?></span>
	</button>
	<?php
}

/**
 * ProductCard (server-rendered; interactivity via data hooks).
 */
function priniti_product_card( array $product, string $class = '' ): void {
	$purchasable = priniti_purchasable_variant( $product );
	$off         = priniti_discount( $purchasable );
	$packs       = priniti_pack_labels( $product );
	?>
	<article class="<?php echo esc_attr( priniti_cx( 'group relative flex h-full flex-col overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-0.5 hover:shadow-lift has-[.card-link:focus-visible]:ring-2 has-[.card-link:focus-visible]:ring-brand', $class ) ); ?>">
		<div class="relative aspect-square overflow-hidden bg-linear-to-b from-blush via-surface to-surface sm:aspect-[5/4]">
			<span aria-hidden="true" class="absolute left-1/2 top-[54%] size-[70%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-tint/60 transition-transform duration-500 group-hover:scale-110"></span>
			<?php priniti_product_image( $product['images'][0] ?? null, $product['name'], '(min-width:1280px) 22vw, (min-width:768px) 30vw, 46vw', 'p-3 drop-shadow-[0_10px_14px_rgb(21_26_46/0.14)] sm:p-4' ); ?>
			<div class="absolute left-2 top-2 flex flex-col items-start gap-1">
				<?php foreach ( $product['badges'] as $badge ) : ?>
					<span class="<?php echo esc_attr( priniti_badge_classes( 'new' === $badge ? 'leaf' : 'navy', 'px-2 py-0.5 text-[10px] shadow-card' ) ); ?>"><?php echo esc_html( PRINITI_BADGE_LABELS[ $badge ] ?? $badge ); ?></span>
				<?php endforeach; ?>
				<?php if ( $off > 0 ) : ?>
					<span class="<?php echo esc_attr( priniti_badge_classes( 'leaf', 'px-2 py-0.5 text-[10px]' ) ); ?>"><?php echo esc_html( $off . '% off' ); ?></span>
				<?php endif; ?>
			</div>
			<?php priniti_wishlist_button( $product, 'absolute right-2 top-2 z-10 size-8' ); ?>
			<button type="button" data-priniti-quickview="<?php echo esc_attr( (string) wp_json_encode( priniti_product_payload( $product ) ) ); ?>" aria-label="<?php echo esc_attr( 'Quick view: ' . $product['name'] ); ?>" class="absolute inset-x-2 bottom-2 z-10 hidden h-8 items-center justify-center gap-1.5 rounded-full bg-surface/95 text-xs font-semibold shadow-card transition duration-200 hover:bg-ink hover:text-white lg:inline-flex lg:translate-y-1 lg:opacity-0 lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:opacity-100">
				<?php priniti_the_icon( 'eye', 'size-3.5' ); ?>
				<?php esc_html_e( 'Quick view', 'priniti' ); ?>
			</button>
		</div>

		<div class="flex flex-1 flex-col gap-0.5 p-3">
			<?php if ( $product['categoryHref'] ) : ?>
				<a href="<?php echo esc_url( $product['categoryHref'] ); ?>" class="relative z-10 line-clamp-1 w-fit text-[10px] font-bold uppercase tracking-wide text-brand transition-colors hover:text-brand-dark"><?php echo esc_html( $product['categoryName'] ); ?></a>
			<?php endif; ?>
			<p class="text-[11px] leading-tight text-ink-soft"><?php echo esc_html( $packs ? implode( ' · ', $packs ) : 'Pack size to be confirmed' ); ?></p>

			<h3 class="line-clamp-2 font-display text-sm font-semibold leading-snug sm:text-[15px]">
				<a href="<?php echo esc_url( $product['href'] ); ?>" class="card-link outline-none after:absolute after:inset-0"><?php echo esc_html( $product['name'] ); ?></a>
			</h3>

			<?php
			if ( $product['reviewCount'] && null !== $product['rating'] ) {
				priniti_rating_stars( (float) $product['rating'], (int) $product['reviewCount'], 'sm', 'mt-0.5' );
			}
			?>

			<div class="relative z-10 mt-auto flex items-end justify-between gap-2 pt-2">
				<?php if ( $purchasable ) : ?>
					<?php priniti_price_display( (float) ( $purchasable['mrp'] ?? $purchasable['price'] ), (float) $purchasable['price'], 'sm', false ); ?>
					<?php priniti_card_add_button( $product, $purchasable ); ?>
				<?php else : ?>
					<p class="text-xs font-semibold text-ink-soft"><?php esc_html_e( 'Price coming soon', 'priniti' ); ?></p>
					<a href="<?php echo esc_url( $product['href'] ); ?>" aria-label="<?php echo esc_attr( 'View details: ' . $product['name'] ); ?>" class="inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand transition duration-200 hover:bg-brand hover:text-white">
						<?php priniti_the_icon( 'arrow-right', 'size-4' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</article>
	<?php
}

/** ProductGrid. */
function priniti_product_grid( array $products, string $class = '', string $empty_title = 'No products found', string $empty_text = '' ): void {
	if ( ! $products ) {
		?>
		<div class="rounded-card border border-dashed border-line px-6 py-14 text-center">
			<p class="font-display text-lg font-semibold"><?php echo esc_html( $empty_title ); ?></p>
			<?php if ( $empty_text ) : ?>
				<p class="mt-1 text-ink-soft"><?php echo esc_html( $empty_text ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return;
	}
	?>
	<ul role="list" class="<?php echo esc_attr( priniti_cx( 'grid grid-cols-2 gap-3 lg:gap-4 md:grid-cols-3 xl:grid-cols-4', $class ) ); ?>">
		<?php foreach ( $products as $p ) : ?>
			<li><?php priniti_product_card( $p ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * ProductCarousel. Arrow state and scrolling live in interactions.ts ([data-priniti-carousel]).
 *
 * @param callable $header Echoes the heading markup.
 */
function priniti_product_carousel( string $label, callable $header, array $products ): void {
	?>
	<section aria-roledescription="carousel" aria-label="<?php echo esc_attr( $label ); ?>" class="relative" data-priniti-carousel>
		<div class="mb-4 flex items-end justify-between gap-4 lg:mb-5">
			<div><?php $header(); ?></div>
			<div class="hidden shrink-0 gap-2 md:flex">
				<button type="button" data-carousel-prev aria-label="<?php esc_attr_e( 'Previous products', 'priniti' ); ?>" disabled class="<?php echo esc_attr( priniti_icon_button_classes( 'size-10', 'border border-line bg-surface shadow-card hover:bg-surface disabled:opacity-35' ) ); ?>"><?php priniti_the_icon( 'chevron-left', 'size-5' ); ?></button>
				<button type="button" data-carousel-next aria-label="<?php esc_attr_e( 'Next products', 'priniti' ); ?>" class="<?php echo esc_attr( priniti_icon_button_classes( 'size-10', 'bg-brand text-white shadow-card hover:bg-brand-dark disabled:opacity-35' ) ); ?>"><?php priniti_the_icon( 'chevron-right', 'size-5' ); ?></button>
			</div>
		</div>
		<ul role="list" data-carousel-track class="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth px-4 pb-2 sm:-mx-6 sm:px-6 lg:mx-0 lg:gap-4 lg:px-0">
			<?php foreach ( $products as $p ) : ?>
				<li class="w-[46%] shrink-0 snap-start sm:w-[32%] md:w-[24%] lg:w-[19%]"><?php priniti_product_card( $p ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * PackFan: a composition of REAL pack images (decorative: empty alt; names are in the surrounding content).
 */
function priniti_pack_fan( array $products, string $class = '', bool $priority = false ): void {
	$layouts = array(
		3 => array(
			array( 4, 30, -8, 1, false ),
			array( 33, 36, 0, 3, false ),
			array( 66, 30, 8, 1, false ),
		),
		5 => array(
			array( 0, 24, -12, 1, true ),
			array( 13, 27, -6, 2, false ),
			array( 33, 34, 0, 4, false ),
			array( 60, 27, 6, 2, false ),
			array( 76, 24, 12, 1, true ),
		),
	);
	$items  = array_values( array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) ) );
	$layout = $layouts[ 5 === count( $items ) ? 5 : 3 ];
	$shown  = array_slice( $items, 0, count( $layout ) );
	$centre = (int) floor( count( $shown ) / 2 );
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'relative size-full', $class ) ); ?>">
		<?php
		foreach ( $shown as $i => $p ) :
			[ $left, $width, $rotate, $z, $hide_mobile ] = $layout[ $i ];
			?>
			<div class="<?php echo esc_attr( priniti_cx( 'absolute bottom-[8%] aspect-[3/4] drop-shadow-xl', $hide_mobile ? 'hidden sm:block' : '' ) ); ?>" style="<?php echo esc_attr( "left:{$left}%;width:{$width}%;z-index:{$z};transform:rotate({$rotate}deg)" ); ?>">
				<img src="<?php echo esc_url( $p['images'][0]['src'] ); ?>" <?php echo ! empty( $p['images'][0]['srcset'] ) ? 'srcset="' . esc_attr( $p['images'][0]['srcset'] ) . '"' : ''; ?> sizes="(min-width:1024px) 16vw, (min-width:640px) 22vw, 30vw" alt="" <?php echo $priority && $i === $centre ? 'fetchpriority="high"' : 'loading="lazy"'; ?> decoding="async" class="absolute inset-0 size-full object-contain">
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/** CategoryCard. */
function priniti_category_card( array $category, int $index, ?int $count = null ): void {
	?>
	<a href="<?php echo esc_url( $category['href'] ); ?>" class="group relative flex h-full flex-col overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-1 hover:shadow-lift hover:ring-brand/50">
		<div class="<?php echo esc_attr( priniti_cx( 'relative aspect-square overflow-hidden', priniti_tint_for_index( $index ) ) ); ?>">
			<?php priniti_product_image( $category['image'], $category['name'], '(min-width:1024px) 18vw, 40vw', 'bg-transparent p-2.5 text-ink/50', false, 'bg-transparent p-2.5 text-ink/50' ); ?>
			<span aria-hidden="true" class="absolute right-1.5 top-1.5 flex size-6 items-center justify-center rounded-full bg-surface text-ink shadow-card transition-colors group-hover:bg-brand group-hover:text-white">
				<?php priniti_the_icon( 'arrow-up-right', 'size-3.5' ); ?>
			</span>
		</div>
		<div class="flex flex-1 flex-col justify-center px-2.5 py-2">
			<h3 class="font-display text-xs font-semibold leading-tight sm:text-[13px]"><?php echo esc_html( $category['name'] ); ?></h3>
			<?php if ( null !== $count ) : ?>
				<p class="mt-0.5 text-[11px] text-ink-soft"><?php echo esc_html( 0 === $count ? 'Coming soon' : sprintf( '%d %s', $count, 1 === $count ? 'product' : 'products' ) ); ?></p>
			<?php endif; ?>
		</div>
	</a>
	<?php
}

/** The category card row used on the homepage and About page. */
function priniti_category_card_list( array $categories, array $counts ): void {
	?>
	<ul role="list" class="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-2.5 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 md:mx-0 md:grid md:grid-cols-5 md:gap-3 md:overflow-visible md:px-0 md:pb-0 lg:grid-cols-10 lg:gap-2.5">
		<?php foreach ( $categories as $i => $c ) : ?>
			<li class="w-[34%] shrink-0 snap-start sm:w-[24%] md:w-auto"><?php priniti_category_card( $c, $i, $counts[ $c['slug'] ] ?? 0 ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/** CategoryBanner. */
/**
 * Visual personality per category: an eyebrow, a fallback line of copy (used only when the category has no
 * description of its own) and the vector accents drawn around the packs. Matched by slug prefix; editable through
 * the `priniti_category_art` filter. Copy describes the occasion, never the product (no ingredient or health claims).
 */
function priniti_category_art( string $slug, string $name ): array {
	$art = array(
		'cookies'      => array( 'Tea-time bakery', 'Priniti cookies for chai breaks, lunch boxes and sharing.', array( 'cookie', 'grain', 'dots' ) ),
		'rusk'         => array( 'Chai partner', 'Priniti rusk for your morning and evening chai.', array( 'grain', 'cookie', 'dots' ) ),
		'indian'       => array( 'Indian classics', 'Traditional Priniti namkeen for tea-time, travel and family gatherings.', array( 'chili', 'leaf', 'dots' ) ),
		'namkeen'      => array( 'Indian classics', 'Traditional Priniti namkeen for tea-time, travel and family gatherings.', array( 'chili', 'leaf', 'dots' ) ),
		'potato'       => array( 'Crunch time', 'Priniti potato chips in bold flavours for movie nights and parties.', array( 'burst', 'zigzag', 'dots' ) ),
		'charchare'    => array( 'Crunchy sticks', 'CharChare sticks for anytime snack cravings.', array( 'zigzag', 'burst', 'dots' ) ),
		'popcorn'      => array( 'Movie night', 'Priniti popcorn for movie nights and get-togethers.', array( 'burst', 'confetti', 'dots' ) ),
		'puffs'        => array( 'Fun snacking', 'Colourful Priniti puffs and fryums for playful snack breaks.', array( 'confetti', 'burst', 'dots' ) ),
		'ringo'        => array( 'Ring of fun', 'Ringo Star rings for snack-time fun with friends.', array( 'confetti', 'squiggle', 'dots' ) ),
		'sweets'       => array( 'Festive mithai', 'Priniti sweets for festivals, celebrations and gifting.', array( 'flower', 'sparkle', 'dots' ) ),
		'donut'        => array( 'Sweet bakes', 'Priniti donut cakes are on their way to the online store.', array( 'confetti', 'sparkle', 'dots' ) ),
	);
	$match = array( 'Priniti category', sprintf( 'Explore Priniti %s for every snack moment.', $name ), array( 'sparkle', 'squiggle', 'dots' ) );
	foreach ( $art as $prefix => $a ) {
		if ( str_starts_with( $slug, $prefix ) ) {
			$match = $a;
			break;
		}
	}
	return (array) apply_filters(
		'priniti_category_art',
		array(
			'eyebrow' => $match[0],
			'text'    => $match[1],
			'accents' => $match[2],
		),
		$slug
	);
}

/** Draws a category's vector accents around its hero composition. */
function priniti_category_accents( array $accents, string $tone ): void {
	// Natural colours per accent (crumbs look like crumbs, leaves are green); the dot grid follows the tone.
	$colour = array(
		'cookie'   => 'text-[#d9a25f]',
		'grain'    => 'text-[#c9a23a]',
		'chili'    => 'text-brand',
		'leaf'     => 'text-leaf',
		'burst'    => 'text-[#f5b301]',
		'zigzag'   => 'text-brand/60',
		'confetti' => 'text-brand/70',
		'flower'   => 'text-[#f08c00]',
		'sparkle'  => 'text-brand',
		'squiggle' => 'text-navy/40',
		'dots'     => 'navy' === $tone ? 'text-navy/25' : 'text-ink/15',
	);
	$spots = array(
		'left-[54%] top-[10%] size-10 -rotate-12 motion-safe:animate-float',
		'right-[4%] top-[14%] size-12 rotate-12 motion-safe:animate-float-delayed',
		'bottom-[8%] left-[50%] size-24',
	);
	foreach ( array_slice( $accents, 0, 3 ) as $i => $kind ) {
		$size = in_array( $kind, array( 'squiggle', 'zigzag' ), true ) ? 'h-5 w-20' : '';
		$grain = 'grain' === $kind ? 'h-14 w-7' : '';
		priniti_decor( $kind, priniti_cx( 'hidden md:block', $spots[ $i ], $size, $grain, $colour[ $kind ] ?? 'text-brand' ) );
	}
}

function priniti_category_banner( array $category, int $index, int $count, array $products = array() ): void {
	$tone = priniti_tone_for_index( $index );
	$art  = priniti_category_art( (string) $category['slug'], (string) $category['name'] );
	$text = $category['description'] ?: $art['text'];
	$has  = (bool) array_filter( $products, static fn ( $p ) => ! empty( $p['images'][0] ) );
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'relative overflow-hidden rounded-[2rem]', priniti_tint_for_index( $index ) ) ); ?>">
		<?php
		priniti_decor( 'blob', '-right-24 -top-32 hidden size-[34rem] text-surface/60 md:block' );
		priniti_decor( 'squiggle', 'bottom-6 left-6 h-4 w-20 text-brand/30 sm:left-10' );
		priniti_category_accents( $art['accents'], $tone );
		?>
		<div class="relative grid items-center gap-2 md:grid-cols-[1.1fr_1fr]">
			<div class="relative z-10 px-6 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
				<?php priniti_eyebrow( $art['eyebrow'], 'mb-3' ); ?>
				<h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl"><?php echo esc_html( $category['name'] ); ?></h1>
				<p class="mt-3 max-w-lg text-base leading-relaxed text-ink-soft sm:text-lg"><?php echo esc_html( $text ); ?></p>
				<div class="mt-6 flex flex-wrap items-center gap-3">
					<?php if ( $count > 0 ) : ?>
						<?php priniti_button_link( '#products', 'Shop ' . esc_html( $category['name'] ) . priniti_icon( 'arrow-right', 'size-4' ), 'primary', 'md', 'h-11 px-5' ); ?>
					<?php endif; ?>
					<p class="inline-flex min-h-11 items-center gap-2 rounded-full bg-surface px-4 text-sm font-semibold shadow-card">
						<?php priniti_the_icon( 'package', 'size-4 text-brand' ); ?>
						<?php echo esc_html( 0 === $count ? 'Coming soon' : sprintf( '%d %s', $count, 1 === $count ? 'product' : 'products' ) ); ?>
					</p>
				</div>
			</div>
			<?php if ( $has ) : ?>
				<div class="relative mx-auto -mt-6 w-full max-w-md px-4 pb-4 md:mt-0 md:max-w-none md:px-0 md:pb-0 lg:pr-6">
					<?php priniti_pack_stage( $products, $tone, '', true ); ?>
				</div>
			<?php elseif ( $category['image'] ) : ?>
				<div aria-hidden="true" class="relative mx-auto h-52 w-full max-w-xs md:h-72">
					<img src="<?php echo esc_url( $category['image']['src'] ); ?>" alt="" class="absolute inset-0 size-full object-contain p-6">
				</div>
			<?php else : ?>
				<div aria-hidden="true" class="relative mx-auto hidden h-72 w-full items-center justify-center md:flex">
					<span class="flex size-40 items-center justify-center rounded-full bg-surface text-brand shadow-soft"><?php priniti_the_icon( 'package', 'size-16 [stroke-width:1.25]' ); ?></span>
					<?php priniti_decor( 'ring', 'left-1/2 top-1/2 size-60 -translate-x-1/2 -translate-y-1/2 text-ink/15' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/** Category navigation pills (shop hero and category pages), the current category highlighted. */
function priniti_category_pills( array $categories, array $counts, string $current = '', string $class = '' ): void {
	$pill = static fn ( bool $on ) => priniti_cx( 'inline-flex min-h-10 shrink-0 items-center gap-2 whitespace-nowrap rounded-full px-4 text-sm font-semibold transition', $on ? 'bg-ink text-white' : 'bg-surface text-ink ring-1 ring-line hover:text-brand hover:ring-brand/50' );
	?>
	<nav aria-label="Shop by category" class="<?php echo esc_attr( priniti_cx( '-mx-4 overflow-x-auto px-4 scrollbar-none sm:mx-0 sm:px-0', $class ) ); ?>">
		<ul role="list" class="flex gap-2 pb-1 sm:flex-wrap">
			<li><a href="<?php echo esc_url( priniti_url( '/shop' ) ); ?>" class="<?php echo esc_attr( $pill( '' === $current ) ); ?>" <?php echo '' === $current ? 'aria-current="page"' : ''; ?>>All</a></li>
			<?php foreach ( $categories as $c ) : ?>
				<?php $n = $counts[ $c['slug'] ] ?? 0; ?>
				<li>
					<a href="<?php echo esc_url( $c['href'] ); ?>" class="<?php echo esc_attr( $pill( $c['slug'] === $current ) ); ?>" <?php echo $c['slug'] === $current ? 'aria-current="page"' : ''; ?>>
						<?php echo esc_html( $c['name'] ); ?>
						<span class="text-xs font-medium opacity-60"><?php echo esc_html( $n ? (string) $n : 'Soon' ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/** Compact branded hero for /shop. */
function priniti_shop_hero( string $heading, int $total, array $products ): void {
	?>
	<div class="relative overflow-hidden rounded-[2rem] bg-linear-to-br from-cream via-blush to-brand-tint ring-1 ring-line/60">
		<?php
		priniti_decor( 'blob', '-right-20 -top-32 hidden size-[28rem] text-brand/10 md:block' );
		priniti_decor( 'dots', 'left-[44%] top-6 hidden size-24 text-brand/15 md:block' );
		priniti_decor( 'sparkle', 'left-[52%] bottom-10 hidden size-4 text-navy/40 md:block' );
		priniti_decor( 'chili', 'right-[38%] top-[16%] hidden size-9 -rotate-12 text-brand motion-safe:animate-float lg:block' );
		priniti_decor( 'leaf', 'right-[3%] bottom-[20%] hidden size-9 rotate-12 text-leaf/70 motion-safe:animate-float-delayed lg:block' );
		priniti_decor( 'leaf', 'right-4 bottom-4 size-8 text-leaf/30 md:hidden' );
		?>
		<div class="relative grid items-center md:grid-cols-[1.3fr_1fr]">
			<div class="px-6 py-8 sm:px-8 lg:px-12 lg:py-10">
				<?php priniti_eyebrow( 'Online store', 'mb-3' ); ?>
				<h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-[2.75rem]"><?php echo esc_html( $heading ); ?></h1>
				<p class="mt-3 max-w-lg text-base leading-relaxed text-ink-soft">Namkeen, chips, puffs, sweets, cookies and rusk from Priniti, all in one place. Pick a category or search for your favourite.</p>
				<p class="mt-5 inline-flex items-center gap-2 rounded-full bg-surface px-4 py-2 text-sm font-semibold shadow-card">
					<?php priniti_the_icon( 'shopping-bag', 'size-4 text-brand' ); ?>
					<?php echo esc_html( sprintf( '%d %s', $total, 1 === $total ? 'product' : 'products' ) ); ?>
				</p>
			</div>
			<?php if ( $products ) : ?>
				<div class="relative hidden w-full max-w-md justify-self-center md:block lg:max-w-lg lg:pr-6">
					<?php priniti_pack_stage( $products, 'brand', '', true ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/** Grid of the other categories, used at the foot of a category page. */
function priniti_related_categories( array $categories, array $counts, string $current ): void {
	$others = array_values( array_filter( $categories, static fn ( $c ) => $c['slug'] !== $current ) );
	if ( ! $others ) {
		return;
	}
	?>
	<section aria-labelledby="related-categories-heading" class="mt-14 lg:mt-20">
		<?php priniti_section_heading( array( 'id' => 'related-categories-heading', 'eyebrow' => 'Keep exploring', 'title' => 'More Priniti categories', 'href' => priniti_url( '/shop' ), 'link_label' => 'Shop all', 'class' => 'mb-6' ) ); ?>
		<ul role="list" class="grid grid-cols-3 gap-3 sm:grid-cols-5 lg:grid-cols-9">
			<?php foreach ( $others as $i => $c ) : ?>
				<?php $index = (int) array_search( $c['slug'], array_column( $categories, 'slug' ), true ); ?>
				<li><?php priniti_category_card( $c, $index, $counts[ $c['slug'] ] ?? 0 ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/** ReviewCard (real reviews only). */
function priniti_review_card( array $review ): void {
	?>
	<article class="flex h-full flex-col gap-4 rounded-card border border-line bg-surface p-6">
		<div class="flex items-center justify-between gap-3"><?php priniti_rating_stars( (float) $review['rating'], null, 'md' ); ?></div>
		<p class="flex-1 leading-relaxed text-ink"><?php echo esc_html( $review['body'] ); ?></p>
		<footer class="border-t border-line pt-4 text-sm">
			<p class="font-semibold"><?php echo esc_html( $review['author'] ); ?></p>
			<p class="text-ink-soft"><?php echo esc_html( 'Product: ' . $review['productName'] ); ?></p>
		</footer>
	</article>
	<?php
}

/**
 * Latest approved product reviews with a rating (reviews API equivalent). No sample reviews are ever returned.
 *
 * @return array<int, array{id:int, author:string, rating:int, body:string, productName:string}>
 */
function priniti_featured_reviews( int $limit = 3 ): array {
	$pre = apply_filters( 'priniti_pre_reviews', null );
	if ( is_array( $pre ) ) {
		return $pre;
	}
	if ( ! post_type_exists( 'product' ) ) {
		return array();
	}
	$out = array();
	foreach ( get_comments( array( 'post_type' => 'product', 'status' => 'approve', 'type' => 'review', 'number' => $limit * 3, 'meta_key' => 'rating' ) ) as $c ) { // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$rating = (int) get_comment_meta( $c->comment_ID, 'rating', true );
		if ( $rating < 1 || '' === trim( $c->comment_content ) ) {
			continue;
		}
		$out[] = array(
			'id'          => (int) $c->comment_ID,
			'author'      => $c->comment_author,
			'rating'      => $rating,
			'body'        => wp_strip_all_tags( $c->comment_content ),
			'productName' => html_entity_decode( get_the_title( (int) $c->comment_post_ID ), ENT_QUOTES, 'UTF-8' ),
		);
		if ( count( $out ) >= $limit ) {
			break;
		}
	}
	return $out;
}

/** QuantitySelector (wired by assets/src/js/product.ts via [data-qty]). */
function priniti_quantity_selector( string $name, int $max, string $size = 'md' ): void {
	$btn = priniti_cx( 'flex items-center justify-center rounded-full text-ink transition hover:bg-ink/5 disabled:opacity-35 disabled:hover:bg-transparent', 'sm' === $size ? 'size-8' : 'size-10' );
	?>
	<div role="group" aria-label="<?php echo esc_attr( 'Quantity for ' . $name ); ?>" data-qty data-max="<?php echo esc_attr( (string) $max ); ?>" class="inline-flex items-center rounded-full border border-line bg-surface p-0.5">
		<button type="button" data-qty-dec aria-label="Decrease quantity" disabled class="<?php echo esc_attr( $btn ); ?>"><?php priniti_the_icon( 'minus', 'size-4' ); ?></button>
		<output aria-live="polite" data-qty-value class="min-w-8 text-center text-sm font-semibold tabular-nums">1</output>
		<button type="button" data-qty-inc aria-label="Increase quantity" class="<?php echo esc_attr( $btn ); ?>"><?php priniti_the_icon( 'plus', 'size-4' ); ?></button>
	</div>
	<?php
}

/** ProductDetails: supplied details only; anything missing says "Coming soon", never filler. */
function priniti_product_details( array $product ): void {
	$text = static function ( $v ): string {
		return is_string( $v ) && '' !== trim( $v ) ? trim( $v ) : '';
	};
	$list = static function ( $v ): array {
		if ( is_array( $v ) ) {
			return array_values( array_filter( array_map( static fn ( $i ) => is_string( $i ) ? trim( $i ) : '', $v ) ) );
		}
		return is_string( $v ) ? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\n/', $v ) ) ) ) : array();
	};
	$soon       = '<p class="text-ink-soft">Coming soon.</p>';
	$highlights = $list( $product['highlights'] );
	$packs      = priniti_pack_labels( $product );
	$skus       = array_values( array_unique( array_filter( array_column( $product['variants'], 'sku' ) ) ) );
	$faqs       = is_array( $product['faqs'] ) ? array_filter( $product['faqs'], static fn ( $f ) => is_array( $f ) && ! empty( $f['question'] ) && ! empty( $f['answer'] ) ) : array();

	$info = array(
		'Brand'     => 'Priniti',
		'Category'  => $product['categoryName'] ?: 'Coming soon',
		'Pack size' => $packs ? implode( ' · ', $packs ) : 'To be confirmed',
	);
	if ( $skus ) {
		$info['SKU'] = implode( ', ', $skus );
	}

	$panel = static function ( string $title, string $icon, bool $open, callable $body ): void {
		?>
		<details <?php echo $open ? 'open' : ''; ?> class="group rounded-2xl bg-surface ring-1 ring-line/70 transition-shadow open:shadow-card">
			<summary class="flex min-h-14 cursor-pointer list-none items-center gap-3 rounded-2xl px-5 py-3 font-display text-base font-semibold [&::-webkit-details-marker]:hidden">
				<span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><?php priniti_the_icon( $icon, 'size-4' ); ?></span>
				<span class="flex-1"><?php echo esc_html( $title ); ?></span>
				<?php priniti_the_icon( 'chevron-down', 'size-5 text-ink-soft transition-transform duration-200 group-open:rotate-180' ); ?>
			</summary>
			<div class="px-5 pb-5 text-sm leading-relaxed text-ink-soft sm:text-[15px]"><?php $body(); ?></div>
		</details>
		<?php
	};
	$prose = static fn ( string $v ) => static function () use ( $v, $soon ): void {
		echo $v ? '<p class="max-w-2xl">' . nl2br( esc_html( $v ) ) . '</p>' : $soon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	};
	?>
	<section aria-labelledby="details-heading">
		<h2 id="details-heading" class="sr-only">Product details</h2>
		<div class="grid gap-4 lg:grid-cols-[1.5fr_1fr] lg:gap-6">
			<div class="rounded-[1.5rem] bg-surface p-6 shadow-card ring-1 ring-line/70 sm:p-8">
				<?php priniti_eyebrow( 'About this product', 'mb-3' ); ?>
				<h3 class="font-display text-xl font-bold sm:text-2xl"><?php echo esc_html( $product['name'] ); ?></h3>
				<div class="mt-3 leading-relaxed text-ink-soft">
					<?php echo $product['description'] ? '<p class="max-w-2xl">' . esc_html( $product['description'] ) . '</p>' : '<p>A full description is coming soon.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<h4 class="mt-6 text-sm font-semibold">Highlights</h4>
				<?php if ( $highlights ) : ?>
					<ul role="list" class="mt-3 grid gap-2 sm:grid-cols-2">
						<?php foreach ( $highlights as $h ) : ?>
							<li class="flex items-start gap-2 text-sm text-ink"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-leaf-tint text-leaf"><?php priniti_the_icon( 'check', 'size-3' ); ?></span><?php echo esc_html( $h ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="mt-1 text-sm text-ink-soft">Coming soon.</p>
				<?php endif; ?>
			</div>
			<div class="rounded-[1.5rem] bg-navy p-6 text-white shadow-card sm:p-8">
				<p class="text-xs font-bold uppercase tracking-[0.16em] text-white/60">Product information</p>
				<dl class="mt-4 divide-y divide-white/10 text-sm">
					<?php foreach ( $info as $label => $value ) : ?>
						<div class="flex justify-between gap-4 py-3">
							<dt class="text-white/70"><?php echo esc_html( $label ); ?></dt>
							<dd class="text-right font-semibold"><?php echo esc_html( $value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
		</div>

		<div class="mt-4 grid items-start gap-3 md:grid-cols-2 lg:mt-6 lg:gap-4">
			<?php
			$panel( 'Ingredients', 'wheat', false, $prose( $text( $product['ingredients'] ) ) );
			$panel(
				'Nutrition information',
				'info',
				false,
				static function () use ( $product, $soon ): void {
					$rows = is_array( $product['nutrition'] ) ? $product['nutrition'] : array();
					if ( ! $rows ) {
						echo $soon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						return;
					}
					?>
					<table class="w-full max-w-md text-left text-sm">
						<caption class="sr-only">Nutrition information per 100 g</caption>
						<thead><tr class="border-b border-line text-ink"><th class="py-2 font-semibold">Nutrient</th><th class="py-2 font-semibold">Per 100 g</th></tr></thead>
						<tbody>
							<?php foreach ( $rows as $r ) : ?>
								<tr class="border-b border-line"><td class="py-2"><?php echo esc_html( $r['label'] ?? '' ); ?></td><td class="py-2 tabular-nums"><?php echo esc_html( $r['per100g'] ?? '' ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php
				}
			);
			$panel(
				'Pack sizes',
				'package',
				false,
				static function () use ( $packs ): void {
					if ( ! $packs ) {
						echo '<p>Pack size to be confirmed.</p>';
						return;
					}
					echo '<p>Available in ' . esc_html( implode( ' and ', $packs ) ) . ( count( $packs ) > 1 ? ' packs. Choose your pack size above.' : ' packs.' ) . '</p>';
				}
			);
			$panel( 'Storage', 'sparkles', false, $prose( $text( $product['storage'] ) ) );
			$panel(
				'Shipping and delivery',
				'truck',
				false,
				static function () use ( $product, $text ): void {
					$note = $text( $product['shippingNote'] );
					echo '<p>' . esc_html( $note ?: 'Delivery details are coming soon.' ) . '</p>';
					echo '<p class="mt-2"><a class="font-semibold text-brand hover:text-brand-dark" href="' . esc_url( priniti_url( '/shipping-policy' ) ) . '">Read the shipping policy</a></p>';
				}
			);
			if ( $faqs ) {
				$panel(
					'Questions',
					'headphones',
					false,
					static function () use ( $faqs ): void {
						echo '<dl class="flex flex-col gap-3">';
						foreach ( $faqs as $f ) {
							echo '<div><dt class="font-semibold text-ink">' . esc_html( $f['question'] ) . '</dt><dd class="mt-1">' . esc_html( $f['answer'] ) . '</dd></div>';
						}
						echo '</dl>';
					}
				);
			}
			?>
		</div>
	</section>
	<?php
}

/**
 * Trust notes under the purchase panel. Only statements that are true for every order
 * (who makes the product, how to track and get help); no certifications or delivery promises.
 */
function priniti_product_assurances(): void {
	$config = priniti_site_config();
	$items  = array(
		array( 'icon' => 'badge-check', 'title' => 'Made by Priniti', 'text' => $config['legal_name'], 'href' => priniti_url( '/about' ) ),
		array( 'icon' => 'sparkles', 'title' => 'Swad Mein No.1', 'text' => 'The Priniti promise', 'href' => priniti_url( '/about' ) ),
		array( 'icon' => 'package-check', 'title' => 'Track your order', 'text' => 'Order status online', 'href' => priniti_url( '/track-order' ) ),
		array( 'icon' => 'headphones', 'title' => 'Need help?', 'text' => 'Contact our team', 'href' => priniti_url( '/contact' ) ),
	);
	?>
	<ul role="list" class="grid grid-cols-2 gap-2.5">
		<?php foreach ( $items as $i ) : ?>
			<li>
				<a href="<?php echo esc_url( $i['href'] ); ?>" class="flex h-full flex-col items-start gap-2 rounded-2xl bg-surface p-3 ring-1 ring-line/70 transition hover:-translate-y-0.5 hover:shadow-card min-[440px]:flex-row min-[440px]:items-center min-[440px]:gap-3">
					<span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-navy-tint text-navy"><?php priniti_the_icon( $i['icon'], 'size-[18px]' ); ?></span>
					<span class="min-w-0">
						<span class="block text-[13px] font-semibold leading-tight"><?php echo esc_html( $i['title'] ); ?></span>
						<span class="block text-xs leading-tight text-ink-soft"><?php echo esc_html( $i['text'] ); ?></span>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="flex items-start gap-3 rounded-2xl border border-dashed border-line p-4">
		<span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-leaf-tint text-leaf"><?php priniti_the_icon( 'truck', 'size-[18px]' ); ?></span>
		<div class="text-sm">
			<p class="font-semibold">Delivery</p>
			<p class="text-ink-soft">Delivery estimates by pincode are coming soon. <a href="<?php echo esc_url( priniti_url( '/shipping-policy' ) ); ?>" class="font-semibold text-brand hover:text-brand-dark">Shipping policy</a></p>
		</div>
	</div>
	<?php
}

/**
 * Reviews block on the product page: real WooCommerce reviews only, with an honest empty state.
 *
 * @param WP_Comment[] $reviews Approved reviews.
 */
function priniti_product_reviews( array $product, array $reviews ): void {
	?>
	<section id="reviews" aria-labelledby="reviews-heading" class="mt-14 scroll-mt-24 lg:mt-20">
		<?php priniti_section_heading( array( 'id' => 'reviews-heading', 'eyebrow' => 'Customer reviews', 'title' => 'What customers say', 'class' => 'mb-6' ) ); ?>
		<?php if ( $reviews && null !== $product['rating'] ) : ?>
			<div class="grid gap-4 lg:grid-cols-[18rem_1fr] lg:gap-6">
				<div class="flex flex-col items-start gap-2 rounded-[1.5rem] bg-surface p-6 ring-1 ring-line/70">
					<p class="font-display text-5xl font-extrabold tabular-nums"><?php echo esc_html( sprintf( '%.1f', (float) $product['rating'] ) ); ?></p>
					<?php priniti_rating_stars( (float) $product['rating'], null, 'md' ); ?>
					<p class="text-sm text-ink-soft"><?php echo esc_html( sprintf( _n( 'Based on %d review', 'Based on %d reviews', (int) $product['reviewCount'], 'priniti' ), (int) $product['reviewCount'] ) ); ?></p>
				</div>
				<ul role="list" class="grid gap-4 sm:grid-cols-2">
					<?php foreach ( $reviews as $c ) : ?>
						<?php $r = (int) get_comment_meta( $c->comment_ID, 'rating', true ); ?>
						<li>
							<article class="flex h-full flex-col gap-3 rounded-[1.25rem] bg-surface p-5 ring-1 ring-line/70">
								<?php
								if ( $r > 0 ) {
									priniti_rating_stars( (float) $r, null, 'sm' );
								}
								?>
								<p class="flex-1 leading-relaxed"><?php echo esc_html( wp_strip_all_tags( $c->comment_content ) ); ?></p>
								<p class="text-sm font-semibold"><?php echo esc_html( $c->comment_author ); ?></p>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<?php priniti_empty_state( 'message-square-text', 'No reviews yet', 'Reviews from customers who bought ' . $product['name'] . ' will appear here.' ); ?>
		<?php endif; ?>
	</section>
	<?php
}

/** "Complete your snack box": a slim promo strip linking across the range. */
function priniti_snack_box_strip( string $exclude_category = '' ): void {
	$picks = array_slice(
		array_values(
			array_filter(
				priniti_catalog(),
				static fn ( $p ) => $p['categorySlug'] !== $exclude_category && ! empty( $p['images'] )
			)
		),
		0,
		3
	);
	?>
	<aside aria-labelledby="snackbox-heading" class="relative mt-14 overflow-hidden rounded-[1.75rem] bg-brand text-white lg:mt-20">
		<?php
		priniti_decor( 'blob', '-right-16 -top-24 size-80 text-brand-dark/70' );
		priniti_decor( 'dots', 'bottom-4 left-[40%] size-24 text-white/15' );
		priniti_decor( 'sparkle', 'left-6 top-6 size-5 text-white/70' );
		?>
		<div class="relative grid items-center gap-6 p-6 sm:p-8 md:grid-cols-[1.3fr_1fr] lg:px-12">
			<div>
				<p class="text-xs font-bold uppercase tracking-[0.16em] text-white/75">Complete your snack box</p>
				<h2 id="snackbox-heading" class="mt-2 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Mix namkeen, chips, sweets and cookies</h2>
				<p class="mt-2 max-w-md text-white/85">Add a few favourites from other Priniti categories to the same order.</p>
				<?php priniti_button_link( priniti_url( '/shop' ), 'Shop the range' . priniti_icon( 'arrow-right', 'size-4' ), 'outline', 'md', 'mt-5 border-white bg-white text-ink hover:bg-white/90' ); ?>
			</div>
			<?php if ( $picks ) : ?>
				<ul role="list" class="flex justify-center gap-3" aria-label="Snacks from other categories">
					<?php foreach ( $picks as $i => $p ) : ?>
						<li class="<?php echo esc_attr( 1 === $i ? '-translate-y-2' : 'translate-y-2' ); ?>">
							<a href="<?php echo esc_url( $p['href'] ); ?>" class="relative block size-24 rounded-2xl bg-white/95 shadow-lift transition hover:-translate-y-1 sm:size-28">
								<?php priniti_product_image( $p['images'][0], $p['name'], '112px', 'p-2' ); ?>
								<span class="sr-only"><?php echo esc_html( $p['name'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</aside>
	<?php
}

/**
 * Gallery items for a product page: every real product photo, topped up to three views with presentation views
 * made from the first official photo (a close-up crop and the pack on a Priniti backdrop). Those views are labelled
 * as such on the page and in their alt text; they are never presented as separate product photographs.
 *
 * @return array<int, array{kind:string, image:?array, label:string, note:string}>
 */
function priniti_gallery_items( array $product ): array {
	$items = array();
	foreach ( $product['images'] as $i => $img ) {
		$items[] = array(
			'kind'  => 'photo',
			'image' => $img,
			'label' => 0 === $i ? 'Front view' : sprintf( 'Photo %d', $i + 1 ),
			'note'  => '',
		);
	}
	if ( ! $items ) {
		return array(
			array(
				'kind'  => 'photo',
				'image' => null,
				'label' => 'Image coming soon',
				'note'  => '',
			),
		);
	}
	$first = $product['images'][0];
	if ( count( $items ) < 3 ) {
		$items[] = array(
			'kind'  => 'detail',
			'image' => $first,
			'label' => 'Close-up',
			'note'  => 'Close-up of the product photo',
		);
	}
	if ( count( $items ) < 3 ) {
		$items[] = array(
			'kind'  => 'scene',
			'image' => $first,
			'label' => 'On a Priniti backdrop',
			'note'  => 'Product photo on a Priniti backdrop',
		);
	}
	return $items;
}

/** Product gallery: swipeable slides, thumbnails, previous / next, hover zoom on photos (interactions.ts). */
function priniti_product_gallery( array $product, int $cat_index, string $tone ): void {
	$items = priniti_gallery_items( $product );
	$total = count( $items );
	$name  = $product['name'];
	$alt   = static function ( array $item ) use ( $name ): string {
		if ( 'photo' === $item['kind'] ) {
			return $item['image']['alt'] ?? '' ?: $name;
		}
		return $name . ': ' . lcfirst( $item['note'] );
	};
	?>
	<div class="flex min-w-0 flex-col gap-3 lg:sticky lg:top-24 lg:self-start" data-priniti-gallery aria-roledescription="carousel" aria-label="<?php echo esc_attr( $name . ' images' ); ?>">
		<div class="relative">
			<ul role="list" tabindex="0" data-gallery-track class="focus-visible:outline-offset-4 scrollbar-none flex snap-x snap-mandatory overflow-x-auto overscroll-x-contain rounded-[1.75rem] ring-1 ring-line/70">
				<?php foreach ( $items as $i => $item ) : ?>
					<li role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( '%d of %d: %s', $i + 1, $total, $item['label'] ) ); ?>" class="<?php echo esc_attr( priniti_cx( 'relative aspect-square w-full shrink-0 snap-center overflow-hidden sm:aspect-[4/3] lg:aspect-square', 'scene' === $item['kind'] ? 'bg-linear-to-br from-navy to-navy-dark' : ( 'detail' === $item['kind'] ? 'bg-surface' : priniti_tint_for_index( $cat_index ) ) ) ); ?>" <?php echo 'photo' === $item['kind'] && $item['image'] ? 'data-zoom' : ''; ?>>
						<?php if ( 'photo' === $item['kind'] ) : ?>
							<?php priniti_decor_backdrop( $tone ); ?>
							<?php priniti_product_image( $item['image'] ? array_merge( $item['image'], array( 'alt' => $alt( $item ) ) ) : null, $name, '(min-width:1024px) 50vw, 94vw', 'p-8 drop-shadow-[0_18px_24px_rgb(21_26_46/0.18)] transition-transform duration-200 ease-out group-hover:scale-100 sm:p-12', 0 === $i, 'bg-transparent' ); ?>
						<?php elseif ( 'detail' === $item['kind'] ) : ?>
							<img src="<?php echo esc_url( $item['image']['src'] ); ?>" <?php echo ! empty( $item['image']['srcset'] ) ? 'srcset="' . esc_attr( $item['image']['srcset'] ) . '"' : ''; ?> sizes="(min-width:1024px) 50vw, 94vw" alt="<?php echo esc_attr( $alt( $item ) ); ?>" loading="lazy" decoding="async" class="absolute inset-0 size-full scale-[1.9] object-contain [transform-origin:50%_34%]">
						<?php else : ?>
							<div aria-hidden="true">
								<?php
								priniti_decor( 'arch', 'bottom-[12%] left-[20%] h-[78%] w-[60%] text-brand' );
								priniti_decor( 'dots', 'left-[6%] top-[8%] size-24 text-white/15' );
								priniti_decor( 'chili', 'left-[10%] top-[34%] size-10 -rotate-12 text-brand' );
								priniti_decor( 'leaf', 'right-[8%] top-[24%] size-10 rotate-12 text-leaf' );
								priniti_decor( 'sparkle', 'right-[16%] bottom-[30%] size-4 text-lime' );
								?>
								<div class="absolute bottom-[6%] left-[14%] h-[14%] w-[72%] rounded-[50%] bg-cream shadow-[0_24px_36px_-16px_rgb(0_0_0/0.5)]"></div>
							</div>
							<img src="<?php echo esc_url( $item['image']['src'] ); ?>" <?php echo ! empty( $item['image']['srcset'] ) ? 'srcset="' . esc_attr( $item['image']['srcset'] ) . '"' : ''; ?> sizes="(min-width:1024px) 40vw, 80vw" alt="<?php echo esc_attr( $alt( $item ) ); ?>" loading="lazy" decoding="async" class="absolute bottom-[12%] left-1/2 h-[72%] w-auto -translate-x-1/2 object-contain drop-shadow-[0_20px_22px_rgb(0_0_0/0.35)]">
							<?php priniti_decor_stamp( 'Swad Mein No.1 • Priniti Foods • ', 'right-[5%] top-[5%] size-16 sm:size-20' ); ?>
						<?php endif; ?>
						<?php if ( $item['note'] ) : ?>
							<span class="absolute bottom-3 left-3 rounded-full bg-surface/90 px-3 py-1 text-xs font-medium text-ink-soft shadow-card"><?php echo esc_html( $item['note'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $product['badges'] ) : ?>
				<div class="pointer-events-none absolute left-4 top-4 flex flex-col items-start gap-1.5">
					<?php foreach ( $product['badges'] as $badge ) : ?>
						<span class="<?php echo esc_attr( priniti_badge_classes( 'new' === $badge ? 'leaf' : 'navy', 'px-2.5 py-1 text-xs shadow-card' ) ); ?>"><?php echo esc_html( PRINITI_BADGE_LABELS[ $badge ] ?? $badge ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<span aria-hidden="true" data-zoom-hint class="pointer-events-none absolute bottom-3 right-3 hidden items-center gap-1.5 rounded-full bg-surface/90 px-3 py-1 text-xs font-medium text-ink-soft shadow-card [@media(pointer:fine)]:inline-flex">
				<?php priniti_the_icon( 'search', 'size-3.5' ); ?>Hover to zoom
			</span>
			<?php if ( $total > 1 ) : ?>
				<button type="button" data-gallery-prev aria-label="Previous image" class="absolute left-3 top-1/2 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card transition hover:bg-ink hover:text-white disabled:opacity-0 sm:flex"><?php priniti_the_icon( 'chevron-left', 'size-5' ); ?></button>
				<button type="button" data-gallery-next aria-label="Next image" class="absolute right-3 top-1/2 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full bg-surface/95 text-ink shadow-card transition hover:bg-ink hover:text-white disabled:opacity-0 sm:flex"><?php priniti_the_icon( 'chevron-right', 'size-5' ); ?></button>
				<div aria-hidden="true" class="absolute inset-x-0 bottom-3 flex justify-center gap-1.5 sm:hidden">
					<?php foreach ( $items as $i => $item ) : ?>
						<span data-gallery-dot class="<?php echo esc_attr( priniti_cx( 'h-1.5 rounded-full transition-all', 0 === $i ? 'w-5 bg-ink' : 'w-1.5 bg-ink/25' ) ); ?>"></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $total > 1 ) : ?>
			<ul role="list" class="flex gap-2 overflow-x-auto scrollbar-none" aria-label="<?php echo esc_attr( 'Choose an image of ' . $name ); ?>">
				<?php foreach ( $items as $i => $item ) : ?>
					<li class="shrink-0">
						<button type="button" data-gallery-go="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Show image %d of %d: %s', $i + 1, $total, $item['label'] ) ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-on-class="border-navy ring-2 ring-navy/20" data-off-class="border-line hover:border-ink" class="<?php echo esc_attr( priniti_cx( 'group/thumb relative block size-20 overflow-hidden rounded-xl border transition sm:size-24', 'scene' === $item['kind'] ? 'bg-navy' : 'bg-surface', 0 === $i ? 'border-navy ring-2 ring-navy/20' : 'border-line hover:border-ink' ) ); ?>">
							<?php if ( $item['image'] ) : ?>
								<img src="<?php echo esc_url( $item['image']['thumb'] ?: $item['image']['src'] ); ?>" alt="" loading="lazy" class="<?php echo esc_attr( priniti_cx( 'absolute inset-0 size-full object-contain p-1.5', 'detail' === $item['kind'] ? 'scale-[1.9] p-0 [transform-origin:50%_34%]' : '' ) ); ?>">
							<?php endif; ?>
							<span class="absolute inset-x-0 bottom-0 bg-ink/70 px-1 py-0.5 text-center text-[9px] font-semibold leading-tight text-white"><?php echo esc_html( $item['label'] ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
