<?php

namespace Tribe\Project\CLI;

class Glomar extends Command {

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


		$this->file_system->get_config_file();
	}

}
