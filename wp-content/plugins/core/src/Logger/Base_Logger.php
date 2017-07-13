<?php
namespace Tribe\Project\Logger;

class Base_Logger implements TribeLogInstance {

	/**
	 * Return a translatable string for the settings page in the child class
	 */
	function get_label() {}

	/**
	 * Every connector must register itself
	 *
	 * @param $debug_level
	 */
	function register_logger( $debug_level ) {}

	/**
	 * @param string $error_level
	 * @param string $message
	 * @param array $context
	 */
	function log( string $error_level, string $message, array $context = [] ) {
		$this->log( $error_level, $message, $context );
	}
}