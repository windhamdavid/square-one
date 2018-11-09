<?php

namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Widgets;

class Widgets_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$container[ 'widgets.recent_posts' ] = function ( Container $container ) {
			return new Widgets\Recent_Posts();
		};

		add_action( 'widgets_init', function() use ( $container ) {
			$container[ 'widgets.recent_posts' ]->register_widget();
		}, 10, 0 );
	}

}
