<?php
namespace Tribe\Project\Service_Providers\Logger;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Logger\Admin;
use Tribe\Project\Logger\Error_Log;

class Logger_Service_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {

		$container[ 'logger.error-log' ] = function( Container $container ) {
			return new Error_Log();
		};

		add_action( 'init', function() use ( $container ) {
			$container[ 'logger.error-log' ]->register_logger();
		} );
		add_filter( 'tribe_logger_connections', function( $loggers ) use ( $container ) {
			$loggers[ 'error_log' ] = $container['logger.error-log' ];

			return $loggers;
		} );
	}
}