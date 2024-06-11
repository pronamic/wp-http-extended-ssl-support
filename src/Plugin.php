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
	private $instance;

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

	}
}
