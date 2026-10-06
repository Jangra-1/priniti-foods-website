<?php
/**
 * /login (reference/nextjs/app/login/page.tsx + LoginForm). Signs in with email or mobile number through
 * priniti-core; "Forgot password" uses WooCommerce's reset flow.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$result = function_exists( 'priniti_core_form_result' ) ? priniti_core_form_result( 'login' ) : null;
$errors = $result['errors'] ?? array();
$packs  = priniti_get_products_by_slugs( priniti_data( 'merchandising' )['heroSlugs'] );
$lost   = function_exists( 'wc_lostpassword_url' ) ? wc_lostpassword_url() : wp_lostpassword_url();

get_header();
priniti_auth_shell(
	'Welcome Back',
	'Sign in to continue shopping with Priniti Foods.',
	'Your favourite snacks, one sign-in away.',
	'Swad Mein No.1. Namkeen, chips, puffs, sweets, cookies and more.',
	$packs,
	static function () use ( $result, $errors, $lost ): void {
		?>
		<form method="post" action="<?php echo esc_url( priniti_url( '/login' ) ); ?>" novalidate data-priniti-form class="flex flex-col gap-4">
			<?php priniti_form_hidden_fields( 'login' ); ?>
			<?php
			priniti_input( array( 'label' => 'Email or Mobile', 'name' => 'identifier', 'value' => $result['values']['identifier'] ?? '', 'error' => $errors['identifier'] ?? '', 'validate' => 'email-or-mobile', 'attrs' => array( 'autocomplete' => 'username' ) ) );
			priniti_password( array( 'label' => 'Password', 'name' => 'password', 'error' => $errors['password'] ?? '', 'validate' => 'required:your password', 'attrs' => array( 'autocomplete' => 'current-password' ) ) );
			?>
			<div class="flex flex-wrap items-center justify-between gap-2 text-sm">
				<label class="flex cursor-pointer items-center gap-2"><input type="checkbox" name="remember" value="1" class="size-4 accent-brand"> Remember me</label>
				<a href="<?php echo esc_url( $lost ); ?>" class="font-semibold text-brand underline-offset-4 hover:underline">Forgot Password?</a>
			</div>
			<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', true, 'h-12' ) ); ?>">Login</button>
			<?php priniti_form_notice( $result['message'] ?? '', $result['tone'] ?? 'info' ); ?>
			<p class="text-center text-sm text-ink-soft">Don&apos;t have an account? <a href="<?php echo esc_url( priniti_url( '/signup' ) ); ?>" class="font-semibold text-brand underline-offset-4 hover:underline">Create Account</a></p>
			<p class="text-center text-sm"><a href="<?php echo esc_url( priniti_url( '/shop' ) ); ?>" class="font-semibold text-ink-soft underline-offset-4 hover:text-brand hover:underline">Continue shopping</a></p>
		</form>
		<?php
	}
);
get_footer();
