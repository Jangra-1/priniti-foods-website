<?php
/**
 * UI primitives (reference/nextjs/components/ui/*): Eyebrow, SectionHeading, Badge, Input, Select, Textarea,
 * PasswordField, FormNotice. Each echoes the same markup and classes as its React counterpart.
 *
 * Form fields carry data-validate rules; assets/src/js/forms.ts mirrors the reference's blur/submit validation,
 * and the server (priniti-core) validates again.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $tone 'brand' (default) or 'lime' (on dark sections: `text-lime [&>span]:bg-lime/50`).
 */
function priniti_eyebrow( string $text, string $class = '', string $tone = 'brand' ): void {
	$color = 'lime' === $tone ? 'text-lime [&>span]:bg-lime/50' : 'text-brand';
	?>
	<p class="<?php echo esc_attr( priniti_cx( 'flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em]', $color, $class ) ); ?>">
		<span aria-hidden="true" class="h-px w-6 bg-brand/50"></span>
		<?php echo esc_html( $text ); ?>
		<span aria-hidden="true" class="h-px w-6 bg-brand/50"></span>
	</p>
	<?php
}

/**
 * SectionHeading.
 *
 * @param array{id?:string, title:string, eyebrow?:string, description?:string, href?:string, link_label?:string, align?:string, class?:string} $a
 */
