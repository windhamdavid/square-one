<?php


namespace Tribe\Project\Templates;


class API_Code extends Base {
	public function get_data(): array {
		$data         = parent::get_data();
		$data['code'] = filter_input( INPUT_GET, 'code' );
		$data['logo'] = trailingslashit( get_stylesheet_directory_uri() ) . 'img/theme/branding-assets/tribe-clockr-logo.png';
		return $data;
	}
}