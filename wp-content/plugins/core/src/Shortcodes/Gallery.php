<?php

namespace Tribe\Project\Shortcodes;

use Tribe\Project\Theme\Image_Gallery;

class Gallery {
	public function hook() {
		add_filter( 'post_gallery', [ $this, 'modify_shortcode_output' ], 10, 2 );
	}

	public function modify_shortcode_output( $output, $attr ) {
		global $post;

		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) ) {
				$attr['orderby'] = 'post__in';
			}
			$attr['include'] = $attr['ids'];
		}

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( ! $attr['orderby'] ) {
				unset( $attr['orderby'] );
			}
		}

		$attributes = shortcode_atts( [
			'order'   => 'ASC',
			'orderby' => 'menu_order ID',
			'id'      => $post ? $post->ID : 0,
			'size'    => 'thumbnail',
			'include' => '',
			'exclude' => ''
		], $attr, 'gallery' );

		$id      = intval( $attributes['id'] );
		$orderby = $attributes['orderby'];

		if ( 'RAND' == $orderby ) {
			$orderby = 'none';
		}

		if ( ! empty( $attributes['include'] ) ) {
			$_attachments = get_posts( array( 'include'        => $attributes['include'],
			                                  'post_status'    => 'inherit',
			                                  'post_type'      => 'attachment',
			                                  'post_mime_type' => 'image',
			                                  'order'          => $attributes['order'],
			                                  'orderby'        => $orderby
			) );

			$attachments = [];
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( ! empty( $attributes['exclude'] ) ) {
			$attachments = get_children( array( 'post_parent'    => $id,
			                                    'exclude'        => $attributes['exclude'],
			                                    'post_status'    => 'inherit',
			                                    'post_type'      => 'attachment',
			                                    'post_mime_type' => 'image',
			                                    'order'          => $attributes['order'],
			                                    'orderby'        => $orderby
			) );
		} else {
			$attachments = get_children( array( 'post_parent'    => $id,
			                                    'post_status'    => 'inherit',
			                                    'post_type'      => 'attachment',
			                                    'post_mime_type' => 'image',
			                                    'order'          => $attributes['order'],
			                                    'orderby'        => $orderby
			) );
		}

		if ( empty( $attachments ) ) {
			return '';
		}

		if ( is_feed() ) {
			$gallery = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$gallery .= wp_get_attachment_link( $att_id, $attributes['size'], true ) . "\n";
			}

			return $gallery;
		}

		$gallery_args = [
			'images' => $attachments,
		];
		$gallery_obj  = new Image_Gallery( $gallery_args );

		$gallery = $gallery_obj->gallery_html();
		//remove any new lines already in there
		$gallery = str_replace( "\n", "", $gallery );

		//remove all <p>
		$gallery = str_replace( "<p>", "", $gallery );

		//replace <br /> with \n
		$gallery = str_replace( array( "<br />", "<br>", "<br/>" ), "\n", $gallery );

		//replace </p> with \n\n
		$gallery = str_replace( "</p>", "\n\n", $gallery );


		return $gallery;
	}
}