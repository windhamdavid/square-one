<?php


namespace Tribe\Project\Service_Providers;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Settings;

class Settings_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$this->register_pages( $container );
	}

	public function register_pages( Container $container ) {
		$container[ 'settings.general' ] = function ( Container $container ) {
			return new Settings\General();
		};
		$container[ 'settings.logger-admin'] = function( Container $container ) {
			return new Settings\Logger_Settings();
		};

		add_action( 'init', function () use ( $container ) {
			$container[ 'settings.general' ]->hook();
		}, 0, 0 );

		add_action( 'init', function() use ( $container ) {
			$container[ 'settings.logger-admin']->hook();
		}, 0, 0  );
	}
}
