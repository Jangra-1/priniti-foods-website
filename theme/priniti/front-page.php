<?php
/**
 * Homepage (reference/nextjs/app/page.tsx), in the section order of the approved design. Sections that depend on
 * sales or launch data use neutral, hand-picked labels unless that data really exists.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$merch      = priniti_data( 'merchandising' );
$categories = priniti_get_categories();
$counts     = priniti_category_counts();
$best       = priniti_get_products( array( 'collection' => 'best-sellers', 'limit' => 8 ) );
$new        = priniti_get_products( array( 'collection' => 'new-arrivals', 'limit' => 4 ) );
$hero_packs = priniti_get_products_by_slugs( $merch['heroSlugs'] );
$story      = priniti_get_products_by_slugs( $merch['storySlugs'] );
$picks      = priniti_get_products_by_slugs( $merch['curatedPicks']['slugs'] );
$range      = priniti_get_products_by_slugs( $merch['explore']['slugs'] );
$sweets     = priniti_get_products_by_slugs( $merch['sweetsBakery']['slugs'] );
$featured   = priniti_get_products_by_slugs( array( $merch['featured']['slug'] ) );
$combos     = priniti_get_products( array( 'category' => 'combos', 'limit' => 3 ) );
$promo      = priniti_data( 'home' )['promo'];

$carousel = $best
	? array( 'eyebrow' => 'Top picks', 'title' => 'Best Sellers', 'items' => $best )
	: array( 'eyebrow' => 'From every category', 'title' => $merch['explore']['title'], 'items' => $range );

$tile = static function ( string $slug, string $eyebrow, string $title, string $cta, string $tone ) use ( $sweets ): array {
	$cat = priniti_get_category( $slug );
	return array(
		'eyebrow'  => $eyebrow,
		'title'    => $title,
		'cta'      => $cta,
		'tone'     => $tone,
		'href'     => $cat['href'] ?? priniti_url( '/shop' ),
		'products' => array_values( array_filter( $sweets, static fn ( $p ) => $p['categorySlug'] === $slug ) ),
	);
};

get_header();

priniti_json_ld( priniti_organization_json_ld() );
priniti_json_ld( priniti_website_json_ld() );

priniti_section_hero( $hero_packs, $categories );
priniti_section_categories( $categories, $counts );
priniti_section_featured_products( 'Handpicked for you', 'Featured Products', priniti_url( '/shop' ), $picks );
priniti_section_why();

if ( $carousel['items'] ) :
	?>
	<section aria-labelledby="carousel-heading" class="bg-canvas py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<?php
			priniti_product_carousel(
				$carousel['title'],
				static function () use ( $carousel ): void {
					priniti_eyebrow( $carousel['eyebrow'], 'mb-2' );
					echo '<h2 id="carousel-heading" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">' . esc_html( $carousel['title'] ) . '</h2>';
				},
				$carousel['items']
			);
			?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
endif;

// Combos is ecommerce-only: "coming soon" until real combo products exist.
priniti_section_promo_banner( $combos ? null : $promo['badge'], $combos, array_slice( $hero_packs, 0, 3 ) );
priniti_section_promo_tiles(
	array(
		$tile( 'sweets', 'Mithai', 'Sweets', 'Shop sweets', 'navy' ),
		$tile( 'cookies', 'Bakery', 'Cookies', 'Shop cookies', 'leaf' ),
	)
);

if ( $new ) :
	?>
	<section aria-labelledby="new-heading" class="bg-canvas py-8 lg:py-10">
		<?php priniti_container_open(); ?>
			<?php priniti_section_heading( array( 'id' => 'new-heading', 'eyebrow' => 'Just in', 'title' => 'New Arrivals', 'href' => priniti_url( '/shop' ) . '?collection=new-arrivals', 'class' => 'mb-5' ) ); ?>
			<?php priniti_product_grid( $new, 'md:grid-cols-4' ); ?>
		<?php priniti_container_close(); ?>
	</section>
	<?php
endif;

if ( $featured ) {
	priniti_section_featured_product( $featured[0] );
}
priniti_section_reviews( priniti_featured_reviews() );
priniti_section_stats(
	'The Priniti range',
	'Snacks, sweets and bakery from one brand.',
	array(
		array( 'value' => (string) array_sum( $counts ), 'label' => 'Products online' ),
		array( 'value' => (string) count( $categories ), 'label' => 'Categories' ),
		array( 'value' => 'No.1', 'label' => 'Swad Mein' ),
	)
);
priniti_section_brand_story( $story );
priniti_section_newsletter();
priniti_section_social( $range );

get_footer();
