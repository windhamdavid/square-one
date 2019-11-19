<?php
declare( strict_types=1 );

namespace Tribe\Project\Blocks;

use Tribe\Gutenpanels\Blocks\Block_Type_Interface;

class Image_Text extends Block_Type_Config {
	public const NAME = 'tribe/image-text';

	public function build(): Block_Type_Interface {
		return $this->factory->block( self::NAME )
			->set_label( 'Image + Text' )
			->set_dashicon( 'align-left' )
			->add_layout_property( 'grid-template-areas', 'image text' )
			->add_conditional_layout_property( 'grid-template-areas', 'text image', 'alignment', '==', 'image-right' )
			->add_sidebar_section(
				$this->factory->sidebar()->section()->set_label( 'Layout Settings' )
					->add_field(
						$this->factory->sidebar()->field()->image_select( 'alignment' )
							->set_label( 'Layout' )
							->add_option( 'image-left', 'Left', 'https://via.placeholder.com/100x60.png?text=Left' )
							->add_option( 'image-right', 'Right', 'https://via.placeholder.com/100x60.png?text=Right' )
							->set_default( 'image-left' )
							->build()
					)
					->build()
			)
			->add_content_section( // Image
				$this->factory->content()->section()
					->set_layout_property( 'grid-area', 'image' )
					->add_field(
						$this->factory->content()->field()->image( 'image' )
							->set_label( 'Image area' )
							->build()
					)
					->build()
			)
			->add_content_section( // Text
				$this->factory->content()->section()
					->set_layout_property( 'grid-area', 'text' )
					->add_field(
						$this->factory->content()->field()->text( 'title' )
							->set_label( 'Title' )
							->add_class( 'h2' )
							->build()
					)
					->add_field(
						$this->factory->content()->field()->richtext( 'content' )
							->set_label( 'Content' )
							->add_class( 'p' )
							->build()
					)
					->add_field(
						$this->factory->content()->field()->fixed_container( 'cta' )
							->set_label( 'Call to Action' )
							->add_template_block( 'tribe/button' )
							->build()
					)
					->build()
			)
			->build();
	}
}
