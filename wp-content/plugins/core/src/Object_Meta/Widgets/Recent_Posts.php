<?php

namespace Tribe\Project\Object_Meta\Widgets;

use Tribe\Libs\ACF;
use Tribe\Project\Taxonomies\Category\Category;

/**
 * Class Recent_Posts
 * @package Tribe\Project\Object_Meta\Widgets
 */
class Recent_Posts extends ACF\ACF_Meta_Group {

	const NAME = 'widget_recent_posts';

	const NUMBER_POSTS = 'recent_posts_number_posts';
	const CATEGORY = 'recent_posts_category';


	/**
	 * @return array
	 */
	public function get_keys() {
		return [
			static::NUMBER_POSTS,
			static::CATEGORY,
		];
	}

	/**
	 * @return array
	 */
	public function get_group_config() {
		$group = new ACF\Group( self::NAME, $this->object_types );
		$group->set( 'title', __( 'Recent Posts Widget Settings', 'tribe' ) );

		$group->add_field( $this->add_post_count_field() );
		$group->add_field( $this->add_category_field() );

		return $group->get_attributes();
	}

	private function add_post_count_field() {
		$field = new ACF\Field( self::NAME . '_' . self::NUMBER_POSTS );
		$field->set_attributes( [
			'label' => __( 'Number of Posts', 'tribe' ),
			'name'  => self::NUMBER_POSTS,
			'type'  => 'number',
			'default_value' => 3,
			'min' => 1,
		] );

		return $field;
	}

	private function add_category_field() {
		$field = new ACF\Field( self::NAME . '_' . self::CATEGORY );
		$field->set_attributes( [
			'label' => __( 'Optional Category', 'tribe' ),
			'name'  => self::CATEGORY,
			'type'  => 'taxonomy',
			'taxonomies' => [ Category::NAME ]
		] );

		return $field;
	}

}
