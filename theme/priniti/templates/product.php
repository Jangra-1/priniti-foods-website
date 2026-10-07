<?php
/**
 * /product/<slug> (reference/nextjs/app/product/[slug]/page.tsx): gallery, purchase panel with pack-size
 * cards, trust and delivery notes, product information, reviews and related product rails.
 *
 * The purchase panel is server-rendered: one panel per pack size, the selected one visible
 * (assets/src/js/interactions.ts switches panels and drives quantity, add to cart, buy now and the
 * sticky mobile buy bar). Every fact shown comes from the product data; anything missing reads "Coming soon".
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$product = priniti_get_product( (string) get_queried_object()->post_name );
if ( ! $product ) {
	// Published but hidden from the catalog (or not visible): behave like the reference's notFound().
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	require PRINITI_DIR . '/404.php';
	return;
}

$images     = $product['images'];
$variants   = $product['variants'];
$selected   = priniti_purchasable_variant( $product ) ?? ( $variants[0] ?? null );
$labelled   = array_values( array_filter( $variants, static fn ( $v ) => '' !== $v['label'] ) );
$buyable    = array_values( array_filter( $variants, 'priniti_is_purchasable' ) );
$related    = priniti_related_products( $product, 4 );
$categories = priniti_get_categories();
$cat_index  = max( 0, (int) array_search( $product['categorySlug'], array_column( $categories, 'slug' ), true ) );
$tone       = priniti_tone_for_index( $cat_index );
$summary    = $product['description'] ? wp_trim_words( $product['description'], 32 ) : '';
$max_qty    = (int) priniti_site_config()['commerce']['max_quantity_per_line'];

// "Explore the Priniti range": one product from each other category, so the rail spans the whole range.
$range = array();
foreach ( priniti_catalog() as $p ) {
	if ( $p['categorySlug'] !== $product['categorySlug'] && ! isset( $range[ $p['categorySlug'] ] ) && ! empty( $p['images'] ) ) {
		$range[ $p['categorySlug'] ] = $p;
	}
}
$range = array_slice( array_values( $range ), 0, 8 );

$reviews = $product['reviewCount'] ? get_comments(
	array(
		'post_id' => $product['id'],
		'status'  => 'approve',
		'type'    => 'review',
		'number'  => 6,
	)
) : array();

get_header();
priniti_json_ld( priniti_product_json_ld( $product ) );
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php
	priniti_breadcrumbs(
		array(
			array( 'label' => 'Shop', 'href' => priniti_url( '/shop' ) ),
			array( 'label' => $product['categoryName'], 'href' => $product['categoryHref'] ),
			array( 'label' => $product['name'] ),
		)
	);
	?>

	<div class="mt-5 grid gap-8 lg:mt-6 lg:grid-cols-[1.05fr_1fr] lg:gap-12 xl:gap-16">
		<div class="flex flex-col gap-3 lg:sticky lg:top-24 lg:self-start" data-priniti-gallery>
			<div class="<?php echo esc_attr( priniti_cx( 'relative aspect-square overflow-hidden rounded-[1.75rem] ring-1 ring-line/70 sm:aspect-[4/3] lg:aspect-square', priniti_tint_for_index( $cat_index ) ) ); ?>" data-gallery-main data-zoom>
				<?php priniti_decor_backdrop( $tone ); ?>
				<?php priniti_product_image( $images[0] ?? null, $product['name'], '(min-width:1024px) 50vw, 94vw', 'p-8 drop-shadow-[0_18px_24px_rgb(21_26_46/0.18)] transition-transform duration-200 ease-out group-hover:scale-100 sm:p-12', true, 'bg-transparent' ); ?>
				<?php if ( $product['badges'] ) : ?>
					<div class="absolute left-4 top-4 flex flex-col items-start gap-1.5">
						<?php foreach ( $product['badges'] as $badge ) : ?>
							<span class="<?php echo esc_attr( priniti_badge_classes( 'new' === $badge ? 'leaf' : 'navy', 'px-2.5 py-1 text-xs shadow-card' ) ); ?>"><?php echo esc_html( PRINITI_BADGE_LABELS[ $badge ] ?? $badge ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $images ) : ?>
					<span aria-hidden="true" class="absolute bottom-3 right-3 hidden items-center gap-1.5 rounded-full bg-surface/90 px-3 py-1 text-xs font-medium text-ink-soft shadow-card [@media(pointer:fine)]:inline-flex">
						<?php priniti_the_icon( 'search', 'size-3.5' ); ?>Hover to zoom
					</span>
				<?php endif; ?>
			</div>
			<?php if ( count( $images ) > 1 ) : ?>
				<ul role="list" class="flex gap-2 overflow-x-auto scrollbar-none" aria-label="<?php echo esc_attr( $product['name'] . ' images' ); ?>">
					<?php foreach ( $images as $i => $img ) : ?>
						<li class="shrink-0">
							<button type="button" data-gallery-thumb="<?php echo esc_attr( (string) wp_json_encode( $img ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Show image %d of %d', $i + 1, count( $images ) ) ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-on-class="border-navy ring-2 ring-navy/20" data-off-class="border-line hover:border-ink" class="<?php echo esc_attr( priniti_cx( 'relative block size-20 overflow-hidden rounded-xl border bg-surface transition', 0 === $i ? 'border-navy ring-2 ring-navy/20' : 'border-line hover:border-ink' ) ); ?>">
								<img src="<?php echo esc_url( $img['thumb'] ?: $img['src'] ); ?>" alt="" loading="lazy" class="absolute inset-0 size-full object-contain p-1.5">
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="flex flex-col gap-6">
			<div>
				<a href="<?php echo esc_url( $product['categoryHref'] ); ?>" class="<?php echo esc_attr( priniti_badge_classes( 'tint', 'px-3 py-1.5 text-xs uppercase tracking-wide transition-colors hover:bg-brand hover:text-white' ) ); ?>"><?php echo esc_html( $product['categoryName'] ); ?></a>
				<h1 class="mt-3 font-display text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl"><?php echo esc_html( $product['name'] ); ?></h1>
				<?php
				if ( $product['reviewCount'] && null !== $product['rating'] ) {
					echo '<a href="#reviews" class="mt-3 inline-flex">';
					priniti_rating_stars( (float) $product['rating'], (int) $product['reviewCount'], 'md' );
					echo '</a>';
				}
				?>
				<?php if ( $summary ) : ?>
					<p class="mt-3 max-w-xl leading-relaxed text-ink-soft"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>
			</div>

			<div class="flex flex-col gap-5 rounded-[1.5rem] bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6" data-priniti-purchase>
				<?php if ( $labelled ) : ?>
					<fieldset>
						<legend class="mb-3 flex items-center gap-2 text-sm font-semibold">
							<?php priniti_the_icon( 'package', 'size-4 text-brand' ); ?>Choose pack size
						</legend>
						<div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap">
							<?php foreach ( $labelled as $v ) : ?>
								<label class="relative cursor-pointer">
									<input type="radio" name="priniti-pack" value="<?php echo esc_attr( (string) $v['id'] ); ?>" class="peer sr-only" <?php checked( $selected && $selected['id'] === $v['id'] ); ?>>
									<span class="flex min-h-16 min-w-32 flex-col justify-center rounded-2xl border-2 border-line bg-surface px-4 py-2.5 transition hover:border-ink/40 peer-checked:border-navy peer-checked:bg-navy-tint peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand">
										<span class="font-display text-lg font-bold leading-tight"><?php echo esc_html( $v['label'] ); ?></span>
										<span class="text-xs text-ink-soft"><?php echo esc_html( priniti_is_purchasable( $v ) ? priniti_format_inr( (float) $v['price'] ) : 'Price coming soon' ); ?></span>
									</span>
									<span aria-hidden="true" class="absolute right-2.5 top-2.5 hidden size-5 items-center justify-center rounded-full bg-navy text-white peer-checked:flex"><?php priniti_the_icon( 'check', 'size-3' ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<?php foreach ( $labelled as $v ) : ?>
							<?php if ( 'image-filename' === $v['source'] ) : ?>
								<p class="mt-2 text-xs text-ink-soft" data-variant-only="<?php echo esc_attr( (string) $v['id'] ); ?>" <?php echo $selected && $selected['id'] === $v['id'] ? '' : 'hidden'; ?>>Pack size to be confirmed.</p>
							<?php endif; ?>
						<?php endforeach; ?>
					</fieldset>
				<?php else : ?>
					<p class="flex items-center gap-2 text-sm text-ink-soft"><?php priniti_the_icon( 'package', 'size-4 text-brand' ); ?>Pack size to be confirmed.</p>
				<?php endif; ?>

				<?php
				$panels = $variants ? $variants : array( null );
				foreach ( $panels as $v ) :
					$can_buy = $v && priniti_is_purchasable( $v );
					$active  = ( null === $v && null === $selected ) || ( $v && $selected && $v['id'] === $selected['id'] );
					?>
					<div class="flex flex-col gap-5" data-variant-panel="<?php echo esc_attr( (string) ( $v['id'] ?? 0 ) ); ?>" <?php echo $can_buy ? 'data-sticky-price="' . esc_attr( priniti_format_inr( (float) $v['price'] ) ) . '"' : ''; ?> <?php echo $active ? '' : 'hidden'; ?>>
						<?php if ( $can_buy ) : ?>
							<div class="border-t border-line pt-5">
								<?php priniti_price_display( (float) ( $v['mrp'] ?? $v['price'] ), (float) $v['price'], 'lg' ); ?>
							</div>
							<div class="flex items-center gap-3">
								<span class="text-sm font-semibold">Quantity</span>
								<?php priniti_quantity_selector( $product['name'], $max_qty ); ?>
							</div>
						<?php else : ?>
							<div class="flex items-start gap-3 rounded-2xl border border-dashed border-line bg-canvas p-4">
								<span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><?php priniti_the_icon( 'info', 'size-4' ); ?></span>
								<div>
									<p class="font-display font-bold">Price coming soon</p>
									<p class="mt-0.5 text-sm text-ink-soft">Online ordering for this product opens once its price is published.</p>
								</div>
							</div>
						<?php endif; ?>
						<div class="grid grid-cols-[1fr_auto] items-center gap-3 sm:flex" <?php echo $can_buy ? 'data-buy-actions' : ''; ?>>
							<?php if ( $can_buy ) : ?>
								<?php priniti_add_to_cart_button( $product, $v, array( 'size' => 'lg', 'full_width' => true, 'class' => 'sm:flex-1', 'open_cart' => true ) ); ?>
								<button type="button" data-priniti-buy-now="<?php echo esc_attr( priniti_cart_payload( $product, $v ) ); ?>" class="<?php echo esc_attr( priniti_button_classes( 'dark', 'lg', false, 'order-last col-span-2 sm:order-none sm:flex-1' ) ); ?>">Buy now</button>
							<?php else : ?>
								<?php priniti_button_link( priniti_url( '/contact' ), priniti_icon( 'message-square-text', 'size-4' ) . 'Ask about this product', 'outline', 'lg', 'w-full sm:flex-1' ); ?>
							<?php endif; ?>
							<?php priniti_wishlist_button( $product, 'size-12 border border-line' ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php priniti_product_assurances(); ?>
		</div>
	</div>

	<div class="mt-12 lg:mt-16">
		<?php priniti_product_details( $product ); ?>
	</div>

	<?php priniti_product_reviews( $product, $reviews ); ?>

	<?php if ( $related ) : ?>
		<section aria-labelledby="related-heading" class="mt-14 lg:mt-20">
			<?php priniti_section_heading( array( 'id' => 'related-heading', 'eyebrow' => 'You may also like', 'title' => 'More from ' . $product['categoryName'], 'href' => $product['categoryHref'], 'link_label' => 'View all', 'class' => 'mb-6' ) ); ?>
			<ul role="list" class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
				<?php foreach ( $related as $p ) : ?>
					<li><?php priniti_product_card( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php priniti_snack_box_strip( $product['categorySlug'] ); ?>

	<?php if ( $range ) : ?>
		<section aria-labelledby="range-heading" class="mt-14 lg:mt-20">
			<?php
			priniti_product_carousel(
				'Explore the Priniti range',
				static function (): void {
					priniti_eyebrow( 'From every category', 'mb-2' );
					echo '<h2 id="range-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Explore the Priniti range</h2>';
				},
				$range
			);
			?>
		</section>
	<?php endif; ?>
</div>

<?php if ( $buyable ) : ?>
	<div data-sticky-buy hidden class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface/95 px-4 py-3 shadow-[0_-8px_24px_-12px_rgb(21_26_46/0.25)] backdrop-blur lg:hidden">
		<div class="mx-auto flex max-w-xl items-center gap-3">
			<div class="min-w-0 flex-1">
				<p class="truncate text-xs text-ink-soft"><?php echo esc_html( $product['name'] ); ?></p>
				<p class="font-display text-lg font-bold tabular-nums" data-sticky-price-out><?php echo esc_html( priniti_format_inr( (float) ( $selected['price'] ?? 0 ) ) ); ?></p>
			</div>
			<button type="button" data-sticky-action="add" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', false, 'h-11 px-4' ) ); ?>"><?php priniti_the_icon( 'shopping-bag', 'size-4' ); ?>Add</button>
			<button type="button" data-sticky-action="buy" class="<?php echo esc_attr( priniti_button_classes( 'dark', 'md', false, 'h-11 px-4' ) ); ?>">Buy now</button>
		</div>
	</div>
<?php endif; ?>
<?php
get_footer();
