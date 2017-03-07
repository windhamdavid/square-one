<?php


namespace Tribe\Project\Service_Providers;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Shortcodes\Gallery;

class Shortcode_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$container['shortcodes.gallery'] = function ( Container $container ) {
			return new Gallery();
		};

		$container['service_loader']->enqueue( 'shortcodes.gallery', 'hook' );
	}

}
