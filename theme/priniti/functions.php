<?php
/**
 * Priniti theme bootstrap.
 *
 * The visual and UX source of truth is the Next.js project in reference/nextjs (source repository).
 * Templates mirror its components one to one; see docs/ARCHITECTURE.md for the component map.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

define( 'PRINITI_VERSION', '0.1.0' );
define( 'PRINITI_DIR', get_template_directory() );
define( 'PRINITI_URI', get_template_directory_uri() );

require PRINITI_DIR . '/inc/config.php';
require PRINITI_DIR . '/inc/helpers.php';
require PRINITI_DIR . '/inc/setup.php';
require PRINITI_DIR . '/inc/catalog.php';
require PRINITI_DIR . '/inc/assets.php';
require PRINITI_DIR . '/inc/rest.php';
require PRINITI_DIR . '/inc/woocommerce.php';
