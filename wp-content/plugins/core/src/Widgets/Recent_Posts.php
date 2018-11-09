<?php

namespace Tribe\Project\Widgets;

use Tribe\Project\Templates\Content\Widgets\Recent_Posts as Template;

class Recent_Posts extends Widget {

	const SLUG = 'cti_recent_posts';

	public function get_slug() {
		return self::SLUG;
	}

	public function get_title() {
		return __( 'Recent Posts', 'tribe' );
	}

	public function get_options() {
		return [
			'classname' => self::SLUG,
			'description' => __( 'Display recent posts.', 'tribe' ),
		];
	}

	public function render_admin( $options ) {
		return '';
	}

	public function render_display( $args, $options ) {
		$template = new Template( $args['widget_id'], 'content/widgets/recent-posts.twig' );
		echo $template->render();
	}
}
