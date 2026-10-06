<?php
/**
 * Logo (components/layout/Logo.tsx): the real logo, or a text wordmark when none is configured.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$config = priniti_site_config();
$logo   = $config['logo'];

if ( $logo ) :
	?>
	<img src="<?php echo esc_url( $logo['src'] ); ?>" alt="<?php echo esc_attr( $config['name'] ); ?>" width="<?php echo (int) $logo['width']; ?>" height="<?php echo (int) $logo['height']; ?>" fetchpriority="high" decoding="async" class="h-8 w-auto sm:h-9">
<?php else : ?>
	<span class="font-display text-xl font-extrabold tracking-tight text-brand lg:text-2xl">Priniti<span class="text-ink"> Foods</span></span>
	<?php
endif;
