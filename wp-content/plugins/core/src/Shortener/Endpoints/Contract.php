<?php

namespace Tribe\Project\Shortener\Endpoints;

abstract class Contract {

	const PATH = 'shortener/v1';

	abstract public function register();

	abstract public function endpoint();

}
