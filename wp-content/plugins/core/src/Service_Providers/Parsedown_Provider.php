<?php

namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Parsedown\Tribe_Parsedown;
use Parsedown;

class Parsedown_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		$this->parsedown( $container );
		$this->tribe_parsedown( $container );
	}

	protected function parsedown( Container $container ) {
		$container['parsedown'] = function () {
			return new Parsedown();
		};
	}

	protected function tribe_parsedown( Container $container ) {
		$container['tribe_parsedown'] = function () {
			return new Tribe_Parsedown();
		};
	}
}