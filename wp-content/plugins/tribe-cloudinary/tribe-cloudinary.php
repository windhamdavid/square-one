<?php
/*
Plugin Name: Tribe Cloudinary
Plugin URI: http://tri.be
Description: Use Cloudinary to handle all custom image sizes
Author: Modern Tribe, Inc.
Author URI: http://tri.be
Contributors: Mat Lipe
Text Domain: cloudinary
Version: 1.0
*/

/**
 * Auto load class files based on namespaced folders
 *
 * @return void
 */
if( !function_exists( 'cloudinary_autoload' ) ){
	function cloudinary_autoload( $class ){
		$parts = explode('\\', $class);
		if ( $parts[0] == 'Cloudinary' && !empty( $parts[ 1 ] ) ){
			$class = $parts[ 1 ];
		}
		if( file_exists( dirname(__FILE__).'/classes/' . $class .'.php' ) ){
			require( dirname(__FILE__).'/classes/' . $class .'.php' );
		} elseif( file_exists( dirname(__FILE__).'/vendor/cloudinary-api/' . $class .'.php' ) ){
			require( dirname(__FILE__).'/vendor/cloudinary-api/' . $class .'.php' );
		}
	}
}


/**
 * Load all the plugin files and initialize appropriately
 *
 * @return void
 */
if ( !function_exists('cloudinary_load') ) { // play nice
	function cloudinary_load() {
		spl_autoload_register( 'cloudinary_autoload' );
		add_action( 'plugins_loaded', array( 'Tribe_Cloudinary',    'init' ) );
		add_action( 'plugins_loaded', array( 'Cloudinary_Settings', 'init' ) );
	}

	cloudinary_load();
}
