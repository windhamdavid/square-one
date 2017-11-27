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
		$this->channel->queue_declare( $queue_name, true, true, false, false );
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
		$this->channel->basic_qos( null, 1, null );
		$ticket    = $this->channel->basic_consume( $queue_name );
		$task      = $this->channel->basic_get( $queue_name, null, $ticket );
		$priority  = $task->get_properties()['priority'] ?: 10 ;
		$signature = json_decode( $task->getBody(), true );

		return new Message( $signature['task_handler'], $signature['args'], $priority, $ticket );
	}

	public function ack( string $job_id, string $queue_name ) {
		
	}

	public function nack( string $job_id, string $queue_name ) {
		$this->channel->basic_nack( $job_id );
	}

	public function count( string $queue_name ): int {
		$queue = $this->channel->queue_declare( $queue_name, true, true, false, false );
		return $queue[1];
	}
}