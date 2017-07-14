<?php
namespace Tribe\Project\Service_Providers\Logger;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Libs\ACF\Group;
use Tribe\Project\Logger\Admin;
use Tribe\Project\Logger\Error_Log;
use Tribe\Project\Logger\Slack;

class Logger_Service_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {

		$container[ 'logger.error-log' ] = function( Container $container ) {
			return new Error_Log();
		};
		$container[ 'logger.slack' ] = function( Container $container ) {
			return new Slack();
		};

		add_action( 'init', function() use ( $container ) {
			$container[ 'logger.error-log' ]->register_logger();
			$container[ 'logger.slack' ]->register_logger();
		} );
		add_filter( 'tribe_logger_connections', function( $loggers ) use ( $container ) {
			$loggers[ 'error_log' ] = $container[ 'logger.error-log' ];
			$loggers[ 'slack' ] = $container[ 'logger.slack' ];

			return $loggers;
		} );

		add_action( 'tribe_add_loggers', function( Group $group ) use ( $container ) {
			$container[ 'logger.error-log' ]->get_acf_settings_group( $group );
			$container[ 'logger.slack' ]->get_acf_settings_group( $group );
		} );
	}
}