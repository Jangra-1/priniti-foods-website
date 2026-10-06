<?php
/**
 * /product/<slug> (reference/nextjs/app/product/[slug]/page.tsx): ProductGallery, ProductPurchasePanel,
 * PackSizeSelector, ProductDetails and related products.
 *
 * The purchase panel is server-rendered: one panel per pack size, the selected one visible
 * (assets/src/js/product.ts switches panels and drives quantity, add to cart and buy now).
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

$images   = $product['images'];
$variants = $product['variants'];
$selected = priniti_purchasable_variant( $product ) ?? ( $variants[0] ?? null );
$labelled = array_values( array_filter( $variants, static fn ( $v ) => '' !== $v['label'] ) );
$related  = priniti_related_products( $product, 4 );

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

	<div class="mt-6 grid gap-8 lg:grid-cols-[1.05fr_1fr] lg:gap-14">
		<div class="flex flex-col gap-3" data-priniti-gallery>
			<div class="relative aspect-square overflow-hidden rounded-card border border-line bg-surface lg:aspect-[4/5]" data-gallery-main>
				<?php priniti_product_image( $images[0] ?? null, $product['name'], '(min-width:1024px) 50vw, 94vw', 'p-6 sm:p-10', true ); ?>
			</div>
			<?php if ( count( $images ) > 1 ) : ?>
				<ul role="list" class="flex gap-2" aria-label="<?php echo esc_attr( $product['name'] . ' images' ); ?>">
					<?php foreach ( $images as $i => $img ) : ?>
						<li>
							<button type="button" data-gallery-thumb="<?php echo esc_attr( (string) wp_json_encode( $img ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Show image %d of %d', $i + 1, count( $images ) ) ); ?>" aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-on-class="border-navy" data-off-class="border-line hover:border-ink" class="<?php echo esc_attr( priniti_cx( 'relative block size-20 overflow-hidden rounded-xl border bg-surface transition-colors', 0 === $i ? 'border-navy' : 'border-line hover:border-ink' ) ); ?>">
								<img src="<?php echo esc_url( $img['thumb'] ?: $img['src'] ); ?>" alt="" loading="lazy" class="absolute inset-0 size-full object-contain p-1.5">
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="flex flex-col gap-5">
			<div>
				<a href="<?php echo esc_url( $product['categoryHref'] ); ?>" class="text-sm font-medium text-ink-soft hover:text-brand"><?php echo esc_html( $product['categoryName'] ); ?></a>
				<h1 class="mt-1 font-display text-3xl font-extrabold leading-tight sm:text-4xl"><?php echo esc_html( $product['name'] ); ?></h1>
				<?php
				if ( $product['reviewCount'] && null !== $product['rating'] ) {
					priniti_rating_stars( (float) $product['rating'], (int) $product['reviewCount'], 'md', 'mt-3' );
				}
				?>
			</div>

			<div class="flex flex-col gap-5" data-priniti-purchase>
				<?php if ( $labelled ) : ?>
					<fieldset>
						<legend class="mb-2 text-sm font-semibold">Pack size</legend>
						<div class="flex flex-wrap gap-2">
							<?php foreach ( $labelled as $v ) : ?>
								<label class="relative cursor-pointer">
									<input type="radio" name="priniti-pack" value="<?php echo esc_attr( (string) $v['id'] ); ?>" class="peer sr-only" <?php checked( $selected && $selected['id'] === $v['id'] ); ?>>
									<span class="flex min-h-11 min-w-20 items-center justify-center rounded-full border px-5 text-sm font-semibold transition-colors border-line bg-surface hover:border-ink peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand"><?php echo esc_html( $v['label'] ); ?></span>
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
					<p class="text-sm text-ink-soft">Pack size to be confirmed.</p>
				<?php endif; ?>

				<?php
				$panels = $variants ? $variants : array( null );
				foreach ( $panels as $v ) :
					$buyable = $v && priniti_is_purchasable( $v );
					$active  = ( null === $v && null === $selected ) || ( $v && $selected && $v['id'] === $selected['id'] );
					?>
					<div class="flex flex-col gap-5" data-variant-panel="<?php echo esc_attr( (string) ( $v['id'] ?? 0 ) ); ?>" <?php echo $active ? '' : 'hidden'; ?>>
						<?php
						if ( $buyable ) {
							priniti_price_display( (float) ( $v['mrp'] ?? $v['price'] ), (float) $v['price'], 'lg' );
						} else {
							echo '<p class="font-display text-xl font-bold text-ink-soft">Price coming soon</p>';
						}
						?>
						<?php if ( $buyable ) : ?>
							<div class="flex items-center gap-3">
								<span class="text-sm font-semibold">Quantity</span>
								<?php priniti_quantity_selector( $product['name'], (int) priniti_site_config()['commerce']['max_quantity_per_line'] ); ?>
							</div>
						<?php endif; ?>
						<div class="flex flex-col gap-3 sm:flex-row sm:items-center">
							<?php priniti_add_to_cart_button( $product, $buyable ? $v : null, array( 'size' => 'lg', 'full_width' => true, 'class' => 'sm:flex-1', 'open_cart' => true ) ); ?>
							<?php if ( $buyable ) : ?>
								<button type="button" data-priniti-buy-now="<?php echo esc_attr( priniti_cart_payload( $product, $v ) ); ?>" class="<?php echo esc_attr( priniti_button_classes( 'dark', 'lg', false, 'sm:flex-1' ) ); ?>">Buy now</button>
							<?php endif; ?>
							<?php priniti_wishlist_button( $product, 'size-12 self-start border border-line sm:self-auto' ); ?>
						</div>
						<?php if ( ! $buyable ) : ?>
							<p class="text-sm text-ink-soft">Online ordering for this product opens once its price is published.</p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="mt-12 lg:mt-16">
		<?php priniti_product_details( $product ); ?>
	</div>

	<?php if ( $related ) : ?>
		<section aria-labelledby="related-heading" class="mt-14 lg:mt-20">
			<?php priniti_section_heading( array( 'id' => 'related-heading', 'title' => 'You may also like', 'href' => $product['categoryHref'], 'link_label' => 'More ' . $product['categoryName'], 'class' => 'mb-6' ) ); ?>
			<ul role="list" class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-4">
				<?php foreach ( $related as $p ) : ?>
					<li><?php priniti_product_card( $p ); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
