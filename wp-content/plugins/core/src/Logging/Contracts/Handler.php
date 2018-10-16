<?php
namespace Tribe\Project\Logging\Contracts;

use Monolog\Handler\BrowserConsoleHandler;

class Handler {

	public function register() {

		/**
		 * Numeric value representing the Monolog log level.
		 *
		 * @see https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels
		 *
		 * @param int $log_level
		 */
		$log_level = apply_filters( 'tribe/project/logging/console/log_level', \Monolog\Logger::DEBUG );

		return new BrowserConsoleHandler( $log_level );
	}
}