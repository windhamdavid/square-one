<?php


namespace Tribe\Project\Templates\Util;


use Tribe\Project\Twig\Twig_Template;

class Panel_Spacer extends Twig_Template {
	protected $vars;

	public function get_data(): array {
		$data         = [];
		$data['vars'] = $this->vars;

		return $data;
	}

	public function set_vars( $vars ) {
		$this->vars = $vars;
	}

}