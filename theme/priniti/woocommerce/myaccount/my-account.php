<?php
/**
 * My Account layout: navigation card + content card.
 *
 * Overrides woocommerce/templates/myaccount/my-account.php.
 *
 * @package Priniti
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="grid gap-5 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-8">
	<?php do_action( 'woocommerce_account_navigation' ); ?>
	<div class="woocommerce-MyAccount-content min-w-0 rounded-2xl bg-surface p-5 shadow-card ring-1 ring-line/70 sm:p-6">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>
</div>
