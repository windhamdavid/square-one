<?php

namespace Tribe\Project\Queues\Backends;

use PhpAmqpLib\Message\AMQPMessage;
use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;

class RabbitMQ implements Backend {

	protected $channel = null;

	public function __construct( \PhpAmqpLib\Channel\AMQPChannel $connection ) {
		$this->channel = $connection;
	}

	public function get_type(): string {
		return self::class;
	}

	public function enqueue( string $queue_name, Message $message ) {
		$rabbit_message = $this->add_to_queue( $message );
		$this->channel->queue_declare( $queue_name, false, true, false, false );
		$this->channel->basic_publish( $rabbit_message, '', $queue_name );
	}

	private function add_to_queue( $message ) {
		$data = [
			'task_handler' => $message->get_task_handler(),
			'args'         => $message->get_args(),
		];

		$message_priority = $message->get_priority() < 255 ? $message->get_priority() : 255;

		return new AMQPMessage( json_encode( $data ),
			[
				'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
				'priority'      => $message_priority,
			]
		);
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
		$queue = $this->channel->queue_declare( $queue_name, true, true, false, false );
		return $queue[1];
	}
}