<?php
/**
 * /category/<slug> (reference/nextjs/app/category/[slug]/page.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$term       = get_queried_object();
$category   = priniti_get_category( $term->slug );
$categories = priniti_get_categories();
$filters    = priniti_parse_filters( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$index      = max( 0, (int) array_search( $term->slug, array_column( $categories, 'slug' ), true ) );
$counts     = priniti_category_counts();
$count      = $counts[ $term->slug ] ?? 0;
$featured   = priniti_get_products( array( 'category' => $term->slug, 'limit' => 4 ) );
$published  = $category ? $category['published'] : true;
$category ??= array(
	'name'        => $term->name,
	'slug'        => $term->slug,
	'description' => wp_strip_all_tags( $term->description ),
	'image'       => null,
	'href'        => get_term_link( $term ),
);

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php
	priniti_breadcrumbs(
		array(
			array( 'label' => 'Shop', 'href' => priniti_url( '/shop' ) ),
			array( 'label' => $category['name'] ),
		),
		'mb-4'
	);
	priniti_category_banner( $category, $index, $count, $featured );
	priniti_category_pills( $categories, $counts, $term->slug, 'mt-5' );
	?>
	<?php if ( ! $published ) : ?>
		<?php
		priniti_empty_state(
			'package',
			$category['name'] . ' are coming soon',
			sprintf( 'There are no %s to show yet. They will appear here as soon as they are available in the online store.', strtolower( $category['name'] ) ),
			array( 'href' => priniti_url( '/shop' ), 'label' => 'Browse all products' ),
			'mt-8'
		);
		?>
	<?php else : ?>
		<div id="products" class="mt-8 scroll-mt-28 lg:mt-10">
			<?php priniti_catalog_view( (string) $category['href'], $filters, $categories, $term->slug ); ?>
		</div>
	<?php endif; ?>
	<?php priniti_snack_box_strip( $term->slug ); ?>
	<?php priniti_related_categories( $categories, $counts, $term->slug ); ?>
</div>
<?php
get_footer();
