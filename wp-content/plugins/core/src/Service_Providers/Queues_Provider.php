<?php


namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Queues\Backends\MySQL;
use Tribe\Project\Queues\Backends\SQS;
use Tribe\Project\Queues\Backends\WP_Cache;
use Tribe\Project\Queues\DefaultQueue;
use Aws\Sqs\SqsClient;

class Queues_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {

		$container['queues.backend.sqs.client'] = function () {
			$credentials = [
				'region'      => defined( 'AWS_REGION' ) ? AWS_REGION : 'us-east-1',
				'credentials' => [
					'key'    => AWS_KEY,
					'secret' => AWS_SECRET,
				],
			];

			return new SqsClient( $credentials );
		};

		$container['queues.backend.sqs'] = function() use ( $container ) {
			return new SQS( $container['queues.backend.sqs.client'] );
		};

		$container['queues.backend.wp_cache'] = function() {
			return new WP_Cache();
		};

		$container['queues.backend.mysql'] = function() {
			return new MySQL();
		};

		$container['queues.DefaultQueue'] = function ( $container ) {
			$backend = $container['queues.backend.sqs'];
			return new DefaultQueue( $backend );
		};

		add_action( 'plugins_loaded', function () use ($container) {
			$container['queues.DefaultQueue'];
		} );
	}
}