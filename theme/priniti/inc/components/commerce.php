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
	$variant     = priniti_default_variant( $product );
	$purchasable = priniti_purchasable_variant( $product );
	$off         = priniti_discount( $purchasable );
	$packs       = priniti_pack_labels( $product );
	?>
	<article class="<?php echo esc_attr( priniti_cx( 'group relative flex h-full flex-col overflow-hidden rounded-2xl bg-surface shadow-card ring-1 ring-line/70 transition duration-300 hover:-translate-y-0.5 hover:shadow-lift has-[.card-link:focus-visible]:ring-2 has-[.card-link:focus-visible]:ring-brand', $class ) ); ?>">
		<div class="relative aspect-[5/4] overflow-hidden bg-surface">
			<?php priniti_product_image( $product['images'][0] ?? null, $product['name'], '(min-width:1280px) 22vw, (min-width:768px) 30vw, 46vw' ); ?>
			<div class="absolute left-2 top-2 flex flex-col items-start gap-1">
				<?php foreach ( $product['badges'] as $badge ) : ?>
					<span class="<?php echo esc_attr( priniti_badge_classes( 'new' === $badge ? 'leaf' : 'navy', 'px-2 py-0.5 text-[10px] shadow-card' ) ); ?>"><?php echo esc_html( PRINITI_BADGE_LABELS[ $badge ] ?? $badge ); ?></span>
				<?php endforeach; ?>
				<?php if ( $off > 0 ) : ?>
					<span class="<?php echo esc_attr( priniti_badge_classes( 'leaf', 'px-2 py-0.5 text-[10px]' ) ); ?>"><?php echo esc_html( $off . '% off' ); ?></span>
				<?php endif; ?>
			</div>
			<?php priniti_wishlist_button( $product, 'absolute right-2 top-2 z-10 size-8' ); ?>
			<button type="button" data-priniti-quickview="<?php echo esc_attr( (string) wp_json_encode( priniti_product_payload( $product ) ) ); ?>" aria-label="<?php echo esc_attr( 'Quick view: ' . $product['name'] ); ?>" class="absolute inset-x-2 bottom-2 z-10 inline-flex h-8 items-center justify-center gap-1.5 rounded-full bg-surface/95 text-xs font-semibold shadow-card transition duration-200 hover:bg-ink hover:text-white lg:translate-y-1 lg:opacity-0 lg:group-focus-within:translate-y-0 lg:group-focus-within:opacity-100 lg:group-hover:translate-y-0 lg:group-hover:opacity-100">
				<?php priniti_the_icon( 'eye', 'size-3.5' ); ?>
				<?php esc_html_e( 'Quick view', 'priniti' ); ?>
			</button>
		</div>

		<div class="flex flex-1 flex-col gap-0.5 p-3">
			<?php if ( $product['categoryHref'] ) : ?>
				<a href="<?php echo esc_url( $product['categoryHref'] ); ?>" class="relative z-10 w-fit text-[10px] font-bold uppercase tracking-wide text-brand transition-colors hover:text-brand-dark"><?php echo esc_html( $product['categoryName'] ); ?></a>
			<?php endif; ?>
			<?php if ( $variant ) : ?>
				<p class="text-[11px] leading-tight text-ink-soft"><?php echo esc_html( $packs ? implode( ' · ', $packs ) : 'Pack size to be confirmed' ); ?></p>
			<?php endif; ?>

			<h3 class="font-display text-sm font-semibold leading-snug">
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
function priniti_category_banner( array $category, int $index, int $count ): void {
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'relative overflow-hidden rounded-[2rem]', priniti_tint_for_index( $index ) ) ); ?>">
		<div class="relative z-10 max-w-2xl px-6 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
			<h1 class="font-display text-3xl font-extrabold sm:text-4xl"><?php echo esc_html( $category['name'] ); ?></h1>
			<?php if ( $category['description'] ) : ?>
				<p class="mt-2 max-w-lg text-base text-ink-soft"><?php echo esc_html( $category['description'] ); ?></p>
			<?php endif; ?>
			<p class="mt-5 inline-flex rounded-full bg-surface px-3.5 py-1.5 text-sm font-semibold"><?php echo esc_html( 0 === $count ? 'Coming soon' : sprintf( '%d %s', $count, 1 === $count ? 'product' : 'products' ) ); ?></p>
		</div>
		<?php if ( $category['image'] ) : ?>
			<img src="<?php echo esc_url( $category['image']['src'] ); ?>" alt="<?php echo esc_attr( $category['image']['alt'] ); ?>" width="480" height="480" class="absolute -right-6 bottom-0 hidden h-full w-auto object-contain md:block">
		<?php else : ?>
			<div aria-hidden="true" class="absolute -right-16 -top-16 hidden size-72 rounded-full bg-surface/60 md:block"></div>
		<?php endif; ?>
	</div>
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
	$section = static function ( string $title, bool $open, callable $body ): void {
		?>
		<details <?php echo $open ? 'open' : ''; ?> class="group border-b border-line py-1">
			<summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 font-display text-lg font-semibold [&::-webkit-details-marker]:hidden">
				<?php echo esc_html( $title ); ?>
				<span aria-hidden="true" class="text-2xl font-normal leading-none transition-transform group-open:rotate-45">+</span>
			</summary>
			<div class="pb-5 text-ink-soft"><?php $body(); ?></div>
		</details>
		<?php
	};
	$text = static fn ( ?string $v ) => static function () use ( $v ): void {
		echo $v ? '<p class="max-w-2xl leading-relaxed">' . esc_html( $v ) . '</p>' : '<p>Coming soon.</p>';
	};
	?>
	<div class="border-t border-line">
		<?php
		$section( 'Description', true, $text( $product['description'] ) );
		$section( 'Ingredients', false, $text( is_string( $product['ingredients'] ) ? $product['ingredients'] : null ) );
		$section(
			'Nutrition information',
			false,
			static function () use ( $product ): void {
				$rows = is_array( $product['nutrition'] ) ? $product['nutrition'] : array();
				if ( ! $rows ) {
					echo '<p>Coming soon.</p>';
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
		$section( 'Storage', false, $text( is_string( $product['storage'] ) ? $product['storage'] : null ) );
		$section( 'Shipping and delivery', false, $text( is_string( $product['shippingNote'] ) ? $product['shippingNote'] : null ) );
		?>
	</div>
	<?php
}
