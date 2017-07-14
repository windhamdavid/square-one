<?php
namespace Tribe\Project\Logger;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;
use Tribe\Libs\ACF\Field;
use Tribe\Libs\ACF\Group;

class Error_Log extends Base_Logger {

	const NAME = 'error_log';
	const LOG_ERRORS = 'log-errors';
	const PHP_ERROR_LEVEL = 'php-error-level';

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

	public function get_acf_settings_group( Group $group ) {
		$field = new Field( self::LOG_ERRORS );
		$field->set_attributes( [
			'label'         => $this->get_label(),
			'name'          => self::LOG_ERRORS,
			'type'          => 'true_false',
			'instructions'  => __( 'Select true to log errors to the error log. You can manually log any other activities as well.', 'tribe' ),
		] );
		$group->add_field( $field );

		$field = new Field( self::PHP_ERROR_LEVEL );
		$field->set_attributes( [
			'label'         => __( 'PHP Debug Level', 'tribe' ),
			'name'          => self::PHP_ERROR_LEVEL,
			'type'          => 'select',
			'choices' => array (
				Logger::DEBUG       => __( 'Debug', 'tribe' ),
				Logger::INFO        => __( 'Info', 'tribe' ),
				Logger::NOTICE      => __( 'Notice', 'tribe' ),
				Logger::WARNING     => __( 'Warning', 'tribe' ),
				Logger::ERROR       => __( 'Error', 'tribe' ),
				Logger::CRITICAL    => __( 'Critical', 'tribe' ),
				Logger::ALERT       => __( 'Alert', 'tribe' ),
				Logger::EMERGENCY   => __( 'Emergency', 'tribe' )
			),
			'default_value' => [
				0   => Logger::ERROR
			],
			'instructions' => __( 'Be careful with this setting in production', 'tribe' )
		] );
		$group->add_field( $field );

		return $group->get_attributes();
	}

	public function get_label() {
		return __( 'Error Log', 'tribe' );
	}
}