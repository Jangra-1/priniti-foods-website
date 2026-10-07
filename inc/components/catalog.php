<?php
/**
 * Catalog UI (reference/nextjs/components/catalog/*): CatalogView, CatalogToolbar, FilterSidebar, FilterFields,
 * Pagination. Filters live in the URL; the forms are wired by assets/src/js/catalog.ts, which rebuilds the same
 * query string as the reference (q, collection, category, price, rating, stock, sort, page).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * FilterFields: shared by the sidebar (applies instantly) and the mobile sheet (applies on "Apply").
 */
function priniti_filter_fields( array $filters, array $categories, bool $show_categories, array $caps, string $prefix ): void {
	$legend  = 'mb-2 font-display text-sm font-semibold';
	$row     = 'flex min-h-9 cursor-pointer items-center gap-2.5 text-sm';
	$control = 'size-4 accent-brand';
	?>
	<div class="flex flex-col gap-6">
		<?php if ( $show_categories ) : ?>
			<fieldset>
				<legend class="<?php echo esc_attr( $legend ); ?>">Category</legend>
				<?php foreach ( $categories as $c ) : ?>
					<label class="<?php echo esc_attr( $row ); ?>"><input type="checkbox" name="category" value="<?php echo esc_attr( $c['slug'] ); ?>" class="<?php echo esc_attr( $control ); ?>" <?php checked( in_array( $c['slug'], $filters['categories'], true ) ); ?>><?php echo esc_html( $c['name'] ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<?php if ( $caps['prices'] ) : ?>
			<fieldset>
				<legend class="<?php echo esc_attr( $legend ); ?>">Price</legend>
				<label class="<?php echo esc_attr( $row ); ?>"><input type="radio" name="<?php echo esc_attr( "{$prefix}-price" ); ?>" data-name="price" value="" class="<?php echo esc_attr( $control ); ?>" <?php checked( ! $filters['price'] ); ?>>Any price</label>
				<?php foreach ( priniti_price_ranges() as $value => $label ) : ?>
					<label class="<?php echo esc_attr( $row ); ?>"><input type="radio" name="<?php echo esc_attr( "{$prefix}-price" ); ?>" data-name="price" value="<?php echo esc_attr( $value ); ?>" class="<?php echo esc_attr( $control ); ?>" <?php checked( $filters['price'], $value ); ?>><?php echo esc_html( $label ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<?php if ( $caps['ratings'] ) : ?>
			<fieldset>
				<legend class="<?php echo esc_attr( $legend ); ?>">Rating</legend>
				<label class="<?php echo esc_attr( $row ); ?>"><input type="radio" name="<?php echo esc_attr( "{$prefix}-rating" ); ?>" data-name="rating" value="" class="<?php echo esc_attr( $control ); ?>" <?php checked( ! $filters['rating'] ); ?>>Any rating</label>
				<?php foreach ( PRINITI_RATING_OPTIONS as $r ) : ?>
					<label class="<?php echo esc_attr( $row ); ?>"><input type="radio" name="<?php echo esc_attr( "{$prefix}-rating" ); ?>" data-name="rating" value="<?php echo esc_attr( (string) $r ); ?>" class="<?php echo esc_attr( $control ); ?>" <?php checked( $filters['rating'], $r ); ?>><?php echo esc_html( "{$r} stars and up" ); ?></label>
				<?php endforeach; ?>
			</fieldset>
		<?php endif; ?>

		<?php if ( $caps['stock'] ) : ?>
			<fieldset>
				<legend class="<?php echo esc_attr( $legend ); ?>">Availability</legend>
				<label class="<?php echo esc_attr( $row ); ?>"><input type="checkbox" name="stock" value="1" class="<?php echo esc_attr( $control ); ?>" <?php checked( $filters['inStock'] ); ?>>In stock only</label>
			</fieldset>
		<?php endif; ?>
	</div>
	<?php
}

/** Hidden inputs carrying the current filters (so one control can change while the rest are kept). */
function priniti_filter_state( array $f, string $base ): string {
	return sprintf(
		' data-base="%s" data-state="%s"',
		esc_attr( $base ),
		esc_attr(
			(string) wp_json_encode(
				array(
					'q'          => $f['q'],
					'collection' => $f['collection'],
					'category'   => $f['categories'],
					'price'      => $f['price'],
					'rating'     => $f['rating'],
					'stock'      => $f['inStock'],
					'sort'       => $f['sort'],
				)
			)
		)
	);
}

function priniti_catalog_view( string $base, array $filters, array $categories, string $locked_category = '', ?callable $empty_state = null ): void {
	$result          = priniti_product_list( $filters, $locked_category );
	$caps            = priniti_catalog_capabilities();
	$show_categories = '' === $locked_category;
	$has_sidebar     = priniti_has_filter_groups( $caps, $show_categories );
	$active          = priniti_count_active_filters( $filters, ! $show_categories );
	$is_filtered     = '' !== $filters['q'] || $active > 0;
	$sort_options    = priniti_available_sort_options( $caps );
	$state           = priniti_filter_state( $filters, $base );
	?>
	<div class="<?php echo esc_attr( $has_sidebar ? 'lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-10' : '' ); ?>" data-priniti-catalog<?php echo $state; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in priniti_filter_state(). ?>>
		<?php if ( $has_sidebar ) : ?>
			<aside aria-label="Filters" class="hidden lg:block">
				<div class="sticky top-28 rounded-card border border-line bg-surface p-5" data-filter-scope="instant">
					<div class="mb-5 flex items-center justify-between">
						<h2 class="font-display text-lg font-semibold">Filters</h2>
						<?php if ( $active > 0 ) : ?>
							<button type="button" data-filter-clear class="text-sm font-medium text-brand underline-offset-4 hover:underline">Clear all</button>
						<?php endif; ?>
					</div>
					<?php priniti_filter_fields( $filters, $categories, $show_categories, $caps, 'side' ); ?>
				</div>
			</aside>
		<?php endif; ?>

		<div>
			<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
				<form role="search" class="relative flex-1" data-catalog-search>
					<label for="catalog-search" class="sr-only">Search products</label>
					<?php priniti_the_icon( 'search', 'pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-ink-soft' ); ?>
					<input id="catalog-search" name="q" type="search" value="<?php echo esc_attr( $filters['q'] ); ?>" placeholder="Search snacks" class="h-11 w-full rounded-full border border-line bg-surface pl-12 pr-4 text-base placeholder:text-ink-soft/70 focus:border-ink">
				</form>

				<div class="flex items-center gap-2">
					<button type="button" data-filter-open class="<?php echo esc_attr( priniti_button_classes( 'secondary', 'md', false, 'flex-1 sm:flex-none lg:hidden' ) ); ?>">
						<?php priniti_the_icon( 'sliders-horizontal', 'size-4' ); ?>
						<?php echo esc_html( priniti_has_filter_groups( $caps, $show_categories ) ? 'Filters and sort' : 'Sort' ); ?>
						<?php if ( $active > 0 ) : ?>
							<span class="<?php echo esc_attr( priniti_badge_classes( 'brand' ) ); ?>"><?php echo esc_html( (string) $active ); ?></span>
						<?php endif; ?>
					</button>
					<div class="hidden items-center gap-2 sm:flex">
						<label for="catalog-sort" class="text-sm text-ink-soft">Sort by</label>
						<select id="catalog-sort" data-catalog-sort class="h-11 rounded-full border border-line bg-surface px-4 text-sm font-medium">
							<?php foreach ( $sort_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<dialog data-filter-sheet aria-label="<?php echo esc_attr( $has_sidebar ? 'Filters and sort' : 'Sort' ); ?>" class="p-0 text-ink backdrop:bg-ink/50 backdrop:backdrop-blur-[2px] mx-0 mb-0 mt-auto w-full max-w-none rounded-t-3xl open:animate-sheet-up">
					<div class="flex max-h-[88dvh] flex-col bg-surface" data-filter-scope="sheet">
						<div class="flex items-center justify-between border-b border-line px-4 py-2 pl-5">
							<h2 class="font-display text-lg font-semibold"><?php echo esc_html( $has_sidebar ? 'Filters and sort' : 'Sort' ); ?></h2>
							<button type="button" data-filter-close aria-label="<?php echo esc_attr( 'Close ' . ( $has_sidebar ? 'Filters and sort' : 'Sort' ) ); ?>" class="<?php echo esc_attr( priniti_icon_button_classes() ); ?>"><?php priniti_the_icon( 'x', 'size-5' ); ?></button>
						</div>
						<div class="flex-1 overflow-y-auto overscroll-contain">
							<div class="flex flex-col gap-6 p-5">
								<fieldset>
									<legend class="mb-2 font-display text-sm font-semibold">Sort by</legend>
									<?php foreach ( $sort_options as $value => $label ) : ?>
										<label class="flex min-h-9 cursor-pointer items-center gap-2.5 text-sm"><input type="radio" name="sheet-sort" data-name="sort" value="<?php echo esc_attr( $value ); ?>" class="size-4 accent-brand" <?php checked( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></label>
									<?php endforeach; ?>
								</fieldset>
								<?php priniti_filter_fields( $filters, $categories, $show_categories, $caps, 'sheet' ); ?>
							</div>
						</div>
						<div class="border-t border-line bg-surface p-4">
							<div class="grid grid-cols-2 gap-2">
								<button type="button" data-filter-reset class="<?php echo esc_attr( priniti_button_classes( 'secondary' ) ); ?>">Clear all</button>
								<button type="button" data-filter-apply class="<?php echo esc_attr( priniti_button_classes() ); ?>">Apply</button>
							</div>
						</div>
					</div>
				</dialog>
			</div>

			<p aria-live="polite" class="mb-4 text-sm text-ink-soft"><?php echo esc_html( sprintf( '%d %s', $result['total'], 1 === $result['total'] ? 'product' : 'products' ) ); ?></p>

			<?php if ( $result['items'] ) : ?>
				<?php priniti_product_grid( $result['items'], $has_sidebar ? 'lg:grid-cols-2 xl:grid-cols-3' : '' ); ?>
			<?php elseif ( $empty_state ) : ?>
				<?php $empty_state(); ?>
			<?php else : ?>
				<div class="rounded-card border border-dashed border-line px-6 py-14 text-center">
					<p class="font-display text-lg font-semibold">No products match</p>
					<p class="mt-1 text-ink-soft"><?php echo esc_html( $is_filtered ? 'Try removing a filter or searching for something else.' : 'Products in this category are coming soon.' ); ?></p>
					<?php if ( $is_filtered ) : ?>
						<?php priniti_button_link( $base, 'Clear filters', 'secondary', 'md', 'mt-5' ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php priniti_pagination( $base, $filters, $result['page'], $result['pageCount'] ); ?>
		</div>
	</div>
	<?php
}

function priniti_pagination( string $base, array $filters, int $page, int $page_count ): void {
	if ( $page_count <= 1 ) {
		return;
	}
	$href = static fn ( int $n ) => $base . priniti_query_string( array_merge( $filters, array( 'page' => $n ) ) );
	$item = 'flex size-11 items-center justify-center rounded-full border text-sm font-semibold transition-colors';
	?>
	<nav aria-label="Pagination" class="mt-10 flex items-center justify-center gap-2">
		<?php if ( $page > 1 ) : ?>
			<a href="<?php echo esc_url( $href( $page - 1 ) ); ?>" aria-label="Previous page" class="<?php echo esc_attr( priniti_cx( $item, 'border-line bg-surface hover:border-ink' ) ); ?>"><?php priniti_the_icon( 'chevron-left', 'size-5' ); ?></a>
		<?php endif; ?>
		<?php for ( $n = 1; $n <= $page_count; $n++ ) : ?>
			<a href="<?php echo esc_url( $href( $n ) ); ?>" aria-label="<?php echo esc_attr( "Page {$n}" ); ?>" <?php echo $n === $page ? 'aria-current="page"' : ''; ?> class="<?php echo esc_attr( priniti_cx( $item, $n === $page ? 'border-ink bg-ink text-white' : 'border-line bg-surface hover:border-ink' ) ); ?>"><?php echo esc_html( (string) $n ); ?></a>
		<?php endfor; ?>
		<?php if ( $page < $page_count ) : ?>
			<a href="<?php echo esc_url( $href( $page + 1 ) ); ?>" aria-label="Next page" class="<?php echo esc_attr( priniti_cx( $item, 'border-line bg-surface hover:border-ink' ) ); ?>"><?php priniti_the_icon( 'chevron-right', 'size-5' ); ?></a>
		<?php endif; ?>
	</nav>
	<?php
}

/** Breadcrumbs (components/layout/Breadcrumbs.tsx). */
function priniti_breadcrumbs( array $items, string $class = '' ): void {
	priniti_part( 'layout/breadcrumbs', array( 'items' => $items, 'class' => $class ) );
}
