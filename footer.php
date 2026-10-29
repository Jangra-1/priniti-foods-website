<?php
/**
 * Site footer and the mount point for the interactive overlays
 * (CartDrawer, MobileNavigation, SearchModal and Toaster from reference/nextjs/app/layout.tsx).
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<?php priniti_part( 'layout/footer' ); ?>
<div id="priniti-overlays"></div>
<?php wp_footer(); ?>
</body>
</html>
