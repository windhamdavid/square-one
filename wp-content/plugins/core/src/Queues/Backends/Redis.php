<?php

namespace Tribe\Project\Queues\Backends;

use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;

class Redis implements Backend {

	public function __construct( \Predis\Client $redis ) {
		$this->redis = $redis;
	}

	public function get_type(): string {
		return self::class;
	}

	public function enqueue( string $queue_name, Message $message ) {
		$this->redis->zadd( $queue_name, $this->prepare_data( $message ) );
	}

	private function prepare_data( $message ) {
		return [
			$message->get_priority() => json_encode( [
				'task_handler' => $message->get_task_handler(),
				'args'         => $message->get_args(),
			] ),
		];
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
		return $this->redis->llen( $queue_name );
	}
}