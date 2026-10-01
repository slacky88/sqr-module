<?php
/**
 * WooCommerce > Settings > Advanced > Recesso.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Settings.
 */
class Siquis_Recesso_Settings {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_sections_advanced', array( __CLASS__, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( __CLASS__, 'add_settings' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( SIQUIS_RECESSO_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add section.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function add_section( $sections ) {
		$sections['siquis_recesso'] = __( 'Recesso (art. 54-bis)', 'siquis-recesso' );
		return $sections;
	}

	/**
	 * Settings fields.
	 *
	 * @param array  $settings        Settings.
	 * @param string $current_section Current section.
	 * @return array
	 */
	public static function add_settings( $settings, $current_section ) {
		if ( 'siquis_recesso' !== $current_section ) {
			return $settings;
		}

		$url = Siquis_Recesso_Activator::get_withdrawal_url();
		if ( '' !== $url ) {
			$desc = sprintf(
				/* translators: %s: withdrawal page URL. */
				esc_html__( 'Pagina di recesso attiva: %s', 'siquis-recesso' ),
				'<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $url ) . '</a>'
			);
		} else {
			$desc = '<strong>' . esc_html__( 'La funzione di recesso di WooCommerce è disattivata.', 'siquis-recesso' ) . '</strong> '
				. '<a href="' . esc_url( self::features_url() ) . '">' . esc_html__( 'Attivala nelle funzionalità di WooCommerce', 'siquis-recesso' ) . '</a>.';
		}

		return array(
			array(
				'title' => __( 'Recesso (art. 54-bis Codice del Consumo)', 'siquis-recesso' ),
				'type'  => 'title',
				'desc'  => $desc,
				'id'    => 'siquis_recesso_options',
			),
			array(
				'title'   => __( 'Testo del link', 'siquis-recesso' ),
				'id'      => 'siquis_recesso_label',
				'type'    => 'text',
				'default' => __( 'Recedere dal contratto qui', 'siquis-recesso' ),
				'css'     => 'min-width:300px;',
			),
			array(
				'title'   => __( 'Link nel footer', 'siquis-recesso' ),
				'desc'    => __( 'Mostra il link in fondo a tutte le pagine del sito, sopra eventuali barre fisse (carrello, chat, cookie)', 'siquis-recesso' ),
				'id'      => 'siquis_recesso_footer',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'    => __( 'Aggiungi anche al menu', 'siquis-recesso' ),
				'desc'     => __( 'Inserisce il link come ultima voce del menu scelto (es. il menu del footer del tema), con lo stile del tema.', 'siquis-recesso' ),
				'desc_tip' => true,
				'id'       => 'siquis_recesso_menu_location',
				'type'     => 'select',
				'default'  => '',
				'options'  => array( '' => __( '— Nessun menu —', 'siquis-recesso' ) ) + get_registered_nav_menus(),
			),
			array(
				'title'   => __( 'Link nelle email', 'siquis-recesso' ),
				'desc'    => __( 'Aggiungi il link alle email di ordine inviate al cliente', 'siquis-recesso' ),
				'id'      => 'siquis_recesso_email',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Correggi etichette', 'siquis-recesso' ),
				'desc'    => __( 'In italiano usa "Conferma recesso" al posto di "Conferma il ritiro"', 'siquis-recesso' ),
				'id'      => 'siquis_recesso_fix_labels',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'             => __( 'Giorni per il recesso', 'siquis-recesso' ),
				'desc'              => __( 'Il link nell\'area account e nella pagina ordine resta visibile per questo numero di giorni (minimo 14).', 'siquis-recesso' ),
				'desc_tip'          => true,
				'id'                => 'siquis_recesso_window_days',
				'type'              => 'number',
				'default'           => '30',
				'custom_attributes' => array(
					'min'  => '14',
					'step' => '1',
				),
				'css'               => 'width:80px;',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'siquis_recesso_options',
			),
		);
	}

	/**
	 * Plugins list link.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=advanced&section=siquis_recesso' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Impostazioni', 'siquis-recesso' ) . '</a>' );
		return $links;
	}

	/**
	 * WooCommerce features screen URL.
	 *
	 * @return string
	 */
	public static function features_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=advanced&section=features' );
	}
}
