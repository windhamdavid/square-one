<?php
namespace Tribe\Project\Service_Providers;

use Monolog\Handler\BrowserConsoleHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Logging\Handlers\Console;
use Tribe\Project\Logging\Loggers\General;

class Logging_Provider implements ServiceProviderInterface {

	const LOGGER_GENERAL    = 'logging.logger.general';

	const HANDLER_CONSOLE   = 'logging.handler.console';

	public function register( Container $container ) {

		$container[ self::HANDLER_CONSOLE ] = function () {
			return new BrowserConsoleHandler();
		};

		$container[ self::LOGGER_GENERAL ] = function () use ( $container ) {
			return new General( [
				$container[ self::HANDLER_CONSOLE ],
			] );
		};
	}
}