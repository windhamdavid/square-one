<?php
namespace Tribe\Project\Service_Providers\Logger;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Logger\Error_Log;

class Logger_Service_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$container[ 'logger.error-log' ] = function ( Container $container ) {
			return new Error_Log();
		};
		add_action( 'init', function() use ( $container ) {
			$container[ 'logger.error-log' ]->register_logger();
		} );	}
}