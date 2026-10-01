<?php
/**
 * Uninstall: remove plugin options. The WooCommerce feature is left untouched.
 *
 * @package Siquis_Recesso
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$siquis_recesso_options = array(
	'siquis_recesso_label',
	'siquis_recesso_footer',
	'siquis_recesso_email',
	'siquis_recesso_fix_labels',
	'siquis_recesso_window_days',
	'siquis_recesso_menu_location',
	'siquis_recesso_needs_enable',
	'siquis_recesso_flush',
);

foreach ( $siquis_recesso_options as $siquis_recesso_option ) {
	delete_option( $siquis_recesso_option );
}
