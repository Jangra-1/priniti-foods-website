<?php
/**
 * /cart (reference/nextjs/app/cart/page.tsx). CartView is a React island on the WooCommerce Store API
 * (assets/src/js/components/CartView.tsx); a skeleton shows until it loads, like the reference.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php priniti_breadcrumbs( array( array( 'label' => 'Cart' ) ) ); ?>
	<h1 class="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl">Your cart</h1>
	<?php
	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}
	?>
	<div id="priniti-cart-view"><div aria-busy="true" class="h-64 animate-pulse rounded-card bg-line/50"></div></div>
	<noscript><p class="mt-4 text-ink-soft">Please enable JavaScript to view and change your cart.</p></noscript>
</div>
<?php
get_footer();
