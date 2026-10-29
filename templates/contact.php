<?php
/**
 * /contact (reference/nextjs/app/contact/page.tsx). The enquiry form posts to priniti-core, which validates it,
 * stores it and emails it to the team.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$company = priniti_data( 'company' );
$c       = $company['contact'];
$packs   = priniti_get_products_by_slugs( priniti_data( 'merchandising' )['heroSlugs'] );
$result  = function_exists( 'priniti_core_form_result' ) ? priniti_core_form_result( 'contact' ) : null;
$values  = $result['values'] ?? array();
$errors  = $result['errors'] ?? array();
$section = 'py-8 lg:py-10';

get_header();

priniti_json_ld(
	array(
		'@context'     => 'https://schema.org',
		'@type'        => 'Organization',
		'name'         => $company['legalName'],
		'url'          => home_url( '/' ),
		'contactPoint' => array(
			array( '@type' => 'ContactPoint', 'contactType' => 'customer service', 'telephone' => $c['customerCare']['tel'], 'email' => $c['customerCare']['email'] ),
			array( '@type' => 'ContactPoint', 'contactType' => 'sales', 'telephone' => $c['sales']['tel'] ),
			array( '@type' => 'ContactPoint', 'contactType' => 'export enquiries', 'telephone' => $c['export']['tel'], 'email' => $c['export']['email'] ),
		),
	)
);

priniti_page_hero(
	'Contact Priniti Foods',
	static function (): void {
		echo 'Let&apos;s <span class="text-brand">Talk</span>';
	},
	'Get in touch with Priniti Foods for general enquiries, product enquiries, sales and export enquiries.',
	array(
		array( 'Explore Products' . priniti_icon( 'arrow-right', 'size-4' ), priniti_url( '/shop' ) ),
		array( 'Send an Enquiry', '#enquiry' ),
	),
	$packs
);
?>

<section aria-labelledby="options-heading" class="<?php echo esc_attr( "bg-surface {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<?php priniti_section_heading( array( 'id' => 'options-heading', 'eyebrow' => 'How can we help?', 'title' => 'Choose how to reach us', 'align' => 'center', 'class' => 'mb-5' ) ); ?>
		<?php priniti_contact_cards(); ?>
	<?php priniti_container_close(); ?>
</section>

<section id="enquiry" aria-labelledby="form-heading" class="<?php echo esc_attr( "scroll-mt-20 bg-canvas {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<h2 id="form-heading" class="sr-only">Send an enquiry</h2>
		<div class="grid gap-4 lg:grid-cols-[1.35fr_1fr] lg:gap-6">
			<form method="post" action="<?php echo esc_url( priniti_url( '/contact' ) . '#enquiry' ); ?>" novalidate data-priniti-form class="flex flex-col gap-4 rounded-3xl bg-surface p-5 shadow-soft ring-1 ring-line/70 sm:p-6">
				<?php priniti_form_hidden_fields( 'contact' ); ?>
				<div>
					<h2 class="font-display text-xl font-extrabold">Send an Enquiry</h2>
					<p class="mt-0.5 text-sm text-ink-soft">Fields marked * are required.</p>
				</div>
				<div class="grid gap-4 sm:grid-cols-2">
					<?php
					priniti_input( array( 'label' => 'Full Name *', 'name' => 'name', 'value' => $values['name'] ?? '', 'error' => $errors['name'] ?? '', 'validate' => 'name', 'attrs' => array( 'autocomplete' => 'name', 'required' => true ) ) );
					priniti_input( array( 'label' => 'Email *', 'name' => 'email', 'type' => 'email', 'value' => $values['email'] ?? '', 'error' => $errors['email'] ?? '', 'validate' => 'email', 'attrs' => array( 'autocomplete' => 'email', 'required' => true ) ) );
					?>
				</div>
				<div class="grid gap-4 sm:grid-cols-2">
					<?php
					priniti_input( array( 'label' => 'Phone', 'name' => 'phone', 'type' => 'tel', 'value' => $values['phone'] ?? '', 'error' => $errors['phone'] ?? '', 'validate' => 'mobile-optional', 'attrs' => array( 'inputmode' => 'numeric', 'autocomplete' => 'tel-national', 'maxlength' => 10, 'data-digits' => true ) ) );
					priniti_select( array( 'label' => 'Enquiry Type *', 'name' => 'type', 'placeholder' => 'Select enquiry type', 'options' => array( 'General Enquiry', 'Product Enquiry', 'Distributor Enquiry', 'Export Enquiry' ), 'value' => $values['type'] ?? '', 'error' => $errors['type'] ?? '', 'validate' => 'required:an enquiry type', 'attrs' => array( 'required' => true ) ) );
					?>
				</div>
				<?php priniti_textarea( array( 'label' => 'Message *', 'name' => 'message', 'value' => $values['message'] ?? '', 'error' => $errors['message'] ?? '', 'validate' => 'minlen:10', 'attrs' => array( 'required' => true ) ) ); ?>
				<button type="submit" class="<?php echo esc_attr( priniti_button_classes( 'primary', 'md', false, 'h-12 self-start px-8' ) ); ?>">Send Enquiry</button>
				<?php priniti_form_notice( $result['message'] ?? '', $result['tone'] ?? 'info' ); ?>
			</form>
			<?php priniti_reach_panel(); ?>
		</div>
	<?php priniti_container_close(); ?>
</section>

<section id="locations" aria-labelledby="locations-heading" class="<?php echo esc_attr( "scroll-mt-20 bg-surface {$section}" ); ?>">
	<?php priniti_container_open(); ?>
		<?php priniti_section_heading( array( 'id' => 'locations-heading', 'eyebrow' => 'Visit us', 'title' => 'Our Locations', 'description' => 'Priniti Foods operates from manufacturing facilities in Sonipat, Haryana and Kanpur, Uttar Pradesh.', 'align' => 'center', 'class' => 'mb-5' ) ); ?>
		<?php priniti_location_cards(); ?>
	<?php priniti_container_close(); ?>
</section>

<?php
priniti_cta_band( 'Looking for Priniti Foods?', 'Explore our range of snacks, sweets and bakery products.', array( 'Explore Products', priniti_url( '/shop' ) ), array( 'Track Order', priniti_url( '/track-order' ) ) );
get_footer();
