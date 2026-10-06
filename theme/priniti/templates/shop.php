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
	<?php priniti_breadcrumbs( array( array( 'label' => 'Shop' ) ) ); ?>
	<h1 class="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl"><?php echo esc_html( priniti_shop_heading( $filters ) ); ?></h1>
	<?php priniti_catalog_view( $base, $filters, priniti_get_categories() ); ?>
</div>
<?php
get_footer();
