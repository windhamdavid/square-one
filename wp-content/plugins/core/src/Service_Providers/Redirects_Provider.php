<?php

namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Redirects\Page;
use Tribe\Project\Redirects\Redirects;
use Tribe\Project\Redirects\Table;

class Redirects_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$container[ 'redirects.redirects' ] = function ( Container $container ) {
			return new Redirects();
		};

		$container[ 'redirects.page' ] = function ( Container $container ) {
			return new Page();
		};

		$container[ 'redirects.tagle' ] = function ( Container $container ) {
			return new Table();
		};

		add_action( 'init', function() use ( $container ) {
			$container[ 'redirects.page' ]->hook();
		} );
	}
}
