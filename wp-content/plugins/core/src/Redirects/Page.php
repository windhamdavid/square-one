<?php

namespace Tribe\Project\Redirects;

use Tribe\Libs\ACF\ACF_Settings;

class Page extends ACF_Settings {

	public function get_title() {
		return '301 Redirects';
	}

	public function get_capability() {
		return 'activate_plugins';
	}

	public function get_parent_slug() {
		return 'options-general.php';
	}

	public static function instance() {
		return tribe_project()->container()['redirects.page'];
	}

}