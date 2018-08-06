<?php


namespace Tribe\Project\Shortener\Post_Type;

use Tribe\Libs\Post_Type\Post_Type_Config;

class Config extends Post_Type_Config {
	public function get_args() {
		return [
			'hierarchical'     => false,
			'public'           => false,
			'enter_title_here' => __( 'Shortener', 'tribe' ),
			'capability_type'  => 'post',
		];
	}

	public function get_labels() {
		return [];
	}

}
