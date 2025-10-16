<?php
/**
 * Autoloader for Royal Storage Plugin
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

namespace RoyalStorage;

/**
 * Autoloader class for PSR-4 autoloading
 */
class Autoloader {

	/**
	 * Register the autoloader
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class The class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		// Only autoload classes in the RoyalStorage namespace.
		if ( strpos( $class, 'RoyalStorage' ) !== 0 ) {
			return;
		}

		// Remove the namespace prefix.
		$class = str_replace( 'RoyalStorage\\', '', $class );

		// Convert namespace to file path.
		$file = ROYAL_STORAGE_DIR . 'includes/' . str_replace( '\\', '/', $class ) . '.php';
		$file = str_replace( '_', '-', $file );

		// Load the file if it exists.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

// Register the autoloader.
Autoloader::register();

