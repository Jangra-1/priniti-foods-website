<?php
/**
 * Compact page intro (components/layout/PageHeader.tsx).
 *
 * @package Priniti
 *
 * @var array $args { eyebrow: string, title: string, description?: string }
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="bg-linear-to-br from-brand-tint via-blush to-surface">
	<div class="<?php echo esc_attr( priniti_container_classes( 'py-7 lg:py-9' ) ); ?>">
		<?php if ( ! empty( $args['eyebrow'] ) ) : ?>
			<?php priniti_part( 'ui/eyebrow', array( 'text' => $args['eyebrow'], 'class' => 'mb-2' ) ); ?>
		<?php endif; ?>
		<h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="mt-2 max-w-2xl text-base leading-relaxed text-ink-soft"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
