<?php
/**
 * Fallback template. The storefront's routes all have dedicated templates; this only renders for
 * WordPress views the design does not cover (e.g. blog archives), using the design's page header.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

get_header();

priniti_part(
	'layout/page-header',
	array(
		'title' => is_singular() ? get_the_title() : wp_strip_all_tags( get_the_archive_title() ),
	)
);
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-8 lg:py-12' ) ); ?>">
	<?php if ( have_posts() ) : ?>
		<div class="flex flex-col gap-6">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'rounded-card bg-surface p-5 shadow-card ring-1 ring-line/70' ); ?>>
					<h2 class="font-display text-lg font-semibold"><a href="<?php the_permalink(); ?>" class="hover:text-brand"><?php the_title(); ?></a></h2>
					<div class="mt-2 text-sm text-ink-soft"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="text-ink-soft"><?php esc_html_e( 'Nothing to show here yet.', 'priniti' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
