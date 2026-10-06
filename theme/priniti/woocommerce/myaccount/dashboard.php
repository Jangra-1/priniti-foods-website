<?php
/**
 * My Account dashboard.
 *
 * Overrides woocommerce/templates/myaccount/dashboard.php.
 *
 * @package Priniti
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$tiles = array(
	array( 'orders', 'shopping-bag', 'Orders', 'Check the status of your orders.' ),
	array( 'edit-address', 'map-pin', 'Addresses', 'Your delivery address.' ),
	array( 'edit-account', 'user', 'Account details', 'Name, email and password.' ),
);
?>
<h2 class="font-display text-xl font-extrabold"><?php echo esc_html( sprintf( 'Hello, %s', $current_user->first_name ?: $current_user->display_name ) ); ?></h2>
<p class="mt-1 text-sm text-ink-soft">From here you can follow your orders, manage your delivery address and update your account details.</p>
<ul role="list" class="mt-5 grid gap-3 sm:grid-cols-3">
	<?php foreach ( $tiles as [ $endpoint, $icon, $title, $text ] ) : ?>
		<li>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="flex h-full flex-col gap-2 rounded-2xl bg-canvas p-4 ring-1 ring-line/70 transition-colors hover:bg-brand-tint">
				<span class="flex size-10 items-center justify-center rounded-xl bg-surface text-brand shadow-card"><?php priniti_the_icon( $icon, 'size-5' ); ?></span>
				<span class="font-display text-base font-bold"><?php echo esc_html( $title ); ?></span>
				<span class="text-xs text-ink-soft"><?php echo esc_html( $text ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
<p class="mt-5 text-sm"><a class="font-semibold text-ink-soft underline-offset-4 hover:text-brand hover:underline" href="<?php echo esc_url( wc_logout_url() ); ?>">Log out</a></p>
<?php
do_action( 'woocommerce_account_dashboard' );
