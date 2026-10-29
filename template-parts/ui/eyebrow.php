<?php
/**
 * Small uppercase label flanked by short rules (components/ui/Eyebrow.tsx).
 *
 * @package Priniti
 *
 * @var array $args { text: string, class?: string }
 */

defined( 'ABSPATH' ) || exit;
?>
<p class="<?php echo esc_attr( priniti_cx( 'flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-brand', $args['class'] ?? '' ) ); ?>">
	<span aria-hidden="true" class="h-px w-6 bg-brand/50"></span>
	<?php echo esc_html( $args['text'] ); ?>
	<span aria-hidden="true" class="h-px w-6 bg-brand/50"></span>
</p>
