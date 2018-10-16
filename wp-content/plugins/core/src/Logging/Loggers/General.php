<?php
namespace Tribe\Project\Logging\Loggers;

use Monolog\Logger;
use Tribe\Project\Logging\Contracts\Logger as Tribe_Logger;

class General extends Tribe_Logger {

	const CHANNEL = 'general';

	public function get_logger() : Logger {
		return new Logger( self::CHANNEL );
	}

	public function log() {
		//$messages = apply_filters( 'tribe/project/logging/general/messages', [], $log_level,  );

		if ( ! empty( $messages ) ) {

		}
	}
}