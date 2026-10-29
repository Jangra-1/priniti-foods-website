<?php
/**
 * /my-account: WooCommerce My Account (orders, addresses, account details, password reset) in the design.
 * Signed-out visitors are sent to /login (inc/routes.php), except for the password-reset screens.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$title = is_user_logged_in() ? 'My account' : 'Reset your password';

get_header();
priniti_part( 'layout/page-header', array( 'eyebrow' => 'Priniti Foods', 'title' => $title ) );
?>
<section class="bg-canvas py-6 lg:py-10">
	<?php priniti_container_open(); ?>
		<?php echo do_shortcode( '[woocommerce_my_account]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php priniti_container_close(); ?>
</section>
<?php
get_footer();
