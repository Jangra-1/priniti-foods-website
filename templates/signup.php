<?php
/**
 * /signup (reference/nextjs/app/signup/page.tsx + SignupForm). Creates a WooCommerce customer through priniti-core.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$result = function_exists( 'priniti_core_form_result' ) ? priniti_core_form_result( 'signup' ) : null;
$values = $result['values'] ?? array();
$errors = $result['errors'] ?? array();
$packs  = priniti_get_products_by_slugs( priniti_data( 'merchandising' )['heroSlugs'] );

get_header();
priniti_auth_shell(
	'Create Your Account',
	'Create an account to make your Priniti Foods shopping experience easier.',
	'Snacks for every moment.',
	'Join Priniti Foods and explore namkeen, chips, puffs, sweets, cookies and more.',
	$packs,
	static function () use ( $result, $values, $errors ): void {
		$link = 'font-semibold text-brand underline underline-offset-4';
		?>
		<form method="post" action="<?php echo esc_url( priniti_url( '/signup' ) ); ?>" novalidate data-priniti-form class="flex flex-col gap-4">
			<?php priniti_form_hidden_fields( 'signup' ); ?>
			<?php priniti_input( array( 'label' => 'Full Name *', 'name' => 'name', 'value' => $values['name'] ?? '', 'error' => $errors['name'] ?? '', 'validate' => 'name', 'attrs' => array( 'autocomplete' => 'name' ) ) ); ?>
			<div class="grid gap-4 sm:grid-cols-2">
				<?php
				priniti_input( array( 'label' => 'Email *', 'name' => 'email', 'type' => 'email', 'value' => $values['email'] ?? '', 'error' => $errors['email'] ?? '', 'validate' => 'email', 'attrs' => array( 'autocomplete' => 'email' ) ) );
				priniti_input( array( 'label' => 'Mobile *', 'name' => 'mobile', 'type' => 'tel', 'value' => $values['mobile'] ?? '', 'error' => $errors['mobile'] ?? '', 'validate' => 'mobile', 'attrs' => array( 'inputmode' => 'numeric', 'autocomplete' => 'tel-national', 'maxlength' => 10, 'data-digits' => true ) ) );
				?>
			</div>
			<div class="grid gap-4 sm:grid-cols-2">
				<?php
				priniti_password( array( 'id' => 'signup-password', 'label' => 'Password *', 'name' => 'password', 'hint' => 'At least 8 characters.', 'error' => $errors['password'] ?? '', 'validate' => 'new-password', 'attrs' => array( 'autocomplete' => 'new-password' ) ) );
				priniti_password( array( 'label' => 'Confirm Password *', 'name' => 'confirm', 'error' => $errors['confirm'] ?? '', 'validate' => 'confirm:signup-password', 'attrs' => array( 'autocomplete' => 'new-password' ) ) );
				?>
			</div>
			<div data-field>
				<label class="flex cursor-pointer items-start gap-2 text-sm">
					<input type="checkbox" id="signup-terms" name="terms" value="1" data-validate="checked:Please agree to the terms to continue." class="mt-0.5 size-4 accent-brand" <?php checked( ! empty( $values['terms'] ) ); ?> aria-describedby="signup-terms-error">
					<span>I agree to the <a href="<?php echo esc_url( priniti_url( '/terms-and-conditions' ) ); ?>" class="<?php echo esc_attr( $link ); ?>">Terms &amp; Conditions</a> and <a href="<?php echo esc_url( priniti_url( '/privacy-policy' ) ); ?>" class="<?php echo esc_attr( $link ); ?>">Privacy Policy</a>.</span>
				</label>
				<p id="signup-terms-error" data-error-for="signup-terms" class="mt-1 text-sm text-brand" <?php echo empty( $errors['terms'] ) ? 'hidden' : ''; ?>><?php echo esc_html( $errors['terms'] ?? '' ); ?></p>
			</div>
			<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', true, 'h-12' ) ); ?>">Create Account</button>
			<?php priniti_form_notice( $result['message'] ?? '', $result['tone'] ?? 'info' ); ?>
			<p class="text-center text-sm text-ink-soft">Already have an account? <a href="<?php echo esc_url( priniti_url( '/login' ) ); ?>" class="font-semibold text-brand underline-offset-4 hover:underline">Log in</a></p>
		</form>
		<?php
	}
);
get_footer();
