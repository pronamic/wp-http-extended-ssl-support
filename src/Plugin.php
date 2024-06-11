<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WpExtendedSslSupport
 */

namespace Pronamic\WpExtendedSslSupport;

/**
 * Plugin class
 */
final class Plugin {
	/**
	 * Instance.
	 * 
	 * @var self
	 */
	private static $instance;

	/**
	 * Bootstrap.
	 * 
	 * @retun void
	 */
	public static function bootstrap() {
		self::instance()->setup();
	}

	/**
	 * Instance.
	 * 
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'http_api_curl', [ $this, 'http_api_curl' ], 10, 2 );
	}

	/**
	 * HTTP API cURL.
	 * 
	 * @param resource $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array    $parsed_args The HTTP request arguments.
	 * @return void
	 */
	public function http_api_curl( $handle, $parsed_args ) {
		$this->set_ssl_certificate_blob_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_key_blob_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_key_password_option_if_needed( $handle, $parsed_args );
	}

	/**
	 * Set SSL certificate password option if needed.
	 * 
	 * @param resource $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array    $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_certificate_blob_option_if_needed( $handle, $parsed_args ) {
		/**
		* Curl blob option.
		*
		* @link https://github.com/php/php-src/blob/php-8.1.0/ext/curl/interface.c#L2935-L2955
		*/
		if ( \array_key_exists( 'ssl_certificate_blob', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_SSLCERT_BLOB, $parsed_args['ssl_certificate_blob'] );
		}
	}

	/**
	 * Set SSL key blob option if needed.
	 * 
	 * @param resource $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array    $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_key_blob_option_if_needed( $handle, $parsed_args ) {
		if ( \array_key_exists( 'ssl_key_blob', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_SSLKEY_BLOB, $parsed_args['ssl_key_blob'] );
		}
	}

	/**
	 * Set SSL key password option if needed.
	 * 
	 * @param resource $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array    $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_key_password_option_if_needed( $handle, $parsed_args ) {
		if ( \array_key_exists( 'ssl_key_password', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_SSLKEYPASSWD, $parsed_args['ssl_key_password'] );
		}
	}
}
