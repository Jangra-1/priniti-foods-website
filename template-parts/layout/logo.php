<?php
/**
 * Logo (components/layout/Logo.tsx): the real logo, or a text wordmark when none is configured.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$config = priniti_site_config();
$logo   = $config['logo'];
$class  = $args['class'] ?? 'h-8 w-auto sm:h-9';

if ( $logo ) :
	?>
	<img src="<?php echo esc_url( $logo['src'] ); ?>" alt="<?php echo esc_attr( $config['name'] ); ?>" width="<?php echo (int) $logo['width']; ?>" height="<?php echo (int) $logo['height']; ?>" fetchpriority="high" decoding="async" class="<?php echo esc_attr( priniti_cx( 'block w-auto max-w-none object-contain', $class ) ); ?>">
<?php else : ?>
	<span class="font-display text-xl font-extrabold tracking-tight text-brand lg:text-2xl">Priniti<span class="text-ink"> Foods</span></span>
	<?php
endif;
