<?php
/**
 * Site footer (components/layout/Footer.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$config = priniti_site_config();
$nav    = priniti_navigation()['footer'];
$to     = static fn ( array $items ): array => array_map(
	static fn ( array $l ): array => array(
		'label' => $l['label'],
		'href'  => priniti_url( $l['path'] ),
	),
	$items
);

$columns = array(
	array(
		'title' => __( 'Shop', 'priniti' ),
		'links' => $to( $nav['shop'] ),
	),
	array(
		'title' => __( 'Categories', 'priniti' ),
		'links' => array_map(
			static fn ( array $c ): array => array(
				'label' => $c['name'],
				'href'  => $c['href'],
			),
			priniti_get_categories()
		),
	),
	array(
		'title' => __( 'Customer support', 'priniti' ),
		'links' => $to( $nav['support'] ),
	),
	array(
		'title' => __( 'Company', 'priniti' ),
		'links' => $to( array_merge( array( array( 'label' => 'About us', 'path' => '/about' ) ), $nav['legal'] ) ),
	),
);

$social = array_filter( $config['social'], static fn ( array $s ): bool => ! empty( $s['href'] ) );
?>
<footer class="mt-8 bg-ink text-white lg:mt-10">
	<div class="<?php echo esc_attr( priniti_container_classes( 'py-9 lg:py-10' ) ); ?>">
		<div class="grid gap-8 lg:grid-cols-[1.2fr_2.4fr]">
			<div class="max-w-sm">
				<a href="<?php echo esc_url( priniti_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Priniti Foods home', 'priniti' ); ?>" class="inline-flex rounded-xl bg-white px-3 py-2">
					<?php priniti_part( 'layout/logo' ); ?>
				</a>
				<p class="mt-3 text-[13px] leading-relaxed text-white/70">
					<?php
					/* translators: %s: company legal name */
					echo esc_html( sprintf( __( '%s makes snacks for every moment: namkeen, chips, puffs, sweets, cookies, rusk and more.', 'priniti' ), $config['legal_name'] ) );
					?>
				</p>
				<?php if ( $social ) : ?>
					<ul role="list" class="mt-4 flex gap-2">
						<?php foreach ( $social as $s ) : ?>
							<li>
								<a href="<?php echo esc_url( $s['href'] ); ?>" aria-label="<?php echo esc_attr( $s['label'] ); ?>" class="flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-brand">
									<?php priniti_the_icon( $s['icon'], 'size-[18px]' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="grid grid-cols-2 gap-6 sm:grid-cols-4 lg:gap-8">
				<?php foreach ( $columns as $column ) : ?>
					<div>
						<h2 class="text-xs font-bold uppercase tracking-[0.16em] text-white/60"><?php echo esc_html( $column['title'] ); ?></h2>
						<ul role="list" class="mt-3 flex flex-col gap-2 text-[13px] text-white/75">
							<?php foreach ( $column['links'] as $link ) : ?>
								<li>
									<a href="<?php echo esc_url( $link['href'] ); ?>" class="transition-colors hover:text-white"><?php echo esc_html( $link['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mt-8 flex flex-col gap-2 border-t border-white/10 pt-6 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
			<p>
				<?php
				/* translators: 1: year, 2: company legal name */
				echo esc_html( sprintf( __( '© %1$s %2$s. All rights reserved.', 'priniti' ), wp_date( 'Y' ), $config['legal_name'] ) );
				?>
			</p>
			<?php if ( $config['fssai_license'] ) : ?>
				<p>
					<?php
					/* translators: %s: FSSAI licence number */
					echo esc_html( sprintf( __( 'FSSAI Lic. No. %s', 'priniti' ), $config['fssai_license'] ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</footer>
