<?php
/**
 * Links to the native WooCommerce withdrawal page.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Links.
 */
class Siquis_Recesso_Links {

	/**
	 * Order IDs already printed on this request.
	 *
	 * @var array
	 */
	protected static $printed = array();

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render_footer' ), 5 );
		add_shortcode( 'siquis_recesso_link', array( __CLASS__, 'shortcode' ) );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'menu_item' ), 10, 2 );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( __CLASS__, 'orders_list_action' ), 10, 2 );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'render_order_block' ) );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'render_thankyou' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_link' ), 10, 4 );
	}

	/**
	 * Link label.
	 *
	 * @return string
	 */
	public static function get_label() {
		$label = trim( (string) get_option( 'siquis_recesso_label', '' ) );
		return '' !== $label ? $label : __( 'Recedere dal contratto qui', 'siquis-recesso' );
	}

	/**
	 * Front-end assets.
	 */
	public static function enqueue() {
		if ( is_admin() || '' === Siquis_Recesso_Activator::get_withdrawal_url() ) {
			return;
		}

		wp_enqueue_style( 'siquis-recesso', SIQUIS_RECESSO_URL . 'assets/siquis-recesso.css', array(), SIQUIS_RECESSO_VERSION );

		if ( 'no' !== get_option( 'siquis_recesso_footer', 'yes' ) ) {
			wp_enqueue_script( 'siquis-recesso-footer', SIQUIS_RECESSO_URL . 'assets/siquis-recesso-footer.js', array(), SIQUIS_RECESSO_VERSION, true );
		}

		$data = self::get_prefill_data();
		if ( $data ) {
			wp_enqueue_script( 'siquis-recesso-prefill', SIQUIS_RECESSO_URL . 'assets/prefill.js', array(), SIQUIS_RECESSO_VERSION, true );
			wp_add_inline_script( 'siquis-recesso-prefill', 'window.siquisRecessoPrefill = ' . wp_json_encode( $data ) . ';', 'before' );
		}
	}

	/**
	 * Fields to prefill for a logged-in customer, or empty array.
	 *
	 * @return array
	 */
	protected static function get_prefill_data() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_user_logged_in() || empty( $_GET['siquis_order'] ) || ! function_exists( 'is_wc_endpoint_url' ) ) {
			return array();
		}

		$ref = Siquis_Recesso_Activator::get_endpoint_ref();
		if ( '' === $ref || ! is_wc_endpoint_url( $ref ) ) {
			return array();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = wc_get_order( absint( wp_unslash( $_GET['siquis_order'] ) ) );
		if ( ! $order instanceof WC_Order || (int) $order->get_customer_id() !== get_current_user_id() ) {
			return array();
		}

		$email = $order->get_billing_email();

		return array(
			'order_withdrawal_first_name'         => $order->get_billing_first_name(),
			'order_withdrawal_last_name'          => $order->get_billing_last_name(),
			'order_withdrawal_email'              => $email,
			'order_withdrawal_email_confirmation' => $email,
			'order_withdrawal_order_number'       => $order->get_order_number(),
		);
	}

	/**
	 * Footer link.
	 */
	public static function render_footer() {
		if ( is_admin() || 'no' === get_option( 'siquis_recesso_footer', 'yes' ) ) {
			return;
		}
		$url = Siquis_Recesso_Activator::get_withdrawal_url();
		if ( '' === $url ) {
			return;
		}
		printf(
			'<div class="siquis-recesso-footer"><a href="%s">%s</a></div>',
			esc_url( $url ),
			esc_html( self::get_label() )
		);
	}

	/**
	 * Append the link to the theme menu location chosen in the settings.
	 *
	 * @param string $items Menu items HTML.
	 * @param object $args  wp_nav_menu() arguments.
	 * @return string
	 */
	public static function menu_item( $items, $args ) {
		$location = (string) get_option( 'siquis_recesso_menu_location', '' );
		if ( '' === $location || ! is_object( $args ) || empty( $args->theme_location ) || $location !== $args->theme_location ) {
			return $items;
		}
		$url = Siquis_Recesso_Activator::get_withdrawal_url();
		if ( '' === $url ) {
			return $items;
		}
		return $items . sprintf(
			'<li class="menu-item siquis-recesso-menu-item"><a href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( self::get_label() )
		);
	}

	/**
	 * Shortcode [siquis_recesso_link label="" class=""].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'label' => '',
				'class' => '',
			),
			$atts,
			'siquis_recesso_link'
		);

		$url = Siquis_Recesso_Activator::get_withdrawal_url();
		if ( '' === $url ) {
			return '';
		}

		$classes = 'siquis-recesso-link';
		foreach ( preg_split( '/\s+/', trim( $atts['class'] ) ) as $class ) {
			if ( '' !== $class ) {
				$classes .= ' ' . sanitize_html_class( $class );
			}
		}

		return sprintf(
			'<a class="%s" href="%s">%s</a>',
			esc_attr( $classes ),
			esc_url( $url ),
			esc_html( '' !== trim( $atts['label'] ) ? $atts['label'] : self::get_label() )
		);
	}

	/**
	 * Whether the order can still be withdrawn from.
	 *
	 * @param mixed $order Order.
	 * @return bool
	 */
	public static function is_eligible( $order ) {
		$eligible = false;

		if ( $order instanceof WC_Order && 'yes' !== $order->get_meta( '_order_withdrawal_requested' ) ) {
			$blocked = apply_filters( 'siquis_recesso_blocked_statuses', array( 'failed', 'pending', 'cancelled', 'refunded', 'checkout-draft' ) );
			if ( ! in_array( $order->get_status(), (array) $blocked, true ) ) {
				$ref = $order->get_date_completed() ? $order->get_date_completed() : $order->get_date_created();
				$days = max( 14, (int) get_option( 'siquis_recesso_window_days', 30 ) );
				if ( $ref && time() <= $ref->getTimestamp() + $days * DAY_IN_SECONDS ) {
					$eligible = true;
				}
			}
		}

		return (bool) apply_filters( 'siquis_recesso_order_is_eligible', $eligible, $order );
	}

	/**
	 * My Account orders list action.
	 *
	 * @param array $actions Actions.
	 * @param mixed $order   Order.
	 * @return array
	 */
	public static function orders_list_action( $actions, $order ) {
		if ( ! is_array( $actions ) || ! self::is_eligible( $order ) ) {
			return $actions;
		}
		$url = Siquis_Recesso_Activator::get_withdrawal_url( $order );
		if ( '' !== $url ) {
			$actions['siquis-recesso'] = array(
				'url'  => $url,
				'name' => self::get_label(),
			);
		}
		return $actions;
	}

	/**
	 * Order details block (button or "already requested" note).
	 *
	 * @param mixed $order Order.
	 */
	public static function render_order_block( $order ) {
		if ( ! $order instanceof WC_Order || isset( self::$printed[ $order->get_id() ] ) ) {
			return;
		}

		if ( 'yes' === $order->get_meta( '_order_withdrawal_requested' ) ) {
			self::$printed[ $order->get_id() ] = true;
			echo '<p class="siquis-recesso-order">' . esc_html__( 'Richiesta di recesso già inviata per questo ordine.', 'siquis-recesso' ) . '</p>';
			return;
		}

		if ( ! self::is_eligible( $order ) ) {
			return;
		}
		$url = Siquis_Recesso_Activator::get_withdrawal_url( $order );
		if ( '' === $url ) {
			return;
		}

		self::$printed[ $order->get_id() ] = true;
		printf(
			'<p class="siquis-recesso-order"><a class="button" href="%s">%s</a></p>',
			esc_url( $url ),
			esc_html( self::get_label() )
		);
	}

	/**
	 * Thank-you page.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function render_thankyou( $order_id ) {
		if ( function_exists( 'wc_get_order' ) ) {
			self::render_order_block( wc_get_order( $order_id ) );
		}
	}

	/**
	 * Link in customer emails.
	 *
	 * @param mixed $order         Order.
	 * @param bool  $sent_to_admin Sent to admin.
	 * @param bool  $plain_text    Plain text.
	 * @param mixed $email         Email object.
	 */
	public static function email_link( $order, $sent_to_admin = false, $plain_text = false, $email = null ) {
		if ( $sent_to_admin || 'no' === get_option( 'siquis_recesso_email', 'yes' ) || ! $order instanceof WC_Order || ! is_object( $email ) || empty( $email->id ) ) {
			return;
		}

		$ids = apply_filters( 'siquis_recesso_email_ids', array( 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order', 'customer_invoice' ) );
		if ( ! in_array( $email->id, (array) $ids, true ) ) {
			return;
		}

		$url = Siquis_Recesso_Activator::get_withdrawal_url( $order );
		if ( '' === $url ) {
			return;
		}

		if ( $plain_text ) {
			echo "\n" . esc_html( self::get_label() ) . ': ' . esc_url_raw( $url ) . "\n";
			return;
		}

		printf(
			'<p style="margin:16px 0;">%s <a href="%s">%s</a></p>',
			esc_html__( 'Puoi esercitare il diritto di recesso entro i termini di legge:', 'siquis-recesso' ),
			esc_url( $url ),
			esc_html( self::get_label() )
		);
	}
}