function priniti_section_heading( array $a ): void {
	$centered = ( $a['align'] ?? 'left' ) === 'center';
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'flex items-end justify-between gap-4', $centered ? 'flex-col items-center text-center' : '', $a['class'] ?? '' ) ); ?>">
		<div class="<?php echo esc_attr( $centered ? 'flex max-w-2xl flex-col items-center' : 'max-w-xl' ); ?>">
			<?php
			if ( ! empty( $a['eyebrow'] ) ) {
				priniti_eyebrow( $a['eyebrow'], 'mb-2' );
			}
			?>
			<h2 <?php echo ! empty( $a['id'] ) ? 'id="' . esc_attr( $a['id'] ) . '"' : ''; ?> class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl"><?php echo esc_html( $a['title'] ); ?></h2>
			<?php if ( ! empty( $a['description'] ) ) : ?>
				<p class="mt-1.5 text-sm text-ink-soft sm:text-base"><?php echo esc_html( $a['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $a['href'] ) ) : ?>
			<a href="<?php echo esc_url( $a['href'] ); ?>" class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-brand transition-colors hover:text-brand-dark">
				<?php echo esc_html( $a['link_label'] ?? 'View all' ); ?>
				<?php priniti_the_icon( 'arrow-right', 'size-4' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

function priniti_badge_classes( string $tone = 'neutral', string $extra = 'px-2.5 py-1 text-xs' ): string {
	$tones = array(
		'brand'   => 'bg-brand text-white',
		'tint'    => 'bg-brand-tint text-brand',
		'navy'    => 'bg-navy text-white',
		'leaf'    => 'bg-leaf-tint text-leaf',
		'neutral' => 'bg-ink/8 text-ink-soft',
		'dark'    => 'bg-ink text-white',
	);
	return priniti_cx( 'inline-flex items-center rounded-full font-semibold leading-none', $tones[ $tone ] ?? $tones['neutral'], $extra );
}

/** Unique, stable-per-request ids for label/input pairs (useId). */
function priniti_field_id( string $name ): string {
	static $n = 0;
	return 'f-' . sanitize_html_class( $name ) . '-' . ( ++$n );
}

/** Renders HTML attributes from an array (null/false skipped, true = boolean attribute). */
function priniti_attrs( array $attrs ): string {
	$out = '';
	foreach ( $attrs as $k => $v ) {
		if ( null === $v || false === $v ) {
			continue;
		}
		$out .= true === $v ? ' ' . esc_attr( $k ) : sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( (string) $v ) );
	}
	return $out;
}

function priniti_field_message( string $id, string $error, string $hint ): void {
	?>
	<p id="<?php echo esc_attr( $id ); ?>-error" data-error-for="<?php echo esc_attr( $id ); ?>" class="text-sm text-brand" <?php echo $error ? '' : 'hidden'; ?>><?php echo esc_html( $error ); ?></p>
	<?php if ( $hint ) : ?>
		<p id="<?php echo esc_attr( $id ); ?>-hint" data-hint-for="<?php echo esc_attr( $id ); ?>" class="text-sm text-ink-soft" <?php echo $error ? 'hidden' : ''; ?>><?php echo esc_html( $hint ); ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Input (components/ui/Input.tsx).
 *
 * @param array{label:string, name:string, type?:string, value?:string, error?:string, hint?:string, hide_label?:bool, class?:string, validate?:string, attrs?:array} $a
 */
function priniti_input( array $a ): void {
	$id    = $a['id'] ?? priniti_field_id( $a['name'] );
	$error = (string) ( $a['error'] ?? '' );
	$hint  = (string) ( $a['hint'] ?? '' );
	?>
	<div class="flex flex-col gap-1.5" data-field>
		<label for="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( priniti_cx( 'text-sm font-medium', ! empty( $a['hide_label'] ) ? 'sr-only' : '' ) ); ?>"><?php echo esc_html( $a['label'] ); ?></label>
		<input
			<?php
			echo priniti_attrs( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in priniti_attrs().
				array_merge(
					array(
						'id'               => $id,
						'name'             => $a['name'],
						'type'             => $a['type'] ?? 'text',
						'value'            => $a['value'] ?? '',
						'aria-invalid'     => $error ? 'true' : null,
						'aria-describedby' => $error ? "{$id}-error" : ( $hint ? "{$id}-hint" : null ),
						'data-validate'    => $a['validate'] ?? null,
						'data-error-class' => 'border-brand',
						'data-ok-class'    => 'border-line focus:border-ink',
						'class'            => priniti_cx( 'h-12 w-full rounded-xl border bg-surface px-5 text-base placeholder:text-ink-soft/70', $error ? 'border-brand' : 'border-line focus:border-ink', $a['class'] ?? '' ),
					),
					$a['attrs'] ?? array()
				)
			);
			?>
		>
		<?php priniti_field_message( $id, $error, $hint ); ?>
	</div>
	<?php
}

/**
 * Select (components/ui/Select.tsx).
 *
 * @param array{label:string, name:string, options:array, placeholder:string, value?:string, error?:string, validate?:string, attrs?:array} $a
 */
function priniti_select( array $a ): void {
	$id    = $a['id'] ?? priniti_field_id( $a['name'] );
	$error = (string) ( $a['error'] ?? '' );
	$value = (string) ( $a['value'] ?? '' );
	?>
	<div class="flex flex-col gap-1.5" data-field>
		<label for="<?php echo esc_attr( $id ); ?>" class="text-sm font-medium"><?php echo esc_html( $a['label'] ); ?></label>
		<select
			<?php
			echo priniti_attrs( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array_merge(
					array(
						'id'               => $id,
						'name'             => $a['name'],
						'aria-invalid'     => $error ? 'true' : null,
						'aria-describedby' => $error ? "{$id}-error" : null,
						'data-validate'    => $a['validate'] ?? null,
						'data-error-class' => 'border-brand',
						'data-ok-class'    => 'border-line focus:border-ink',
						'class'            => priniti_cx( 'h-12 w-full rounded-xl border bg-surface px-5 text-base', $error ? 'border-brand' : 'border-line focus:border-ink' ),
					),
					$a['attrs'] ?? array()
				)
			);
			?>
		>
			<option value=""><?php echo esc_html( $a['placeholder'] ); ?></option>
			<?php foreach ( $a['options'] as $key => $label ) : ?>
				<?php $opt = is_int( $key ) ? $label : $key; ?>
				<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $value, $opt ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php priniti_field_message( $id, $error, '' ); ?>
	</div>
	<?php
}

/**
 * Textarea (components/ui/Textarea.tsx).
 */
function priniti_textarea( array $a ): void {
	$id    = $a['id'] ?? priniti_field_id( $a['name'] );
	$error = (string) ( $a['error'] ?? '' );
	?>
	<div class="flex flex-col gap-1.5" data-field>
		<label for="<?php echo esc_attr( $id ); ?>" class="text-sm font-medium"><?php echo esc_html( $a['label'] ); ?></label>
		<textarea
			<?php
			echo priniti_attrs( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array_merge(
					array(
						'id'               => $id,
						'name'             => $a['name'],
						'rows'             => $a['rows'] ?? 4,
						'aria-invalid'     => $error ? 'true' : null,
						'aria-describedby' => $error ? "{$id}-error" : null,
						'data-validate'    => $a['validate'] ?? null,
						'data-error-class' => 'border-brand',
						'data-ok-class'    => 'border-line focus:border-ink',
						'class'            => priniti_cx( 'w-full rounded-xl border bg-surface px-4 py-3 text-base', $error ? 'border-brand' : 'border-line focus:border-ink' ),
					),
					$a['attrs'] ?? array()
				)
			);
			?>
		><?php echo esc_textarea( (string) ( $a['value'] ?? '' ) ); ?></textarea>
		<?php priniti_field_message( $id, $error, '' ); ?>
	</div>
	<?php
}

/**
 * PasswordField with show/hide toggle (components/account/PasswordField.tsx). Values are never echoed back.
 */
function priniti_password( array $a ): void {
	$id    = $a['id'] ?? priniti_field_id( $a['name'] );
	$error = (string) ( $a['error'] ?? '' );
	$hint  = (string) ( $a['hint'] ?? '' );
	?>
	<div class="flex flex-col gap-1.5" data-field>
		<label for="<?php echo esc_attr( $id ); ?>" class="text-sm font-medium"><?php echo esc_html( $a['label'] ); ?></label>
		<div class="relative">
			<input
				<?php
				echo priniti_attrs( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					array_merge(
						array(
							'id'               => $id,
							'name'             => $a['name'],
							'type'             => 'password',
							'aria-invalid'     => $error ? 'true' : null,
							'aria-describedby' => $error ? "{$id}-error" : ( $hint ? "{$id}-hint" : null ),
							'data-validate'    => $a['validate'] ?? null,
							'data-error-class' => 'border-brand',
							'data-ok-class'    => 'border-line focus:border-ink',
							'class'            => priniti_cx( 'h-12 w-full rounded-xl border bg-surface pl-4 pr-12 text-base', $error ? 'border-brand' : 'border-line focus:border-ink' ),
						),
						$a['attrs'] ?? array()
					)
				);
				?>
			>
			<button type="button" data-password-toggle="<?php echo esc_attr( $id ); ?>" aria-label="<?php esc_attr_e( 'Show password', 'priniti' ); ?>" aria-pressed="false" class="absolute right-1.5 top-1/2 flex size-9 -translate-y-1/2 items-center justify-center rounded-full text-ink-soft transition-colors hover:bg-ink/5 hover:text-ink">
				<span data-when="hidden"><?php priniti_the_icon( 'eye', 'size-[18px]' ); ?></span>
				<span data-when="shown" hidden><?php priniti_the_icon( 'eye-off', 'size-[18px]' ); ?></span>
			</button>
		</div>
		<?php priniti_field_message( $id, $error, $hint ); ?>
	</div>
	<?php
}

/** FormNotice (components/account/FormNotice.tsx); tone 'info' (navy) or 'success'/'error'. */
function priniti_form_notice( string $message, string $tone = 'info' ): void {
	$styles = array(
		'info'    => array( 'bg-navy-tint', 'text-navy', 'info' ),
		'success' => array( 'bg-leaf-tint', 'text-leaf', 'check' ),
		'error'   => array( 'bg-brand-tint', 'text-brand', 'triangle-alert' ),
	);
	[ $bg, $icon_color, $icon ] = $styles[ $tone ] ?? $styles['info'];
	?>
	<div role="status" aria-live="polite">
		<?php if ( $message ) : ?>
			<p class="<?php echo esc_attr( priniti_cx( 'flex items-start gap-2.5 rounded-xl px-4 py-3 text-sm leading-relaxed text-ink', $bg ) ); ?>">
				<?php priniti_the_icon( $icon, priniti_cx( 'mt-0.5 size-4 shrink-0', $icon_color ) ); ?>
				<span><?php echo wp_kses( $message, array( 'a' => array( 'href' => true, 'class' => true ), 'strong' => array() ) ); ?></span>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/** Container-wrapped section open/close helpers keep templates close to the JSX. */
function priniti_container_open( string $extra = '' ): void {
	echo '<div class="' . esc_attr( priniti_container_classes( $extra ) ) . '">';
}
function priniti_container_close(): void {
	echo '</div>';
}

/** <a> styled as a Button (ButtonLink). */
function priniti_button_link( string $href, string $label_html, string $variant = 'primary', string $size = 'md', string $extra = '', array $attrs = array() ): void {
	printf(
		'<a href="%s" class="%s"%s>%s</a>',
		esc_url( $href ),
		esc_attr( priniti_button_classes( $variant, $size, false, $extra ) ),
		priniti_attrs( $attrs ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$label_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- callers pass escaped text + theme icons.
	);
}

/**
 * Empty / coming-soon state: an icon in a tinted circle, a title, a line of copy and an optional action.
 *
 * @param array{href:string,label:string}|null $action
 */
function priniti_empty_state( string $icon, string $title, string $text = '', ?array $action = null, string $class = '' ): void {
	?>
	<div class="<?php echo esc_attr( priniti_cx( 'relative flex flex-col items-center gap-3 overflow-hidden rounded-[1.5rem] border border-dashed border-line bg-surface px-6 py-12 text-center', $class ) ); ?>">
		<?php priniti_decor( 'dots', '-left-4 -top-4 size-24 text-line' ); ?>
		<?php priniti_decor( 'sparkle', 'right-[18%] top-8 size-4 text-brand/40' ); ?>
		<span class="relative flex size-14 items-center justify-center rounded-full bg-brand-tint text-brand"><?php priniti_the_icon( $icon, 'size-6' ); ?></span>
		<p class="relative font-display text-lg font-semibold"><?php echo esc_html( $title ); ?></p>
		<?php if ( $text ) : ?>
			<p class="relative max-w-md text-sm text-ink-soft sm:text-base"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<?php
		if ( $action ) {
			priniti_button_link( $action['href'], esc_html( $action['label'] ), 'outline', 'md', 'relative mt-2' );
		}
		?>
	</div>
	<?php
}
