<?php
/**
 * Example Meta
 *
 * An example of how to register object meta.
 */

namespace Tribe\Project\Object_Meta;

use Tribe\Libs\ACF;
use Tribe\Project\Post_Types\Page\Page;

class Redirects extends ACF\ACF_Meta_Group {

	const NAME = 'redirect_meta';

	const REDIRECT = 'redirect';
	const REDIRECT_IS_REGEX = 'is_regex';
	const REDIRECT_PAGE_ID = 'page_id';
	const REDIRECT_PATTERN = 'redirects_pattern';
	const REDIRECT_REDIRECT_URL = 'redirect_url';
	const REDIRECT_TYPE = 'redirect_type';

	public function get_keys() {
		return [
			static::REDIRECT,
			static::REDIRECT_IS_REGEX,
			static::REDIRECT_PAGE_ID,
			static::REDIRECT_PATTERN,
			static::REDIRECT_REDIRECT_URL,
			static::REDIRECT_TYPE,
		];
	}

	public function get_group_config() {
		$group = new ACF\Group( static::NAME, $this->object_types );
		$group->set( 'title', __( '301 Redirects', 'tribe' ) );

		$group->add_field( $this->get_redirect_field() );

		return $group->get_attributes();
	}

	private function get_redirect_field() {
		$repeater = new ACF\Repeater( static::NAME . '_' . static::REDIRECT );
		$repeater->set_attributes( [
			'label'        => __( 'Redirect', 'tribe' ),
			'name'         => static::REDIRECT,
			'button_label' => __( 'Add 301 Redirect', 'tribe' ),
		] );

		$type = new ACF\Field( static::NAME . '_' . static::REDIRECT . '_' . static::REDIRECT_TYPE );
		$type->set_attributes( [
			'label'   => __( 'Type', 'tribe' ),
			'name'    => static::REDIRECT_TYPE,
			'type'    => 'select',
			'choices' => [
				static::REDIRECT_PAGE_ID => __( 'Page', 'tribe' ),
				static::REDIRECT_PATTERN => __( 'URL', 'tribe' ),
			]
		] );
		$repeater->add_field( $type );

		$is_regex = new ACF\Field( static::NAME . '_' . static::REDIRECT . '_' . static::REDIRECT_IS_REGEX );
		$is_regex->set_attributes( [
			'label' => __( 'Regex', 'tribe' ),
			'name'  => static::REDIRECT_IS_REGEX,
			'type'  => 'true_false',
		] );
		$repeater->add_field( $is_regex );

		$pattern = new ACF\Field( static::NAME . '_' . static::REDIRECT . '_' . static::REDIRECT_PATTERN );
		$pattern->set_attributes( [
			'label' => __( 'Match', 'tribe' ),
			'name'  => static::REDIRECT_PATTERN,
			'type'  => 'text',
		] );
		$repeater->add_field( $type );

		$redirect_url = new ACF\Field( static::NAME . '_' . static::REDIRECT . '_' . static::REDIRECT_REDIRECT_URL );
		$redirect_url->set_attributes( [
			'label'             => __( 'Redirect To URL', 'tribe' ),
			'name'              => static::REDIRECT_REDIRECT_URL,
			'type'              => 'text',
			'conditional_logic' => [
				[
					[
						'field'    => static::REDIRECT_TYPE,
						'operator' => '!=',
						'value'    => static::REDIRECT_PAGE_ID,
					],
				],
			],
		] );
		$repeater->add_field( $type );

		$page_id = new ACF\Field( static::NAME . '_' . static::REDIRECT . '_' . static::REDIRECT_PAGE_ID );
		$page_id->set_attributes( [
			'label'             => __( 'Redirect To Page', 'tribe' ),
			'name'              => static::REDIRECT_PAGE_ID,
			'type'              => 'post_object',
			'post_type'         => [
				Page::NAME,
			],
			'conditional_logic' => [
				[
					[
						'field'    => static::REDIRECT_TYPE,
						'operator' => '==',
						'value'    => static::REDIRECT_PAGE_ID,
					],
				],
			],
		] );
		$repeater->add_field( $type );

		return $repeater;
	}

}