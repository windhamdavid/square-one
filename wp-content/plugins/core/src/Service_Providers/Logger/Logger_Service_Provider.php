<?php
namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Logger\Error_Log;

class Error_Log_Service_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$this->register_logs( $container );
	}

	public function register_logs( Container $container ) {
		$container[ 'logger.error-log' ] = function ( Container $container ) {
			return new Error_Log();
		};
		add_action( 'init', function() use ( $container ) {
			$container[ 'logger.error-log' ]->init();
		} );
	}

}