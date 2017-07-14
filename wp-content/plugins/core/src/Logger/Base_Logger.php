<?php
namespace Tribe\Project\Logger;

use Tribe\Libs\ACF\Group;

abstract class Base_Logger implements TribeLogInstance {

	/**
	 * @param string $message
	 * @param array $context
	 */
	function log( string $message, array $context = [] ) {
		$this->log( $message, $context );
	}
}