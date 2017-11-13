<?php

namespace Tribe\Project\CLI;

class Docker extends Command {
	use File_System;

	private $docker_path = '';

	public function description() {
		return __( 'Manage docker for this repo.', 'tribe' );
	}

	public function command() {
		return 'docker';
	}

	public function arguments() {
		return [
			[
				'type'        => 'flag',
				'name'        => 'start',
				'optional'    => true,
				'description' => __( 'Start the docker instances for this repo.', 'tribe' ),
			],
			[
				'type'        => 'flag',
				'name'        => 'stop',
				'optional'    => true,
				'description' => __( 'Stop the docker instances for this repo.', 'tribe' ),
			],
		];
	}

	public function run_command( $args, $assoc_args ) {
		if ( ! count( $assoc_args ) ) {
			$this->helper_message();
		}

		$this->docker_path = trailingslashit( dirname( __DIR__, 5 ) ) . 'dev/docker/';

		// Remove the false flags and flip the array so key becomes value.
		$assoc_args = array_filter( $assoc_args );

		foreach ( $assoc_args as $arg => $bool ) {
			switch ( $arg ) {
				case 'start':
					$this->start();
					break;
				case 'stop':
					$this->stop();
					break;
				default:
					$this->helper_message();
			}
		}
	}

	private function start() {
		if( ! shell_exec( 'docker inspect -f \'{{.State.Running}}\' tribe-proxy' ) ) {
			$this->try_start_s1();
		}

		shell_exec( 'bash ' . $this->docker_path . 'start.sh' );
	}

	private function stop() {
		shell_exec( 'bash ' . $this->docker_path . 'stop.sh' );
	}

	private function helper_message() {
		\WP_CLI::line( __( 'You need to pass a flag --start or --stop to do something', 'tribe' ) );
	}

	private function try_start_s1() {
		$s1 = trailingslashit( dirname( __DIR__, 6 ) ) . 'dev/docker/global/start.sh';
		shell_exec( 'bash ' . $s1 );
	}
}
