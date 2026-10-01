<?php
/**
 * Resilient download of plugin updates.
 *
 * Some hosts block outbound connections to github.com (and to the
 * *.githubusercontent.com hosts it redirects to) while allowing api.github.com,
 * which the update check already uses. Each release is also published on the
 * "dist" branch, so the package can be fetched straight from api.github.com.
 *
 * @package Siquis_Recesso
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Siquis_Recesso_Download.
 */
class Siquis_Recesso_Download {

	/**
	 * GitHub "owner/repo" of the update repository.
	 *
	 * @var string
	 */
	protected static $repo = '';

	/**
	 * Register hooks.
	 *
	 * @param string $repo_url Repository URL, e.g. https://github.com/owner/repo/.
	 */
	public static function init( $repo_url ) {
		if ( ! preg_match( '#github\.com/([^/]+/[^/]+?)/?$#', (string) $repo_url, $m ) ) {
			return;
		}
		self::$repo = $m[1];
		add_filter( 'upgrader_pre_download', array( __CLASS__, 'pre_download' ), 10, 2 );
		add_filter( 'http_request_args', array( __CLASS__, 'request_args' ), 10, 2 );
	}

	/**
	 * Package URL served by the contents API (no redirect to other hosts).
	 *
	 * @return string
	 */
	protected static function api_url() {
		return 'https://api.github.com/repos/' . self::$repo . '/contents/package.zip?ref=dist';
	}

	/**
	 * Whether a package URL belongs to this plugin's releases.
	 *
	 * @param string $package Package URL.
	 * @return bool
	 */
	protected static function is_ours( $package ) {
		return is_string( $package ) && 0 === strpos( $package, 'https://github.com/' . self::$repo . '/releases/download/' );
	}

	/**
	 * Try api.github.com first, then the original release URL.
	 *
	 * @param false|string|WP_Error $reply   Short-circuit value.
	 * @param string                $package Package URL.
	 * @return false|string|WP_Error Local file path on success.
	 */
	public static function pre_download( $reply, $package ) {
		if ( false !== $reply || ! self::is_ours( $package ) || ! function_exists( 'download_url' ) ) {
			return $reply;
		}

		$errors = array();
		foreach ( array( self::api_url(), $package ) as $url ) {
			$file = download_url( $url, 300 );
			if ( ! is_wp_error( $file ) ) {
				if ( self::is_zip( $file ) ) {
					return $file;
				}
				wp_delete_file( $file );
				$errors[] = $url . ': ' . __( 'il file scaricato non è un archivio ZIP valido', 'siquis-recesso' );
				continue;
			}
			$errors[] = $url . ': ' . $file->get_error_message();
		}

		return new WP_Error( 'siquis_recesso_download_failed', implode( ' | ', $errors ) );
	}

	/**
	 * Ask the contents API for the raw file instead of JSON.
	 *
	 * @param array  $args HTTP request args.
	 * @param string $url  Request URL.
	 * @return array
	 */
	public static function request_args( $args, $url ) {
		if ( self::api_url() === $url ) {
			$args['headers']           = isset( $args['headers'] ) && is_array( $args['headers'] ) ? $args['headers'] : array();
			$args['headers']['Accept'] = 'application/vnd.github.raw';
		}
		return $args;
	}

	/**
	 * Quick check of the ZIP signature.
	 *
	 * @param string $file Local file path.
	 * @return bool
	 */
	protected static function is_zip( $file ) {
		$handle = @fopen( $file, 'rb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return false;
		}
		$magic = fread( $handle, 4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return "PK\x03\x04" === $magic;
	}
}
