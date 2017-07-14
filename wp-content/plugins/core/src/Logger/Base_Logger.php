<?php
namespace Tribe\Project\Logger;

use Tribe\Libs\ACF\Group;

class Base_Logger implements TribeLogInstance {

	/**
	 * Return a translatable string for the settings page in the child class
	 */
	public function get_label() {}

	/**
	 * Every connector must register itself
	 */
	public function register_logger() {}

	/**
	 * Every connector must supply an ACF Group of settings.
	 * @param Group $group
	 */
	function get_acf_settings_group( Group $group ) {}

	/**
	 * @param string $error_level
	 * @param string $message
	 * @param array $context
	 */
	function log( string $error_level, string $message, array $context = [] ) {
		$this->log( $error_level, $message, $context );
	}
}