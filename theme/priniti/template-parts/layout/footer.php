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
	<div class="<?php echo esc_attr( priniti_container_classes( 'relative py-9 lg:py-12' ) ); ?>">
		<?php if ( ! is_front_page() ) : // The homepage already ends with the full newsletter section. ?>
		<div class="mb-10 flex flex-col gap-5 rounded-[1.5rem] bg-white/5 p-5 ring-1 ring-white/10 sm:p-6 md:flex-row md:items-center md:justify-between">
			<div class="flex items-center gap-4">
				<span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand text-white"><?php priniti_the_icon( 'mail', 'size-5' ); ?></span>
				<div>
					<p class="font-display text-lg font-bold"><?php esc_html_e( 'Stay in the snack loop', 'priniti' ); ?></p>
					<p class="text-sm text-white/70"><?php esc_html_e( 'New launches and offers from Priniti, straight to your inbox.', 'priniti' ); ?></p>
				</div>
			</div>
			<div class="flex flex-wrap items-center gap-3">
				<?php priniti_button_link( priniti_url( '/' ) . '#newsletter', priniti_icon( 'bell', 'size-4' ) . esc_html__( 'Subscribe', 'priniti' ), 'primary', 'md', 'h-11 px-5' ); ?>
				<?php if ( $social ) : ?>
					<ul role="list" class="flex gap-2" aria-label="<?php esc_attr_e( 'Priniti on social media', 'priniti' ); ?>">
						<?php foreach ( $social as $s ) : ?>
							<li>
								<a href="<?php echo esc_url( $s['href'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $s['label'] ); ?>" class="flex size-11 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-brand">
									<?php priniti_the_icon( $s['icon'], 'size-[18px]' ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="text-xs text-white/50"><?php esc_html_e( 'Social profiles coming soon.', 'priniti' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="grid gap-8 lg:grid-cols-[1fr_3fr] lg:gap-10">
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
				<p class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">
					<?php priniti_the_icon( 'sparkles', 'size-3.5 text-lime' ); ?>Swad Mein No.1
				</p>
			</div>

			<div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-5 lg:gap-8">
				<?php foreach ( $columns as $column ) : ?>
					<?php
					if ( ! $column['links'] ) {
						continue;
					}
					?>
					<div>
						<h2 class="text-xs font-bold uppercase tracking-[0.16em] text-white/60"><?php echo esc_html( $column['title'] ); ?></h2>
						<ul role="list" class="mt-3 flex flex-col gap-2 text-[13px] text-white/75">
							<?php foreach ( $column['links'] as $link ) : ?>
								<li>
									<a href="<?php echo esc_url( $link['href'] ); ?>" class="inline-flex min-h-6 items-center transition-colors hover:text-white"><?php echo esc_html( $link['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="mt-10 flex flex-col gap-2 border-t border-white/10 pt-6 text-sm text-white/60 sm:flex-row sm:items-center sm:justify-between">
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
