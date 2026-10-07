<?php
/**
 * /shop (reference/nextjs/app/shop/page.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$filters = priniti_parse_filters( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters.
$base    = priniti_url( '/shop' );

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php priniti_breadcrumbs( array( array( 'label' => 'Shop' ) ), 'mb-4' ); ?>
	<?php
	$categories = priniti_get_categories();
	$counts     = priniti_category_counts();
	$heading    = priniti_shop_heading( $filters );
	priniti_shop_hero( 'All snacks' === $heading ? 'Shop Priniti Foods' : $heading, (int) array_sum( $counts ), priniti_get_products_by_slugs( priniti_data( 'merchandising' )['heroSlugs'] ) );
	priniti_category_pills( $categories, $counts, '', 'mt-5' );
	?>
	<div class="mt-6 lg:mt-8">
		<?php priniti_catalog_view( $base, $filters, $categories ); ?>
	</div>
</div>
<?php
get_footer();
