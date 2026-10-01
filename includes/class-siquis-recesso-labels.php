<?php
/**
 * Italian label corrections for the native WooCommerce strings.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Labels.
 */
class Siquis_Recesso_Labels {

	/**
	 * Overrides map (null until first use).
	 *
	 * @var array|null
	 */
	protected static $map = null;

	/**
	 * Register hooks.
	 */
	public static function init() {
		if ( 'no' === get_option( 'siquis_recesso_fix_labels', 'yes' ) ) {
			return;
		}
		add_filter( 'gettext_woocommerce', array( __CLASS__, 'filter' ), 10, 3 );
	}

	/**
	 * Replace translations.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original text.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public static function filter( $translation, $text, $domain = 'woocommerce' ) {
		if ( null === self::$map ) {
			self::$map = array();
			if ( 0 === strpos( (string) determine_locale(), 'it' ) ) {
				self::$map = (array) apply_filters(
					'siquis_recesso_label_overrides',
					self::get_default_map()
				);
			}
		}

		return isset( self::$map[ $text ] ) ? self::$map[ $text ] : $translation;
	}

	/**
	 * Withdrawal-specific WooCommerce msgids => Italian wording consistent with art. 54-bis.
	 * The official translation mixes "ritiro" and "annullamento"; generic strings are left untouched.
	 *
	 * @return array
	 */
	protected static function get_default_map() {
		return array(
			// Customer-facing form.
			'Withdraw from contract'                    => 'Recedere dal contratto',
			'Confirm withdrawal'                        => 'Conferma recesso',
			'Continue to review'                        => 'Continua e verifica',
			'Tell us if you want to withdraw from an order placed on this store. You do not need to give a reason.' => 'Compila il modulo per recedere da un ordine effettuato su questo negozio. Non è necessario indicare un motivo.',
			'Some items, like personalized products, may not be eligible. We review every request and reply by email.' => 'Alcuni prodotti, ad esempio quelli personalizzati, potrebbero essere esclusi dal diritto di recesso. Esaminiamo ogni richiesta e rispondiamo via email.',
			'Nothing has been sent yet. Your withdrawal is submitted when you select "Confirm withdrawal".' => 'Non è stato ancora inviato nulla. Il recesso viene inviato quando selezioni "Conferma recesso".',
			"After you confirm, we'll email an acknowledgment to %s with these details and the date and time of submission. Keep it as proof of your withdrawal." => "Dopo la conferma invieremo a %s una ricevuta via email con questi dati e la data e l'ora di invio. Conservala come prova del recesso.",
			'Your withdrawal has been submitted.'       => 'La tua dichiarazione di recesso è stata inviata.',
			"We've emailed an acknowledgment to %s with your details and the date and time of submission. Keep it as proof of your withdrawal." => "Abbiamo inviato a %s una ricevuta via email con i tuoi dati e la data e l'ora di invio. Conservala come prova del recesso.",
			'Order withdrawal progress'                 => 'Avanzamento del recesso',
			'Your details'                              => 'I tuoi dati', // Only used by the withdrawal form in PHP (WC 11.1).
			'What do you want to withdraw?'             => 'Da cosa vuoi recedere?',
			'Withdrawing'                               => 'Oggetto del recesso',
			'The full order'                            => "L'intero ordine",
			'Specific items only'                       => 'Solo alcuni articoli',
			'Choose what you want to withdraw.'         => 'Scegli da cosa vuoi recedere.',
			'List the specific items you want to withdraw.' => 'Elenca gli articoli per cui vuoi recedere.',
			'We could not submit your withdrawal request. Please try again or contact us if the problem continues.' => 'Non è stato possibile inviare la dichiarazione di recesso. Riprova o contattaci se il problema persiste.',
			'A withdrawal request has already been submitted for this order. Please contact us if you need help or want to make changes.' => 'Per questo ordine è già stata inviata una dichiarazione di recesso. Contattaci se hai bisogno di aiuto o vuoi apportare modifiche.',
			'Please wait before submitting another withdrawal request.' => "Attendi qualche secondo prima di inviare un'altra dichiarazione di recesso.",
			// Customer acknowledgment email.
			'We received your withdrawal request'       => 'Abbiamo ricevuto la tua dichiarazione di recesso',
			'We have received your request to withdraw from the order below.' => "Abbiamo ricevuto la tua dichiarazione di recesso relativa all'ordine qui sotto.",
			// Merchant side.
			'Order withdrawal'                          => 'Recesso dal contratto',
			'Endpoint for the order withdrawal page.'   => 'Endpoint della pagina di recesso.',
			'Order withdrawal request for order %s'     => "Dichiarazione di recesso per l'ordine %s",
			'Order withdrawal request received'         => 'Dichiarazione di recesso ricevuta',
			'A customer submitted an order withdrawal request.' => 'Un cliente ha inviato una dichiarazione di recesso.',
			'Order withdrawal request for #%s'          => "Dichiarazione di recesso per l'ordine #%s",
			'A customer submitted an order withdrawal request for order #%s. Review the matched order to confirm the request details.' => "Un cliente ha inviato una dichiarazione di recesso per l'ordine #%s. Controlla l'ordine collegato per verificarne i dettagli.",
			'Order withdrawal requested. Withdrawal type: %s.' => 'Recesso richiesto dal cliente. Oggetto: %s.',
			'This order is older than %1$d days. Only orders within %2$d days of delivery are eligible for withdrawal.' => "Quest'ordine è stato effettuato più di %1\$d giorni fa. Verifica la data di consegna: il termine di recesso è di %2\$d giorni dalla consegna.",
		);
	}
}
