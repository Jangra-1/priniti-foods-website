<?php
/**
 * Customer information + address in the design's two fieldsets (reference CheckoutForm).
 * Field definitions come from priniti-core (single "Full name", Indian mobile and pincode, India only).
 *
 * Overrides woocommerce/templates/checkout/form-billing.php.
 *
 * @package Priniti
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

$fields = $checkout->get_checkout_fields( 'billing' );
$render = static function ( array $keys ) use ( &$fields, $checkout ): void {
	foreach ( $keys as $key ) {
		if ( isset( $fields[ $key ] ) ) {
			woocommerce_form_field( $key, $fields[ $key ], $checkout->get_value( $key ) );
			unset( $fields[ $key ] );
		}
	}
};
?>
<div class="woocommerce-billing-fields flex flex-col gap-8">
	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<fieldset class="flex flex-col gap-4">
		<legend class="mb-1 font-display text-xl font-semibold">Customer information</legend>
		<?php $render( array( 'billing_full_name', 'billing_first_name', 'billing_last_name' ) ); ?>
		<div class="grid gap-4 sm:grid-cols-2"><?php $render( array( 'billing_email', 'billing_phone' ) ); ?></div>
	</fieldset>

	<fieldset class="flex flex-col gap-4">
		<legend class="mb-1 font-display text-xl font-semibold">Shipping address</legend>
		<?php $render( array( 'billing_address_1', 'billing_address_2' ) ); ?>
		<div class="grid gap-4 sm:grid-cols-3"><?php $render( array( 'billing_city', 'billing_state', 'billing_postcode' ) ); ?></div>
		<?php
		// Anything else (country, company, plugin fields) after the design's fields.
		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</fieldset>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields flex flex-col gap-3">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<label class="flex cursor-pointer items-center gap-2 text-sm">
				<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox size-4 accent-brand" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1">
				<span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
			</label>
		<?php endif; ?>
		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account flex flex-col gap-4">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
