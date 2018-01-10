<?php
namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Sortables\Terms;

class Sortables_Service_Provider implements ServiceProviderInterface {
	public function register( Container $container ) {

		$container['sortables.terms'] = function ( Container $container ) {
			return new Terms();
		};
	}
}