<?php
/**
 * Site Health tests and admin notice.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Health.
 */
class Siquis_Recesso_Health {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'site_status_tests', array( __CLASS__, 'add_tests' ) );
		add_action( 'admin_notices', array( __CLASS__, 'feature_notice' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
	}

	/**
	 * Features screen URL.
	 *
	 * @return string
	 */
	protected static function features_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=advanced&section=features' );
	}

	/**
	 * Whether WooCommerce is usable.
	 *
	 * @return bool
	 */
	protected static function wc_ok() {
		return function_exists( 'siquis_recesso_wc_ok' ) && siquis_recesso_wc_ok();
	}

	/**
	 * Whether the native feature is enabled (false if WC unavailable).
	 *
	 * @return bool
	 */
	protected static function feature_on() {
		return self::wc_ok() && class_exists( 'Siquis_Recesso_Activator' ) && Siquis_Recesso_Activator::is_feature_enabled();
	}

	/**
	 * Register direct tests.
	 *
	 * @param array $tests Tests.
	 * @return array
	 */
	public static function add_tests( $tests ) {
		$ids = array( 'wc_version', 'feature', 'endpoint', 'link', 'admin_email' );
		foreach ( $ids as $id ) {
			$tests['direct'][ 'siquis_recesso_' . $id ] = array(
				'label' => __( 'Recesso (art. 54-bis)', 'siquis-recesso' ) . ': ' . $id,
				'test'  => array( __CLASS__, 'test_' . $id ),
			);
		}

		$tests['async']['siquis_recesso_home'] = array(
			'label'             => __( 'Recesso (art. 54-bis)', 'siquis-recesso' ) . ': home',
			'test'              => rest_url( 'siquis-recesso/v1/home-check' ),
			'has_rest'          => true,
			'async_direct_test' => array( __CLASS__, 'test_home' ),
		);
		return $tests;
	}

	/**
	 * REST route used by the async Site Health test.
	 */
	public static function register_rest() {
		register_rest_route(
			'siquis-recesso/v1',
			'/home-check',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'test_home' ),
				'permission_callback' => static function () {
					return current_user_can( 'view_site_health_checks' );
				},
			)
		);
	}

	/**
	 * Test: the link really appears in the public home page HTML (as a visitor sees it).
	 * Catches themes that never call wp_footer() and caches/optimizers that strip it.
	 *
	 * @return array
	 */
	public static function test_home() {
		$response = wp_remote_get(
			add_query_arg( 'siquis_check', time(), home_url( '/' ) ),
			array(
				'timeout'   => 15,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				'headers'   => array( 'Cache-Control' => 'no-cache' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return self::result(
				'home',
				'recommended',
				__( 'Impossibile verificare il link di recesso nella home', 'siquis-recesso' ),
				esc_html__( 'Il sito non è riuscito a scaricare la propria home page (loopback). Controlla manualmente che il link "Recedere dal contratto qui" sia visibile.', 'siquis-recesso' )
			);
		}

		$body = (string) wp_remote_retrieve_body( $response );
		foreach ( array( 'siquis-recesso-footer', 'siquis-recesso-menu-item', 'siquis-recesso-link' ) as $marker ) {
			if ( false !== strpos( $body, $marker ) ) {
				return self::result( 'home', 'good', __( 'Il link di recesso è presente nella home page', 'siquis-recesso' ), esc_html__( 'Il link compare nella home page vista da un visitatore.', 'siquis-recesso' ) );
			}
		}

		return self::result(
			'home',
			'critical',
			__( 'Il link di recesso non compare nella home page', 'siquis-recesso' ),
			esc_html__( 'La home page vista da un visitatore non contiene il link. Possibili cause: il tema non chiama wp_footer(), una cache o un plugin di ottimizzazione lo rimuove, oppure il link nel footer è disattivato. Svuota la cache e, se serve, inserisci il link in un menu o con lo shortcode [siquis_recesso_link].', 'siquis-recesso' ),
			'<p><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=siquis_recesso' ) ) . '">' . esc_html__( 'Apri le impostazioni', 'siquis-recesso' ) . '</a></p>'
		);
	}

	/**
	 * Build a test result.
	 *
	 * @param string $id          Test suffix.
	 * @param string $status      good|recommended|critical.
	 * @param string $label       Label.
	 * @param string $description Description.
	 * @param string $actions     Actions HTML.
	 * @return array
	 */
	protected static function result( $id, $status, $label, $description, $actions = '' ) {
		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Recesso', 'siquis-recesso' ),
				'color' => 'blue',
			),
			'description' => '<p>' . $description . '</p>',
			'actions'     => $actions,
			'test'        => 'siquis_recesso_' . $id,
		);
	}

	/**
	 * Test: WooCommerce version.
	 *
	 * @return array
	 */
	public static function test_wc_version() {
		if ( self::wc_ok() ) {
			return self::result( 'wc_version', 'good', __( 'WooCommerce è compatibile con la funzione di recesso', 'siquis-recesso' ), esc_html__( 'La versione di WooCommerce supporta il recesso nativo.', 'siquis-recesso' ) );
		}
		return self::result(
			'wc_version',
			'critical',
			__( 'WooCommerce mancante o troppo vecchio per la funzione di recesso', 'siquis-recesso' ),
			sprintf(
				/* translators: %s: minimum WooCommerce version. */
				esc_html__( 'Serve WooCommerce attivo, versione %s o superiore.', 'siquis-recesso' ),
				esc_html( SIQUIS_RECESSO_MIN_WC )
			)
		);
	}

	/**
	 * Test: native feature enabled.
	 *
	 * @return array
	 */
	public static function test_feature() {
		if ( self::feature_on() ) {
			return self::result( 'feature', 'good', __( 'La funzione di recesso di WooCommerce è attiva', 'siquis-recesso' ), esc_html__( 'Il pulsante di recesso è disponibile ai clienti.', 'siquis-recesso' ) );
		}
		return self::result(
			'feature',
			'critical',
			__( 'La funzione di recesso di WooCommerce è disattivata', 'siquis-recesso' ),
			esc_html__( 'Il pulsante di recesso obbligatorio non è attivo.', 'siquis-recesso' ),
			'<p><a href="' . esc_url( self::features_url() ) . '">' . esc_html__( 'Apri le funzionalità di WooCommerce', 'siquis-recesso' ) . '</a></p>'
		);
	}

	/**
	 * Test: endpoint slug.
	 *
	 * @return array
	 */
	public static function test_endpoint() {
		$slug = ( self::wc_ok() && class_exists( 'Siquis_Recesso_Activator' ) ) ? Siquis_Recesso_Activator::get_endpoint_slug() : '';
		if ( '' !== $slug ) {
			return self::result( 'endpoint', 'good', __( 'L\'endpoint di recesso è configurato', 'siquis-recesso' ), esc_html( $slug ) );
		}
		return self::result(
			'endpoint',
			'critical',
			__( 'L\'endpoint di recesso è vuoto', 'siquis-recesso' ),
			esc_html__( 'Imposta un endpoint in WooCommerce > Impostazioni > Avanzate.', 'siquis-recesso' )
		);
	}

	/**
	 * Test: a link is shown somewhere.
	 *
	 * @return array
	 */
	public static function test_link() {
		$ok = 'no' !== get_option( 'siquis_recesso_footer', 'yes' ) || '' !== (string) get_option( 'siquis_recesso_menu_location', '' );

		if ( ! $ok ) {
			global $wpdb;
			$ok = (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('page','post') AND post_content LIKE %s LIMIT 1",
					'%' . $wpdb->esc_like( '[siquis_recesso_link' ) . '%'
				)
			);
		}

		if ( $ok ) {
			return self::result( 'link', 'good', __( 'Il link di recesso è visibile', 'siquis-recesso' ), esc_html__( 'Il link è presente nel footer o in una pagina tramite shortcode.', 'siquis-recesso' ) );
		}
		return self::result(
			'link',
			'recommended',
			__( 'Il link di recesso non è sempre visibile', 'siquis-recesso' ),
			esc_html__( 'Il link nel footer e la voce di menu sono disattivati e nessuna pagina usa lo shortcode [siquis_recesso_link].', 'siquis-recesso' ),
			'<p><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=siquis_recesso' ) ) . '">' . esc_html__( 'Apri le impostazioni', 'siquis-recesso' ) . '</a></p>'
		);
	}

	/**
	 * Test: admin email.
	 *
	 * @return array
	 */
	public static function test_admin_email() {
		$email = get_option( 'admin_email' );
		if ( is_email( $email ) ) {
			return self::result(
				'admin_email',
				'good',
				__( 'L\'email amministratore è valida', 'siquis-recesso' ),
				esc_html__( 'WooCommerce invia a questo indirizzo le notifiche delle richieste di recesso.', 'siquis-recesso' )
			);
		}
		return self::result(
			'admin_email',
			'critical',
			__( 'L\'email amministratore non è valida', 'siquis-recesso' ),
			esc_html__( 'WooCommerce invia a questo indirizzo le notifiche delle richieste di recesso: correggilo in Impostazioni > Generali.', 'siquis-recesso' ),
			'<p><a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Apri Impostazioni generali', 'siquis-recesso' ) . '</a></p>'
		);
	}

	/**
	 * Admin notice when the native feature is off.
	 */
	public static function feature_notice() {
		if ( ! self::wc_ok() || ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins', 'woocommerce_page_wc-settings' ), true ) ) {
			return;
		}
		if ( self::feature_on() ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'La funzione di recesso di WooCommerce è disattivata: il pulsante obbligatorio non è attivo.', 'siquis-recesso' ),
			esc_url( self::features_url() ),
			esc_html__( 'Attivala qui', 'siquis-recesso' )
		);
	}
}
