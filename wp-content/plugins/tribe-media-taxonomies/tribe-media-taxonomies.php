<?php
/**
 * Plugin Name: Media Taxonomies
 * Plugin URI: http://tri.be
 * Description: Improve support for taxononmies and relationships for attachments
 * Version: 2.0
 * Author: Daniel Dvorkin (fork of Ralf Hortt)
 * Author URI: http://tri.be
 */

require_once trailingslashit( __DIR__ ) . 'src/Autoload.php';

define( 'Tribe_Media_Path', __DIR__ . '/assets' );

add_action( 'plugins_loaded', function () {
	tribe_media_taxonomies()->init();
}, 1, 0 );

/**
 * Shorthand to get the instance of our main plugin class
 *
 * @return \Tribe\Media\Main
 */
function tribe_media_taxonomies() {
	return \Tribe\Media\Main::instance();
}