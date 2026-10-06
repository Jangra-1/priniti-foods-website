<?php
/**
 * Site header (components/layout/Header.tsx).
 *
 * Rendered on the server; assets/src/js/header.ts adds the scrolled state, the categories menu and the
 * buttons that open the overlays (data-priniti-open). The cart badge is filled in by the cart island,
 * exactly like the reference (it shows no count until the browser knows the cart).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$nav = priniti_navigation();
?>
<header
	data-priniti-header
	data-top-class="border-line/70 bg-surface"
	data-scrolled-class="border-line bg-surface/95 shadow-soft backdrop-blur"
	class="priniti-sticky-header sticky top-0 z-40 border-b transition-[background-color,box-shadow,border-color] duration-200 border-line/70 bg-surface"
>
	<div class="<?php echo esc_attr( priniti_container_classes( 'grid grid-cols-[1fr_auto_1fr] items-center gap-2 transition-[height] duration-200 lg:flex lg:justify-between lg:gap-6 h-14' ) ); ?>">
		<div class="lg:hidden">
			<button type="button" aria-label="<?php esc_attr_e( 'Open menu', 'priniti' ); ?>" data-priniti-open="mobile-nav" class="<?php echo esc_attr( priniti_icon_button_classes( 'size-9', '-ml-2' ) ); ?>">
				<?php priniti_the_icon( 'menu', 'size-5' ); ?>
			</button>
		</div>

		<a href="<?php echo esc_url( priniti_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Priniti Foods home', 'priniti' ); ?>" class="justify-self-center rounded-lg lg:justify-self-auto">
			<?php priniti_part( 'layout/logo' ); ?>
		</a>

		<?php priniti_part( 'layout/desktop-nav', array( 'items' => $nav['desktop'] ) ); ?>

		<div class="flex items-center justify-end gap-0.5">
			<button
				type="button"
				data-priniti-open="search"
				aria-label="<?php esc_attr_e( 'Search products', 'priniti' ); ?>"
				class="mr-2 hidden h-9 w-48 items-center gap-2 rounded-full bg-navy-tint/70 px-3.5 text-left text-[13px] text-ink-soft transition-colors hover:bg-navy-tint lg:flex xl:w-60"
			>
				<?php priniti_the_icon( 'search', 'size-4 shrink-0' ); ?>
				<?php esc_html_e( 'Search snacks...', 'priniti' ); ?>
			</button>
			<button type="button" aria-label="<?php esc_attr_e( 'Search', 'priniti' ); ?>" data-priniti-open="search" class="<?php echo esc_attr( priniti_icon_button_classes( 'size-9', 'lg:hidden' ) ); ?>">
				<?php priniti_the_icon( 'search', 'size-[18px]' ); ?>
			</button>
			<a href="<?php echo esc_url( priniti_account_url() ); ?>" aria-label="<?php esc_attr_e( 'Account', 'priniti' ); ?>" class="<?php echo esc_attr( priniti_icon_button_classes( 'size-9', 'hidden sm:inline-flex' ) ); ?>">
				<?php priniti_the_icon( 'user', 'size-[18px]' ); ?>
			</a>
			<button type="button" aria-label="<?php esc_attr_e( 'Cart', 'priniti' ); ?>" data-priniti-open="cart" data-priniti-cart-button class="<?php echo esc_attr( priniti_icon_button_classes( 'size-9', '-mr-2 lg:mr-0' ) ); ?>">
				<?php priniti_the_icon( 'shopping-bag', 'size-[18px]' ); ?>
				<span data-priniti-cart-count aria-hidden="true" hidden class="absolute right-0.5 top-0.5 flex min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[11px] font-bold leading-5 text-white"></span>
			</button>
		</div>
	</div>
</header>
