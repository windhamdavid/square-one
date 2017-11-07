<?php
/**
 * Plugin Name: Tribe Parsedown
 * Description: Parse markdown docs for output as HTML on a site.
 * Author: Modern Tribe, Inc.
 * Author URI: http://tri.be
 * Version: 1.0
/*

/**
 * Load all the plugin files and initialize appropriately
 *
 * @return void
 */
if ( ! function_exists( 'tribe_parsedown_load' ) ) {
	/**
	 * Play nice.
	 */
	function tribe_parsedown_load() {
		require_once 'Tribe_Parsedown.php';
		add_action( 'plugins_loaded', array( 'Tribe_Parsedown', 'init' ) );
	}

	tribe_parsedown_load();
}
