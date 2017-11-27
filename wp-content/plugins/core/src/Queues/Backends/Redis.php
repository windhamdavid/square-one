<?php

namespace Tribe\Project\Queues\Backends;

use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;

class Redis implements Backend {

	protected $predis = null;

	public function __construct( \Predis\Client $redis ) {
		$this->predis = $redis;
	}

	public function get_type(): string {
		return self::class;
	}

	public function enqueue( string $queue_name, Message $message ) {
		$this->predis->zadd( $queue_name, $message->get_priority(), $this->prepare_data( $message ) );
	}

	private function prepare_data( $message ) {
		return json_encode( [
			'task_handler' => $message->get_task_handler(),
			'args'         => $message->get_args(),
		] );
	}

	public function dequeue( string $queue_name ) {
		// Get the item.
		$list_item = array_flip( $this->predis->zrange( $queue_name, 0, 0, 'WITHSCORES' ) );
		$priority = key( $list_item );

		// Remove it from the sorted set.
		$this->predis->zrem( $queue_name, $list_item[ $priority ] );
		$task = json_decode( $list_item[ $priority ], true );
		$id = uniqid( $task['task_handler'], false );

		// Add it as a standard redis object until we hear back.
		$this->predis->set( $id, json_encode( $list_item ) );

		return new Message( $task['task_handler'], $task['args'], $priority, $id );
	}

	public function ack( string $job_id, string $queue_name ) {
		$item = json_decode( $this->predis->get( $job_id ), true );
		$priority = key( $item );

		$task = json_decode( $item[ $priority ], true );
		$task['completed'] = time();

		// Save it in a completed_$queue_name sorted set.
		$this->predis->zadd( 'completed_' . $queue_name, $priority, json_encode( $task ) );
	}

	public function nack( string $job_id, string $queue_name ) {
		$job = json_decode( $this->predis->get( $job_id ), true );
		$priority = key( $job );

		$this->predis->zadd( $queue_name, $priority, $job[$priority] );
		$this->ack( $job_id, 'null' );
	}

	public function cleanup() {
		$keys = $this->predis->scan( 0 );
		foreach ( $keys[1] as $job_id ) {
			$this->nack( $job_id, 'null' );
		}
	}

	public function count( string $queue_name ): int {
		return $this->predis->zcount( $queue_name, PHP_INT_MIN, PHP_INT_MAX );
	}
}