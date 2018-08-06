<?php

namespace Tribe\Project\Shortener\Post_Type;


use Tribe\Project\Service_Providers\Post_Types\Post_Type_Service_Provider;

class Provider extends Post_Type_Service_Provider {
	protected $post_type_class = Shortener::class;
	protected $config_class    = Config::class;
}