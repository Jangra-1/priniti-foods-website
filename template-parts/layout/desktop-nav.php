<?php
/**
 * Desktop navigation with the categories mega-menu (components/layout/DesktopNav.tsx).
 * Open/close behaviour (hover, click, Escape, focus leaving) lives in assets/src/js/header.ts.
 *
 * @package Priniti
 *
 * @var array $args { items: array }
 */

defined( 'ABSPATH' ) || exit;

$link_styles = 'rounded-full px-3 py-1.5 text-[13px] font-semibold text-ink/80 transition-colors hover:bg-brand-tint hover:text-brand';
$categories  = priniti_get_categories();
?>
<nav aria-label="<?php esc_attr_e( 'Primary', 'priniti' ); ?>" class="hidden lg:block">
	<ul role="list" class="flex items-center gap-0.5">
		<?php foreach ( $args['items'] as $item ) : ?>
			<li>
				<?php if ( ! empty( $item['has_category_menu'] ) ) : ?>
					<div class="relative" data-priniti-menu>
						<button type="button" aria-expanded="false" aria-controls="categories-menu" data-priniti-menu-toggle class="<?php echo esc_attr( priniti_cx( $link_styles, 'inline-flex items-center gap-1' ) ); ?>">
							<?php echo esc_html( $item['label'] ); ?>
							<span data-open-class="rotate-180" class="inline-flex transition-transform duration-200"><?php priniti_the_icon( 'chevron-down', 'size-4' ); ?></span>
						</button>
						<div id="categories-menu" hidden data-priniti-menu-panel class="absolute left-1/2 top-full z-50 -translate-x-1/2 pt-3">
							<div class="w-[34rem] animate-pop rounded-card border border-line bg-surface p-3 shadow-lift">
								<ul role="list" class="grid grid-cols-2 gap-1">
									<?php foreach ( $categories as $i => $category ) : ?>
										<li>
											<a href="<?php echo esc_url( $category['href'] ); ?>" class="group/cat flex items-center gap-3 rounded-xl p-2 transition-colors hover:bg-brand-tint">
												<span class="<?php echo esc_attr( priniti_cx( 'relative size-11 shrink-0 overflow-hidden rounded-lg', priniti_tint_for_index( $i ) ) ); ?>">
													<?php if ( $category['image'] ) : ?>
														<img src="<?php echo esc_url( $category['image']['thumb'] ?? $category['image']['src'] ); ?>" alt="" loading="lazy" class="absolute inset-0 size-full object-contain p-1 transition-transform duration-300 group-hover/cat:scale-110">
													<?php endif; ?>
												</span>
												<span class="min-w-0">
													<span class="block text-sm font-semibold"><?php echo esc_html( $category['name'] ); ?></span>
													<?php if ( $category['description'] ) : ?>
														<span class="block truncate text-xs text-ink-soft"><?php echo esc_html( $category['description'] ); ?></span>
													<?php endif; ?>
												</span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
								<a href="<?php echo esc_url( priniti_url( '/shop' ) ); ?>" class="mt-2 flex items-center justify-between rounded-xl bg-ink px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand">
									<?php esc_html_e( 'Shop all products', 'priniti' ); ?>
									<?php priniti_the_icon( 'arrow-right', 'size-4' ); ?>
								</a>
							</div>
						</div>
					</div>
				<?php else : ?>
					<?php
					$is_current = priniti_is_current_path( $item['path'] );
					?>
					<a
						href="<?php echo esc_url( priniti_url( $item['path'] ) ); ?>"
						<?php echo $is_current ? 'aria-current="page"' : ''; ?>
						class="<?php echo esc_attr( priniti_cx( $link_styles, $is_current ? 'bg-brand-tint text-brand' : '' ) ); ?>"
					><?php echo esc_html( $item['label'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
