<?php

namespace Tribe\Project\Shortener;

use Pimple\Container;
use Tribe\Project\Shortener\Post_Type\Provider;

class Service_Provider implements \Pimple\ServiceProviderInterface {
	public function register( Container $container ) {
		if ( ! is_multisite() ) {
			return;
		}

		$container->register( new Provider() );


	}
}

/**
 * Endpoints
 * - Create (restricted to whitelisted domains? Verify a url is not already shortened?)
 * - Update
 * - Delete
 */