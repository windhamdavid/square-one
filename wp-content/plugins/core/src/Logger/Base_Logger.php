<?php
namespace Tribe\Project\Logger;

class Base_Logger implements TribeLogInstance {

	function register_logger() {}

	/**
	 * @param string $error_level
	 * @param string $message
	 * @param array $context
	 */
	function log( string $error_level, string $message, array $context = [] ) {
		$this->log( $error_level, $message, $context );
	}
}