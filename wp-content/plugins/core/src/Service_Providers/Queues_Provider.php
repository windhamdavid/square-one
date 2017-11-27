<?php


namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Queues\Backends\MySQL;
use Tribe\Project\Queues\Backends\RabbitMQ;
use Tribe\Project\Queues\Backends\WP_Cache;
use Tribe\Project\Queues\DefaultQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Queues_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {

		$container['queues.backend.rabbitmq.client'] = function() {
			$server = defined( 'RABBIT_MQ_SERVER' ) ? RABBIT_MQ_SERVER : '127.0.0.1';
			$port   = defined( 'RABBIT_MQ_PORT' ) ? RABBIT_MQ_PORT : '127.0.0.1';
			$user   = defined( 'RABBIT_MQ_USER' ) ? RABBIT_MQ_USER : '127.0.0.1';
			$pass   = defined( 'RABBIT_MQ_PASS' ) ? RABBIT_MQ_PASS : '127.0.0.1';
			return new AMQPStreamConnection( $server, $port, $user, $pass );
		};

		$container['queues.backend.wp_cache'] = function(){
			return new WP_Cache();
		};

		$container['queues.backend.mysql'] = function() {
			return new MySQL();
		};

		$container['queues.backend.rabbit'] = function() use ( $container ) {
			// Only add the shutdown function if we actually use RabbitMQ.
			add_action( 'shutdown', function() use ( $container ) {
				$container['queues.backend.rabbit']->close();
			} );

			return new RabbitMQ( $container['queues.backend.rabbitmq.client'] );
		};

		$container['queues.DefaultQueue'] = function ( $container ) {
			$backend = $container['queues.backend.rabbit'];
			return new DefaultQueue( $backend );
		};

		add_action( 'plugins_loaded', function () use ($container) {
			$container['queues.DefaultQueue'];
		} );

	}
}