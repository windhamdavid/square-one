<?php

namespace Tribe\Project\Templates\Content\Widgets;

use Tribe\Project\Object_Meta\Widgets\Recent_Posts as Meta;
use Tribe\Project\Post_Types\Post\Post;
use Tribe\Project\Taxonomies\Category\Category;
use Tribe\Project\Templates\Components\Image;
use Tribe\Project\Theme\Image_Sizes;
use Tribe\Project\Twig\Twig_Template;

class Recent_Posts extends Twig_Template {

	protected $widget_id;

	public function __construct( $widget_id, $template, \Twig_Environment $twig = null  ) {
		$this->widget_id = $widget_id;
		parent::__construct(  $template, $twig );
	}

	public function get_data() : array {
		$data['posts'] = $this->get_widget_posts();

		return $data;
	}

	protected function get_widget_posts() {
		$args = [
			'post_type' => Post::NAME,
			'numberposts' => get_field( Meta::NUMBER_POSTS, 'widget_' . $this->widget_id ),
		];

		$taxonomy = get_field( Meta::CATEGORY, 'widget_' . $this->widget_id );
		if ( ! empty( $taxonomy ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => Category::NAME,
					'field' => 'term_id',
					'terms' => $taxonomy,
				]
			];
		}

		$posts_data = [];
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			/** @var \WP_Post $post */
			$thumbnail = get_post_thumbnail_id( $post );
			if ( ! empty( $thumbnail ) ) {
				$thumbnail = $this->get_post_thumbnail( $thumbnail );
			}

			$posts_data[] = [
				'title' => $post->post_title,
				'url' => get_the_permalink( $post->ID ),
				'thumbnail' => $thumbnail,
				'excerpt' => $post->post_excerpt,
			];
		}

		return $posts_data;
	}

	private function get_post_thumbnail( $image_id ) : string {
		$image_placeholder = wp_get_attachment_image_src( $image_id, Image_Sizes::HERO_PLACEHOLDER )[ 0 ];

		$options = [
			Image::IMG_ID          => $image_id,
			Image::COMPONENT_CLASS => 'c-image subhead__img',
			Image::AS_BG           => true,
			Image::USE_LAZYLOAD    => true,
			Image::SHIM            => $image_placeholder,
			Image::ECHO            => false,
			Image::WRAPPER_CLASS   => 'c-image__bg',
			Image::SRC_SIZE        => Image_Sizes::HERO,
			Image::SRCSET_SIZES    => [
				Image_Sizes::HERO,
			],
		];

		$image_obj = Image::factory( $options );

		return $image_obj->render();
	}

}
