<?php
/**
 * My Account navigation in the design's sidebar style (like the policy pages' "On this page" card).
 *
 * Overrides woocommerce/templates/myaccount/navigation.php.
 *
 * @package Priniti
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation lg:sticky lg:top-24 lg:self-start" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
	<ul role="list" class="flex gap-1 overflow-x-auto rounded-2xl bg-surface p-2 shadow-card ring-1 ring-line/70 scrollbar-none lg:flex-col lg:p-3">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php $current = wc_is_current_account_menu_item( $endpoint ); ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?> shrink-0">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo $current ? 'aria-current="page"' : ''; ?> class="<?php echo esc_attr( priniti_cx( 'block whitespace-nowrap rounded-xl px-3 py-2 text-sm font-semibold transition-colors', $current ? 'bg-brand-tint text-brand' : 'text-ink-soft hover:bg-brand-tint hover:text-brand' ) ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php
do_action( 'woocommerce_after_account_navigation' );
