<?php
/**
 * Site footer (components/layout/Footer.tsx), extended: a newsletter / social call-to-action band,
 * brand column, Shop, Categories, Customer support, Company and Policies columns.
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
		'links' => $to( $nav['company'] ?? array() ),
	),
	array(
		'title' => __( 'Policies', 'priniti' ),
		'links' => $to( $nav['legal'] ),
	),
);

$social = array_filter( $config['social'], static fn ( array $s ): bool => ! empty( $s['href'] ) );
?>
<footer class="relative mt-8 overflow-hidden bg-ink text-white lg:mt-10">
	<?php
	priniti_decor( 'blob', '-right-40 -top-40 size-[28rem] text-white/[0.03]' );
	priniti_decor( 'dots', 'left-[30%] top-10 hidden size-28 text-white/5 lg:block' );
	?>
	<div class="<?php echo esc_attr( priniti_container_classes( 'relative py-7 lg:py-9' ) ); ?>">
		<?php if ( ! is_front_page() ) : // The homepage already ends with the full newsletter section. ?>
		<div class="mb-7 flex flex-col gap-3 rounded-2xl bg-white/5 px-4 py-3.5 ring-1 ring-white/10 sm:flex-row sm:items-center sm:justify-between sm:px-5">
			<div class="flex items-center gap-3">
				<span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand text-white"><?php priniti_the_icon( 'mail', 'size-[18px]' ); ?></span>
				<div>
					<p class="font-display text-base font-bold leading-tight"><?php esc_html_e( 'Stay in the snack loop', 'priniti' ); ?></p>
					<p class="text-xs text-white/70 sm:text-sm"><?php esc_html_e( 'New launches and offers from Priniti, straight to your inbox.', 'priniti' ); ?></p>
				</div>
			</div>
			<div class="flex flex-wrap items-center gap-2.5">
				<?php priniti_button_link( priniti_url( '/' ) . '#newsletter', priniti_icon( 'bell', 'size-4' ) . esc_html__( 'Subscribe', 'priniti' ), 'primary', 'md', 'h-10 px-4 text-sm' ); ?>
				<?php if ( $social ) : ?>
					<ul role="list" class="flex gap-2" aria-label="<?php esc_attr_e( 'Priniti on social media', 'priniti' ); ?>">
						<?php foreach ( $social as $s ) : ?>
							<li>
								<a href="<?php echo esc_url( $s['href'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $s['label'] ); ?>" class="flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-brand">
									<?php priniti_the_icon( $s['icon'], 'size-[18px]' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,3.2fr)] lg:gap-10">
			<div class="max-w-sm">
				<a href="<?php echo esc_url( priniti_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Priniti Foods home', 'priniti' ); ?>" class="inline-flex rounded-xl bg-white px-2.5 py-1.5">
					<?php priniti_part( 'layout/logo', array( 'class' => 'h-9' ) ); ?>
				</a>
				<p class="mt-2.5 text-[13px] leading-snug text-white/70">
					<?php
					/* translators: %s: company legal name */
					echo esc_html( sprintf( __( '%s makes snacks for every moment: namkeen, chips, puffs, sweets, cookies, rusk and more.', 'priniti' ), $config['legal_name'] ) );
					?>
				</p>
				<p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
					<?php priniti_the_icon( 'sparkles', 'size-3.5 text-lime' ); ?>Swad Mein No.1
				</p>
			</div>

			<?php // Collapsible link groups on phones (opened for good from md up by interactions.ts initFooter). ?>
			<div class="divide-y divide-white/10 border-y border-white/10 md:grid md:grid-cols-6 md:gap-x-6 md:divide-y-0 md:border-0 lg:gap-x-8">
				<?php foreach ( $columns as $column ) : ?>
					<?php
					if ( ! $column['links'] ) {
						continue;
					}
					$wide = count( $column['links'] ) > 6; // Categories: two link columns from md up.
					?>
					<details open data-footer-section class="<?php echo esc_attr( priniti_cx( 'group/f', $wide ? 'md:col-span-2' : '' ) ); ?>">
						<summary class="flex min-h-11 cursor-pointer list-none items-center justify-between text-xs font-bold uppercase tracking-[0.16em] text-white/60 md:pointer-events-none md:min-h-0 md:pb-2.5 [&::-webkit-details-marker]:hidden">
							<?php echo esc_html( $column['title'] ); ?>
							<?php priniti_the_icon( 'chevron-down', 'size-4 transition-transform group-open/f:rotate-180 md:hidden' ); ?>
						</summary>
						<ul role="list" class="<?php echo esc_attr( priniti_cx( 'grid gap-x-6 gap-y-1.5 pb-3 text-[13px] leading-snug text-white/75 md:pb-0', $wide ? 'grid-cols-2' : 'grid-cols-1' ) ); ?>">
							<?php foreach ( $column['links'] as $link ) : ?>
								<li>
									<a href="<?php echo esc_url( $link['href'] ); ?>" class="inline-flex min-h-7 items-center transition-colors hover:text-white md:min-h-0"><?php echo esc_html( $link['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mt-6 flex flex-col gap-1 border-t border-white/10 pt-4 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between sm:text-[13px]">
			<p>
				<?php
				/* translators: 1: year, 2: company legal name (ends with a full stop) */
				echo esc_html( sprintf( __( '© %1$s %2$s All rights reserved.', 'priniti' ), wp_date( 'Y' ), $config['legal_name'] ) );
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
