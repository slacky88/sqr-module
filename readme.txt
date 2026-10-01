=== Siquis Recesso per WooCommerce ===
Contributors: siquis
Tags: woocommerce, recesso, consumatori, withdrawal, art. 54-bis
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Attiva e collega la funzione di recesso nativa di WooCommerce (art. 54-bis Codice del Consumo).

== Description ==

Dal 2026 i negozi online devono offrire una "funzione di recesso" con il pulsante "recedere dal contratto qui", sempre disponibile e ben visibile, e un passaggio di conferma "conferma recesso". WooCommerce 11.1+ include questa funzione, ma è disattivata di default e non è collegata da nessuna parte.

Questo plugin non reimplementa il modulo: lo attiva, lo collega e corregge le etichette italiane.

* Attiva la funzione nativa "order_withdrawal" all'attivazione del plugin.
* Link "Recedere dal contratto qui" nel footer, shortcode `[siquis_recesso_link label="" class=""]`.
* Pulsante nell'elenco ordini, nel dettaglio ordine e nella pagina di ringraziamento.
* Link nelle email al cliente (ordine in lavorazione, completato, in attesa, fattura).
* Precompilazione dei dati del modulo per i clienti connessi (mai per gli ospiti).
* Etichetta "Conferma recesso" al posto di "Conferma il ritiro" nelle installazioni in italiano.
* Controlli in Strumenti > Salute del sito.

Impostazioni: WooCommerce > Impostazioni > Avanzate > Recesso (art. 54-bis).

Requisiti: WordPress 6.0+, PHP 7.4+, WooCommerce 11.1+.

Fonti: https://woocommerce.com/document/customer-order-withdrawal/ - Direttiva (UE) 2023/2673 - D.Lgs. 209/2025, art. 54-bis Codice del Consumo.

== Frequently Asked Questions ==

= Garantisce la conformità? =

No, aiuta soltanto. Condizioni generali di vendita, informativa precontrattuale e gestione dei rimborsi restano responsabilità del commerciante.

= Come attivo gli aggiornamenti automatici? =

Sono già attivi: il plugin controlla le nuove versioni pubblicate sul repository GitHub configurato e le propone in Bacheca > Aggiornamenti. Per usare un altro repository definisci `SIQUIS_RECESSO_UPDATE_REPO` in wp-config.php o usa il filtro `siquis_recesso_update_repo`.

= Se disattivo la funzione in WooCommerce il plugin la riattiva? =

No. La funzione viene attivata una sola volta all'attivazione del plugin; se poi la disattivi, vedrai un avviso nell'area amministrativa.

= Il plugin rimuove la funzione di WooCommerce alla disinstallazione? =

No, vengono rimosse solo le opzioni del plugin.

== Changelog ==

= 1.0.0 =
* Prima versione.
