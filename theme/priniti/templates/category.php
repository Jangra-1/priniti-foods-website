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
$count      = priniti_category_counts()[ $term->slug ] ?? 0;
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
	priniti_category_banner( $category, $index, $count );
	?>
	<?php if ( ! $published ) : ?>
		<div class="mt-8 flex flex-col items-center gap-4 rounded-card border border-dashed border-line bg-surface px-6 py-14 text-center">
			<p class="font-display text-xl font-semibold"><?php echo esc_html( $category['name'] . ' are coming soon' ); ?></p>
			<p class="max-w-md text-ink-soft"><?php echo esc_html( sprintf( 'There are no %s to show yet. They will appear here as soon as they are available in the online store.', strtolower( $category['name'] ) ) ); ?></p>
			<?php priniti_button_link( priniti_url( '/shop' ), 'Browse all products' ); ?>
		</div>
	<?php else : ?>
		<div class="mt-8 lg:mt-10">
			<?php priniti_catalog_view( (string) $category['href'], $filters, $categories, $term->slug ); ?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
