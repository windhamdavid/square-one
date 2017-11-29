<?php

namespace Tribe\Project\Queues\Backends;

use Tribe\Project\Queues\Contracts\Backend;
use Tribe\Project\Queues\Message;
use Aws\Sqs\SqsClient;

class SQS implements Backend {

	protected $sqs = null;

	public function __construct( SqsClient $sqs_client ) {
		$this->sqs = $sqs_client;
	}

	private function get_queue_url( $queue_name ) {
		$queue_url = $this->sqs->createQueue( array(
			'QueueName'  => $queue_name . '.fifo',
			'Attributes' => [
				'FifoQueue' => 'true',
			],
		) );

		return $queue_url->get( 'QueueUrl' );
	}

	public function get_type(): string {
		return self::class;
	}

	public function enqueue( string $queue_name, Message $message ) {
		$queue_url = $this->get_queue_url( $queue_name );

		$this->sqs->sendMessage( [
			'QueueUrl'               => $queue_url,
			'MessageBody'            => json_encode( $this->prepare_message( $message ) ),
			'MessageGroupId'         => $message->get_priority(),
			'MessageDeduplicationId' => md5( json_encode( $message ) . microtime() ),
		] );
	}

	private function prepare_message( Message $message ) {
		return [
			'callback' => $message->get_task_handler(),
			'args'     => $message->get_args(),
			'priority' => $message->get_priority(),
		];
	}

	public function dequeue( string $queue_name ) {
		$queue_url = $this->get_queue_url( $queue_name );

		$message_reply = $this->sqs->receiveMessage( [
			'QueueUrl'            => $queue_url,
			'MaxNumberOfMessages' => 1,
			'VisibilityTimeout'   => HOUR_IN_SECONDS,
		] );

		if ( empty ( $message_reply['Messages'] ) ) {
			return new Message( '' );
		}

		$body   = json_decode( $message_reply['Messages'][0]['Body'], 1 );
		$job_id = $message_reply['Messages'][0]['ReceiptHandle'];

		return new Message( $body['callback'], $body['args'], $body['priority'], $job_id );
	}

	public function ack( string $job_id, string $queue_name ) {
		$queue_url = $this->get_queue_url( $queue_name );

		$this->sqs->deleteMessage( [
			'QueueUrl' => $queue_url,
			'ReceiptHandle' => $job_id,
		] );
	}

	public function nack( string $job_id, string $queue_name ) {
		if ( '' == $job_id ) {
			return;
		}

		$queue_url = $this->get_queue_url( $queue_name );

		$this->sqs->changeMessageVisibility( [
			'QueueUrl'          => $queue_url,
			'ReceiptHandle'     => $job_id,
			'VisibilityTimeout' => 0,
		] );
	}

	public function cleanup() {
		null;
	}

	public function count( string $queue_name ): int {
		// TODO: Implement count() method.
	}
}