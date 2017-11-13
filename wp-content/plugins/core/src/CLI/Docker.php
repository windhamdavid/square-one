<?php

namespace Tribe\Project\CLI;

class Docker extends Command {
	use File_System;

	public function description() {
		return __( 'A generated CLI command.', 'tribe' );
	}

	public function command() {
		return 'docker';
	}

	public function arguments() {
		return [
			[
				'type'        => 'positional',
				'name'        => 'docker',
				'optional'    => true,
				'description' => 'The name of the docker',
			],
		];
	}

	public function run_command( $args, $assoc_args ) {
		$this->slug       = $this->sanitize_slug( $args );
		$this->class_name = $this->ucwords( $this->slug );
	}

}
