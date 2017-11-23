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
		$this->redis->zadd( $queue_name, $message->get_priority(), $this->prepare_data( $message ) );
	}

	private function prepare_data( $message ) {
		return json_encode( [
				'task_handler' => $message->get_task_handler(),
				'args'         => $message->get_args(),
			] );
	}

	public function dequeue( string $queue_name ) {
		// Get the item.
		$list_item = array_flip( $this->redis->zrange( $queue_name, 0, 0, 'WITHSCORES' ) );
		$priority = key( $list_item );

		// Remove it from the sorted set.
		$this->redis->zrem( $queue_name, $list_item[ $priority ] );
		$task = json_decode( $list_item[ $priority ], true );
		$id = uniqid( $task['task_handler'], false );

		// Add it as a standard redis object until we hear back.
		$this->redis->set( $id, json_encode( $list_item ) );

		return new Message( $task['task_handler'], $task['args'], $priority, $id );
	}

	public function ack( string $job_id, string $queue_name ) {
		$this->redis->del( [ $job_id ] );
	}

	public function nack( string $job_id, string $queue_name ) {
		$job = json_decode( $this->redis->get( $job_id ), true );
		$priority = key( $job );

		$this->redis->zadd( $queue_name, $priority, $job[$priority] );
		$this->ack( $job_id, 'null' );
	}

	public function cleanup() {
		$keys = $this->redis->scan( 0 );
		foreach ( $keys[1] as $job_id ) {
			$this->nack( $job_id, 'null' );
		}
	}

	public function count( string $queue_name ): int {
		return $this->redis->zcount( $queue_name, PHP_INT_MIN, PHP_INT_MAX );
	}
}