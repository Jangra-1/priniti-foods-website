<?php
/**
 * Generic page. Used until a page gets its own design template (about, contact, policies, cart, checkout...),
 * and for any page created in WordPress that the design does not cover.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	priniti_part(
		'layout/page-header',
		array(
			'title' => get_the_title(),
		)
	);
	?>
	<div class="<?php echo esc_attr( priniti_container_classes( 'py-8 lg:py-12' ) ); ?>">
		<div class="priniti-content max-w-3xl text-base leading-relaxed text-ink-soft [&_a]:font-semibold [&_a]:text-brand [&_h2]:mt-8 [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-ink [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mt-4 [&_ul]:list-disc [&_ul]:pl-5">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
