<?php
/**
 * Document head and site header (reference/nextjs/app/layout.tsx, top half).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-dvh' ); ?>>
<?php wp_body_open(); ?>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-white">
	<?php esc_html_e( 'Skip to content', 'priniti' ); ?>
</a>
<?php
priniti_part( 'layout/announcement-bar' );
priniti_part( 'layout/header' );
?>
<main id="main">
