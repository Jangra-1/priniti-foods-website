<?php
/**
 * Not found (reference/nextjs/app/not-found.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'flex flex-col items-center gap-5 py-24 text-center' ) ); ?>">
	<h1 class="font-display text-4xl font-extrabold"><?php esc_html_e( 'Page not found', 'priniti' ); ?></h1>
	<p class="max-w-md text-ink-soft"><?php esc_html_e( 'The page you are looking for does not exist, or the product is not available yet.', 'priniti' ); ?></p>
	<div class="flex flex-wrap justify-center gap-3">
		<a href="<?php echo esc_url( priniti_url( '/shop' ) ); ?>" class="<?php echo esc_attr( priniti_button_classes() ); ?>"><?php esc_html_e( 'Browse all products', 'priniti' ); ?></a>
		<a href="<?php echo esc_url( priniti_url( '/' ) ); ?>" class="<?php echo esc_attr( priniti_button_classes( 'secondary' ) ); ?>"><?php esc_html_e( 'Back to home', 'priniti' ); ?></a>
	</div>
</div>
<?php
get_footer();
