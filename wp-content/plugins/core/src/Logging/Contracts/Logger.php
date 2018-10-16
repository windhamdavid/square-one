<?php
namespace Tribe\Project\Logging\Contracts;

use Monolog\Logger as Monolog_Logger;

abstract class Logger {

	/**
	 * @var Monolog_Logger $logger
	 */
	protected $logger;

	protected $handlers;

	public function __construct( array $handlers ) {

		if( ! defined( 'TRIBE_DEBUG' ) || ! TRIBE_DEBUG ) {
			return false;
		}

		$this->handlers = $handlers;
		$this->logger = $this->get_logger( $this->handlers );
		$this->push_handlers();
	}

	abstract public function get_logger() : Monolog_Logger;

	public function push_handlers() {
		foreach( $this->handlers as $handler ) {
			$this->logger->pushHandler( $handler );
		}
	}

	abstract public function log();
}