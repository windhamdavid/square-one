<?php
namespace Tribe\Project\Logger;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;

class Error_Log extends Base_Logger {

	const NAME = 'error_log';

	const ERROR_LOG_LOCATION = WP_CONTENT_DIR;
	const ERROR_LOG_NAME = '/debug.log';

	/**
	 * Registers a handler to log to error_log. This is irrelevant to whether `WP_DEBUG` constants are set as we won't always
	 * be checking for errors.
	 *
	 * @param bool $debug_level
	 */
	public function register_logger( $debug_level = false ) {

		if( empty( $debug_level ) ) {
			if( defined( 'ENVIRONMENT' ) && 'PRODUCTION' === ENVIRONMENT ) {
				$debug_level = Logger::CRITICAL;
			} else {
				$debug_level = Logger::DEBUG;
			}
		}

		$logger = new Logger( self::ERROR_LOG_LOCATION . self::ERROR_LOG_NAME );
		$logger->pushHandler( new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $debug_level ) );

		if( defined( 'TRIBE_LOG_FIREPHP_ENABLED' ) && false !== TRIBE_LOG_FIREPHP_ENABLED ) {
			$logger->pushHandler( new FirePHPHandler( ) );
		}
	}

	public function get_label() {
		return __( 'Error Log', 'tribe' );
	}
}