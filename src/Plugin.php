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

use CurlHandle;

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
	 * Files.
	 * 
	 * @var array<string, string>
	 */
	private $files = [];

	/**
	 * Bootstrap.
	 * 
	 * @return void
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
		if ( \has_action( 'http_api_curl', [ $this, 'http_api_curl' ] ) ) {
			return;
		}

		\add_action( 'http_api_curl', [ $this, 'http_api_curl' ], 10, 2 );

		\add_action( 'shutdown', [ $this, 'shutdown' ] );
	}

	/**
	 * HTTP API cURL.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	public function http_api_curl( $handle, $parsed_args ) {
		$this->set_ssl_certificate_blob_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_certificate_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_key_blob_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_key_option_if_needed( $handle, $parsed_args );
		$this->set_ssl_key_password_option_if_needed( $handle, $parsed_args );
	}

	/**
	 * Shutdown.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/shutdown/
	 * @return void
	 */
	public function shutdown() {
		\array_map( 'unlink', $this->files );
	}

	/**
	 * Get temporary file.
	 * 
	 * @param string         $content  Content.
	 * @param Throwable|null $previous The previous exception used for the exception chaining.
	 * @return string
	 * @throws \Exception Throws an exception if creating a temporary file for the content fails.
	 */
	private function get_temporary_file( $content, $previous = null ) {
		$hash = \md5( $content );

		if ( ! \array_key_exists( $hash, $this->files ) ) {
			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_tempnam -- Recommended function `get_temp_dir()` is used.
			$file = \tempnam( \get_temp_dir(), 'pronamic_curl_ssl' );

			if ( false === $file ) {
				$exception = new \Exception( 'Failed to create a temporary file for SSL BLOB data.', 0, $previous );

				throw $exception;
			}

			// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_file_put_contents -- Allowed when using temporary file.
			$result = \file_put_contents( $file, $content );

			if ( false === $result ) {
				$exception = new \Exception( 'Failed to write SSL BLOB data to temporary file.', 0, $previous );

				throw $exception;
			}

			$this->files[ $hash ] = $result;
		}

		return $this->files[ $hash ];
	}

	/**
	 * Set SSL certificate BLOB option if needed.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_certificate_blob_option_if_needed( $handle, $parsed_args ) {
		/**
		* Curl blob option.
		*
		* @link https://github.com/php/php-src/blob/php-8.1.0/ext/curl/interface.c#L2935-L2955
		*/
		if ( \array_key_exists( 'ssl_certificate_blob', $parsed_args ) ) {
			try {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
				\curl_setopt( $handle, \CURLOPT_SSLCERT_BLOB, $parsed_args['ssl_certificate_blob'] );
			} catch ( \ValueError $error ) {
				/**
				 * Not all TLS backends support BLOB, therefore we fall back on a SSL certificate file.
				 * 
				 * @link https://curl.se/libcurl/c/tls-options.html
				 * @link https://github.com/pronamic/wp-http-extended-ssl-support/issues/1
				 */
				$parsed_args['ssl_certificate'] = $this->get_temporary_file( $parsed_args['ssl_certificate_blob'], $error );

				$this->set_ssl_certificate_option_if_needed( $handle, $parsed_args );
			}
		}
	}

	/**
	 * Set SSL certificate option if needed.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_certificate_option_if_needed( $handle, $parsed_args ) {
		/**
		* Curl blob option.
		*
		* @link https://github.com/php/php-src/blob/php-8.1.0/ext/curl/interface.c#L2935-L2955
		*/
		if ( \array_key_exists( 'ssl_certificate', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_SSLCERT, $parsed_args['ssl_certificate'] );
		}
	}

	/**
	 * Set SSL key blob option if needed.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_key_blob_option_if_needed( $handle, $parsed_args ) {
		if ( \array_key_exists( 'ssl_key_blob', $parsed_args ) ) {
			try {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
				\curl_setopt( $handle, \CURLOPT_SSLKEY_BLOB, $parsed_args['ssl_key_blob'] );
			} catch ( \ValueError $error ) {                
				$parsed_args['ssl_key'] = $this->get_temporary_file( $parsed_args['ssl_key_blob'], $error );

				$this->set_ssl_key_option_if_needed( $handle, $parsed_args );
			}
		}
	}

	/**
	 * Set SSL key option if needed.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_key_option_if_needed( $handle, $parsed_args ) {
		if ( \array_key_exists( 'ssl_key', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_SSLKEY, $parsed_args['ssl_key'] );
		}
	}

	/**
	 * Set SSL key password option if needed.
	 * 
	 * @param CurlHandle            $handle      The cURL handle returned by curl_init() (passed by reference).
	 * @param array<string, string> $parsed_args The HTTP request arguments.
	 * @return void
	 */
	private function set_ssl_key_password_option_if_needed( $handle, $parsed_args ) {
		if ( \array_key_exists( 'ssl_key_password', $parsed_args ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- WordPress requests library does not support this yet.
			\curl_setopt( $handle, \CURLOPT_KEYPASSWD, $parsed_args['ssl_key_password'] );
		}
	}
}
