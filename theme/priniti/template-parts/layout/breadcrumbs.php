<?php
/**
 * Breadcrumbs with BreadcrumbList structured data (components/layout/Breadcrumbs.tsx).
 *
 * @package Priniti
 *
 * @var array $args { items: array<array{label:string, href?:string}>, class?: string }
 */

defined( 'ABSPATH' ) || exit;

$all = array_merge( array( array( 'label' => __( 'Home', 'priniti' ), 'href' => priniti_url( '/' ) ) ), $args['items'] );
$ld  = array(
	'@context'        => 'https://schema.org',
	'@type'           => 'BreadcrumbList',
	'itemListElement' => array_map(
		static fn ( array $c, int $i ): array => array_filter(
			array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $c['label'],
				'item'     => $c['href'] ?? null,
			)
		),
		$all,
		array_keys( $all )
	),
);
?>
<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'priniti' ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<ol class="flex flex-wrap items-center gap-1.5 text-sm text-ink-soft">
		<?php
		foreach ( $all as $i => $crumb ) :
			$last = count( $all ) - 1 === $i;
			?>
			<li class="flex items-center gap-1.5">
				<?php if ( $i > 0 ) : ?>
					<?php priniti_the_icon( 'chevron-right', 'size-3.5 text-ink-soft/60' ); ?>
				<?php endif; ?>
				<?php if ( ! empty( $crumb['href'] ) && ! $last ) : ?>
					<a href="<?php echo esc_url( $crumb['href'] ); ?>" class="transition-colors hover:text-brand"><?php echo esc_html( $crumb['label'] ); ?></a>
				<?php else : ?>
					<span <?php echo $last ? 'aria-current="page" class="font-medium text-ink"' : ''; ?>><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
<script type="application/ld+json"><?php echo wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ); ?></script>
