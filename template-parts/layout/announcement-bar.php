<?php
/**
 * Announcement bar (components/layout/AnnouncementBar.tsx). Hidden until a confirmed message is configured.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

$announcement = priniti_site_config()['announcement'];
if ( ! $announcement ) {
	return;
}
?>
<div class="bg-ink px-4 py-1 text-center text-xs font-medium leading-5 text-white">
	<p><?php echo esc_html( $announcement ); ?></p>
</div>
