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
							<ul role="list" class="grid w-[26rem] animate-pop grid-cols-2 gap-1 rounded-card border border-line bg-surface p-3 shadow-lift">
								<?php foreach ( $categories as $category ) : ?>
									<li>
										<a href="<?php echo esc_url( $category['href'] ); ?>" class="block rounded-xl px-3 py-2.5 transition-colors hover:bg-brand-tint">
											<span class="block text-sm font-semibold"><?php echo esc_html( $category['name'] ); ?></span>
											<?php if ( $category['description'] ) : ?>
												<span class="block truncate text-xs text-ink-soft"><?php echo esc_html( $category['description'] ); ?></span>
											<?php endif; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
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
