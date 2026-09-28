<?php
namespace GutenStyle\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Autoloader {
	public static function register(): void {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	private static function autoload( string $class ): void {
		$prefix = 'GutenStyle\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$path     = GUTENSTYLE_PATH . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}
