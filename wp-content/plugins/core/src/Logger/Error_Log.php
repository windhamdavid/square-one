<?php
namespace Tribe\Project\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Error_Log extends Base_Logger {

	const ERROR_LOG_LOCATION = WP_CONTENT_DIR;
	const ERROR_LOG_NAME = '/debug.log';

	public function register_logger() {
		$debug_level = Logger::DEBUG;

		if( defined( 'ENVIRONMENT' ) && 'PRODUCTION' === ENVIRONMENT ) {
			$debug_level = Logger::CRITICAL;
		}

		$logger = new Logger( self::ERROR_LOG_LOCATION . self::ERROR_LOG_NAME );
		$logger->pushHandler( new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $debug_level ) );

		if( defined( 'TRIBE_LOG_FIREPHP_ENABLED' ) && false !== TRIBE_LOG_FIREPHP_ENABLED ) {
			$logger->pushHandler( new FirePHPHandler( ) );
		}
	}
}