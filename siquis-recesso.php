<?php
/**
 * Plugin Name:       Siquis Recesso per WooCommerce
 * Description:       Attiva e collega la funzione di recesso nativa di WooCommerce (art. 54-bis Codice del Consumo): link "Recedere dal contratto qui" sempre visibile e pulsante "Conferma recesso".
 * Version:           1.0.3
 * Author:            Siquis
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 11.1
 * WC tested up to:   11.1.2
 * Text Domain:       siquis-recesso
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

define( 'SIQUIS_RECESSO_VERSION', '1.0.3' );
define( 'SIQUIS_RECESSO_FILE', __FILE__ );
define( 'SIQUIS_RECESSO_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIQUIS_RECESSO_URL', plugin_dir_url( __FILE__ ) );
define( 'SIQUIS_RECESSO_MIN_WC', '11.1' );

if ( ! defined( 'SIQUIS_RECESSO_UPDATE_REPO' ) ) {
	// Repository GitHub da cui arrivano gli aggiornamenti (sovrascrivibile in wp-config.php).
	define( 'SIQUIS_RECESSO_UPDATE_REPO', 'https://github.com/slacky88/sqr-module/' );
}

require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-health.php';

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) && method_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil', 'declare_compatibility' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SIQUIS_RECESSO_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', SIQUIS_RECESSO_FILE, true );
		}
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-activator.php';
		Siquis_Recesso_Activator::activate();
	}
);

/**
 * Whether WooCommerce is active and recent enough.
 *
 * @return bool
 */
function siquis_recesso_wc_ok() {
	return class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, SIQUIS_RECESSO_MIN_WC, '>=' );
}

add_action(
	'plugins_loaded',
	static function () {
		Siquis_Recesso_Health::init();

		if ( ! siquis_recesso_wc_ok() ) {
			add_action(
				'admin_notices',
				static function () {
					if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'manage_woocommerce' ) ) {
						return;
					}
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						sprintf(
							/* translators: %s: minimum WooCommerce version. */
							esc_html__( 'Siquis Recesso per WooCommerce richiede WooCommerce versione %s o superiore.', 'siquis-recesso' ),
							esc_html( SIQUIS_RECESSO_MIN_WC )
						)
					);
				}
			);
			return;
		}

		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-activator.php';
		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-links.php';
		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-labels.php';
		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-settings.php';

		Siquis_Recesso_Activator::init();
		Siquis_Recesso_Links::init();
		Siquis_Recesso_Labels::init();
		Siquis_Recesso_Settings::init();
	},
	20
);

/**
 * Optional GitHub auto-updater (Plugin Update Checker v5 in vendor/).
 */
add_action(
	'plugins_loaded',
	static function () {
		$repo = apply_filters( 'siquis_recesso_update_repo', SIQUIS_RECESSO_UPDATE_REPO );
		$lib  = SIQUIS_RECESSO_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

		if ( empty( $repo ) || ! is_string( $repo ) || ! file_exists( $lib ) ) {
			return;
		}

		require_once $lib;
		require_once SIQUIS_RECESSO_DIR . 'includes/class-siquis-recesso-download.php';
		Siquis_Recesso_Download::init( $repo );

		if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			return;
		}

		$checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker( $repo, SIQUIS_RECESSO_FILE, 'siquis-recesso' );

		if ( is_object( $checker ) && method_exists( $checker, 'getVcsApi' ) ) {
			$api = $checker->getVcsApi();
			if ( is_object( $api ) && method_exists( $api, 'enableReleaseAssets' ) ) {
				$api->enableReleaseAssets( '/^package\.zip$/' );
			}
		}
	},
	5
);
