<?php
/**
 * /search?q= (reference/nextjs/app/search/page.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$filters    = priniti_parse_filters( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$categories = priniti_get_categories();
$q          = $filters['q'];
$matching   = $q ? array_values( array_filter( $categories, static fn ( $c ) => priniti_matches_query( $c['name'], $q ) ) ) : array();
$chip       = 'rounded-full border border-line bg-surface px-4 py-2 text-sm font-medium transition-colors hover:border-brand hover:text-brand';
$chips      = static function ( array $list, string $extra = '' ) use ( $chip ): void {
	echo '<ul role="list" class="' . esc_attr( priniti_cx( 'flex flex-wrap gap-2', $extra ) ) . '">';
	foreach ( $list as $c ) {
		echo '<li><a href="' . esc_url( $c['href'] ) . '" class="' . esc_attr( $chip ) . '">' . esc_html( $c['name'] ) . '</a></li>';
	}
	echo '</ul>';
};

get_header();
?>
<div class="<?php echo esc_attr( priniti_container_classes( 'py-6 lg:py-10' ) ); ?>">
	<?php priniti_breadcrumbs( array( array( 'label' => 'Search' ) ) ); ?>
	<h1 class="mb-6 mt-4 font-display text-3xl font-extrabold sm:text-4xl"><?php echo esc_html( $q ? "Results for “{$q}”" : 'Search' ); ?></h1>

	<?php if ( ! $q ) : ?>
		<div class="rounded-card border border-line bg-surface p-6 sm:p-8">
			<form action="<?php echo esc_url( priniti_url( '/search' ) ); ?>" role="search" class="flex flex-col gap-3 sm:flex-row">
				<label for="search-page-input" class="sr-only">Search products</label>
				<input id="search-page-input" name="q" type="search" placeholder="Search by product or category" class="h-12 flex-1 rounded-full border border-line bg-surface px-5 text-base placeholder:text-ink-soft/70 focus:border-ink">
				<button type="submit" class="h-12 rounded-full bg-brand px-7 text-sm font-semibold text-white hover:bg-brand-dark">Search</button>
			</form>
			<h2 class="mb-3 mt-8 font-display text-lg font-semibold">Browse by category</h2>
			<?php $chips( $categories ); ?>
		</div>
	<?php else : ?>
		<?php if ( $matching ) : ?>
			<div class="mb-6">
				<h2 class="mb-2 text-sm font-semibold text-ink-soft">Matching categories</h2>
				<?php $chips( $matching ); ?>
			</div>
		<?php endif; ?>
		<?php
		priniti_catalog_view(
			priniti_url( '/search' ),
			$filters,
			$categories,
			'',
			static function () use ( $q, $categories, $chips ): void {
				?>
				<div class="rounded-card border border-dashed border-line px-6 py-14 text-center">
					<p class="font-display text-lg font-semibold"><?php echo esc_html( "No products found for “{$q}”" ); ?></p>
					<ul role="list" class="mx-auto mt-3 max-w-sm space-y-1 text-ink-soft">
						<li>Check the spelling or try a shorter word.</li>
						<li>Search by product name (for example “jeera”) or by category.</li>
					</ul>
					<h2 class="mb-3 mt-6 text-sm font-semibold text-ink-soft">Browse a category</h2>
					<?php $chips( $categories, 'justify-center' ); ?>
					<?php priniti_button_link( priniti_url( '/shop' ), 'Browse all products', 'primary', 'md', 'mt-6' ); ?>
				</div>
				<?php
			}
		);
		?>
	<?php endif; ?>
</div>
<?php
get_footer();
