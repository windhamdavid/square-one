<?php
/**
 * Tribe\Media autoloader.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */


/**
 * The autoloader closure. Locates files in the Tribe\Media namespace in the 'src' folder.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register( function ( $class ) {

    $prefix   = 'Tribe\\Media\\';
    $base_dir = __DIR__ . '/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );

    $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );