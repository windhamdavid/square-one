<?php

namespace Tribe\Project\Theme;

/**
 * Class Post_Gallery
 * @package Tribe\Project\Theme
 */

class Image_Gallery {
	private $args = [];

	public function __construct( $options = [] ) {
		$defaults = [
			'images'      => [],
			'gallery_id'  => uniqid(),
			'show_thumbs' => true,
		];

		$this->args = wp_parse_args( $options, $defaults );
	}

	/**
	 * Grab all appropriate gallery image sizes
	 *
	 * @param int $attachment_id
	 * @return array
	 */
	public function get_gallery_image( $attachment_id = 0 ) {

		$attachment = get_post( $attachment_id );
		$meta = get_post_meta( $attachment->ID );

		$image_gallery_mobile = wp_get_attachment_image_src( $attachment->ID, Image_Sizes::CORE_MOBILE );
		$image_gallery_large  = wp_get_attachment_image_src( $attachment->ID, Image_Sizes::CORE_FULL );
		$image_gallery_thumb  = wp_get_attachment_image_src( $attachment->ID, Image_Sizes::CORE_THUMBNAIL );


		$title   = $attachment->post_title;
		$caption = $attachment->post_excerpt;
		$alt     = isset( $meta['_wp_attachment_image_alt'][0] ) ? $meta['_wp_attachment_image_alt'][0] : '';

		return array(
			'alt'             => $alt,
			'attribution'     => isset( $meta['attribution'][0] ) ? $meta['attribution'][0] : '',
			'attribution_url' => isset( $meta['attribution_link'][0] ) ? $meta['attribution_link'][0] : '',
			'caption'         => $caption,
			'image'           => array(
				'full'   => $attachment->guid,
				'mobile' => array(
					'src'    => $image_gallery_mobile[0],
					'width'  => $image_gallery_mobile[1],
					'height' => $image_gallery_mobile[2],
				),
				'large'  => array(
					'src'    => $image_gallery_large[0],
					'width'  => $image_gallery_large[1],
					'height' => $image_gallery_large[2],
				),
				'thumb'  => array(
					'src'    => $image_gallery_thumb[0],
					'width'  => $image_gallery_thumb[1],
					'height' => $image_gallery_thumb[2],
				),
			),
			'title'           => $title
		);
	}

	/**
	 *
	 * @return string
	 */

	public function gallery_html() {

		if ( empty( $this->args['images'] ) ) {
			return '';
		}

		$wrapper_classes = [
			'image-gallery__wrapper',
			$this->args['show_thumbs'] == 1 ? 'image-gallery__wrapper--has-thumbs' : '',
		];

		ob_start();

		?>
		<div<?php echo Util::class_attribute( $wrapper_classes ); ?>>
			<div
				class="swiper-container image-gallery"
				data-js="image-gallery"
				data-id="gallery-<?php echo esc_attr( $this->args['gallery_id'] ); ?>"
			>
				<div class="swiper-wrapper">
					<?php
					$thumbnails = [];

					foreach ( $this->args['images'] as $image_id ) {
						if ( FALSE === get_post_status( $image_id ) ) {
							continue;
						}
						$src_attribute   = [];
						$image           = $this->get_gallery_image( $image_id );
						$thumbnails[]    = $image['image']['thumb']['src'];
						$src_attribute[] = sprintf(
							'%s %dw %dh',
							$image['image']['mobile']['src'],
							$image['image']['mobile']['width'],
							$image['image']['mobile']['height']
						);
						$src_attribute[] = sprintf(
							'%s %dw %dh',
							$image['image']['large']['src'],
							$image['image']['large']['width'],
							$image['image']['large']['height']
						);

						?>
						<figure class="swiper-slide image-gallery__slide">
							<img
								class="image-gallery__image lazyload"
								src="<?php echo esc_url( $image['image']['thumb']['src'] ); ?>"
								data-srcset="<?php echo esc_attr( implode( ", ", $src_attribute ) ); ?>"
								alt="<?php echo esc_attr( $image['alt'] ); ?>"
							/>
						</figure>
					<?php } ?>
				</div>
				<button class="swiper-button-next"></button>
				<button class="swiper-button-prev"></button>
			</div>
			<?php if ( $this->args['show_thumbs'] == 1 ) {
				$i = 0;
				?>
				<div
					class="image-gallery__thumbnails"
					data-js="gallery-thumbnails"
				>
					<?php foreach ( $thumbnails as $thumbnail ) { ?>
						<figure
							class="image-gallery__thumbnail"
							data-js="image-gallery-thumbnail-trigger"
							data-controls="gallery-<?php echo esc_attr( $this->args['gallery_id'] ); ?>"
							data-slide-index="<?php echo $i; ?>"
						>
							<img src="<?php echo esc_url( $thumbnail ); ?>" alt="thumbnail"/>
						</figure>
						<?php $i++;
					} ?>
				</div>
			<?php } ?>
		</div>
		<?php
		return ob_get_clean();
	}
}