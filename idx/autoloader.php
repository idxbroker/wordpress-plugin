<?php
namespace IDX;

// Class Autoloader for PSR-2 Compliance.
spl_autoload_register(
	function ( $class ) {

		// Project-specific namespace prefix.
		$prefix = 'IDX\\';

		// Base directory for the namespace prefix.
		$base_dir = __DIR__ . DIRECTORY_SEPARATOR;

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );
		$relative_class = str_replace( '_', '-', $relative_class );

		// Replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $base_dir . strtolower( str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);
