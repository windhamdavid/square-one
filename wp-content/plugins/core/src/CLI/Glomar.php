<?php

namespace Tribe\Project\CLI;

class Glomar extends Command {

	private $config_file = '';

	public function description() {
		return __( 'Manage glomar.', 'tribe' );
	}

	public function command() {
		return 'glomar';
	}

	public function arguments() {
		return [
			[
				'type'        => 'flag',
				'name'        => 'enable',
				'optional'    => true,
				'description' => 'I can neither confirm nor deny the existence of the enable flag.',
			],
			[
				'type'        => 'flag',
				'name'        => 'disable',
				'optional'    => true,
				'description' => 'I can neither confirm nor deny the existence of the disable flag.',
			],
		];
	}

	public function run_command( $args, $assoc_args ) {
		$this->config_file = $this->file_system->get_config_file();

		if ( $assoc_args['enable'] ) {
			$this->update_config_file( 'true' );
		}

		if ( $assoc_args['disable'] ) {
			$this->update_config_file( 'false' );
		}

		\WP_CLI::line( 'I can neither confirm nor deny that this command has been run' );
	}

	protected function update_config_file( $status ) {
		$glomar_flag = sprintf( 'define( \'TRIBE_GLOMAR\', %s );', $status );
		$this->file_system->insert_into_existing_file( $this->config_file, $glomar_flag, 'TRIBE_GLOMAR', true );
	}

}
