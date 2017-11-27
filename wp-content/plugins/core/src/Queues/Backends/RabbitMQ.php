<?php

namespace Tribe\Project\Queues\Backends;

use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;

class RabbitMQ implements Backend {

	protected $channel = null;

	public function __construct( $channel ) {
	}

	public function get_type(): string {
		// TODO: Implement get_type() method.
	}

	public function enqueue( string $queue_name, Message $m ) {
		// TODO: Implement enqueue() method.
	}

	public function dequeue( string $queue_name ) {
		// TODO: Implement dequeue() method.
	}

	public function ack( string $job_id, string $queue_name ) {
		// TODO: Implement ack() method.
	}

	public function nack( string $job_id, string $queue_name ) {
		// TODO: Implement nack() method.
	}

	public function count( string $queue_name ): int {
		// TODO: Implement count() method.
	}
}