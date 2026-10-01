<?php
/**
 * Activation, feature enabling and shared helpers.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Activator.
 */
class Siquis_Recesso_Activator {

	const FEATURE_ID   = 'order_withdrawal';
	const ENDPOINT_KEY = 'order-withdrawal';

	/**
	 * Hook runtime behaviour.
	 */
	public static function init() {
		add_action( 'woocommerce_init', array( __CLASS__, 'maybe_enable_feature' ), 20 );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ), 999 );
	}

	/**
	 * Activation callback: only sets flags, WC is not ready yet.
	 */
	public static function activate() {
		update_option( 'siquis_recesso_needs_enable', 'yes' );
		update_option( 'siquis_recesso_flush', 'yes' );
	}

	/**
	 * Enable the native feature once, right after activation.
	 */
	public static function maybe_enable_feature() {
		if ( 'yes' !== get_option( 'siquis_recesso_needs_enable' ) ) {
			return;
		}

		if ( ! self::is_feature_enabled() ) {
			$controller = self::get_controller();
			if ( $controller && method_exists( $controller, 'change_feature_enable' ) ) {
				$controller->change_feature_enable( self::FEATURE_ID, true );
				update_option( 'siquis_recesso_flush', 'yes' );
			}
		}

		delete_option( 'siquis_recesso_needs_enable' );
	}

	/**
	 * Flush rewrite rules once endpoints are registered.
	 */
	public static function maybe_flush() {
		if ( 'yes' === get_option( 'siquis_recesso_flush' ) ) {
			flush_rewrite_rules( false );
			delete_option( 'siquis_recesso_flush' );
		}
	}

	/**
	 * Get the WC features controller, if available.
	 *
	 * @return object|null
	 */
	protected static function get_controller() {
		$class = '\Automattic\WooCommerce\Internal\Features\FeaturesController';
		if ( ! function_exists( 'wc_get_container' ) || ! class_exists( $class ) ) {
			return null;
		}
		try {
			return wc_get_container()->get( $class );
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Whether the native withdrawal feature is enabled.
	 *
	 * @return bool
	 */
	public static function is_feature_enabled() {
		try {
			$controller = self::get_controller();
			if ( $controller && method_exists( $controller, 'feature_is_enabled' ) ) {
				return (bool) $controller->feature_is_enabled( self::FEATURE_ID );
			}
			$util = '\Automattic\WooCommerce\Utilities\FeaturesUtil';
			if ( class_exists( $util ) && method_exists( $util, 'feature_is_enabled' ) ) {
				return (bool) $util::feature_is_enabled( self::FEATURE_ID );
			}
		} catch ( \Throwable $e ) {
			return false;
		}
		return false;
	}

	/**
	 * Withdrawal endpoint slug ('' if unavailable).
	 *
	 * @return string
	 */
	public static function get_endpoint_slug() {
		return sanitize_title( (string) get_option( 'woocommerce_myaccount_order_withdrawal_endpoint', 'withdraw-order' ) );
	}

	/**
	 * Endpoint reference for WC helpers: the registered query var key, or the slug as fallback.
	 *
	 * @return string
	 */
	public static function get_endpoint_ref() {
		if ( function_exists( 'WC' ) && isset( WC()->query ) && is_callable( array( WC()->query, 'get_query_vars' ) ) ) {
			$vars = WC()->query->get_query_vars();
			if ( is_array( $vars ) && ! empty( $vars[ self::ENDPOINT_KEY ] ) ) {
				return self::ENDPOINT_KEY;
			}
		}
		return self::get_endpoint_slug();
	}

	/**
	 * Withdrawal page URL, optionally tied to an order.
	 *
	 * @param WC_Order|null $order Order.
	 * @return string
	 */
	public static function get_withdrawal_url( $order = null ) {
		if ( ! self::is_feature_enabled() || ! function_exists( 'wc_get_account_endpoint_url' ) ) {
			return '';
		}
		$slug = self::get_endpoint_slug();
		if ( '' === $slug ) {
			return '';
		}
		$url = wc_get_account_endpoint_url( self::get_endpoint_ref() );
		if ( ! $url ) {
			return '';
		}
		if ( $order instanceof WC_Order ) {
			$url = add_query_arg( 'siquis_order', $order->get_id(), $url );
		}
		return $url;
	}
}
