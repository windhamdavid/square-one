<?php
declare( strict_types=1 );

namespace Tribe\Project\Blocks;

use Tribe\Gutenpanels\Blocks\Block_Type_Interface;

class Accordion extends Block_Type_Config {
	public const NAME = 'tribe/accordion';

	public function build(): Block_Type_Interface {
		return $this->factory->block( self::NAME )
			->set_label( 'Accordion' )
			->set_dashicon( 'menu-alt3' )
			->add_layout_property( 'grid-template-areas', "content\naccordion" )
			->add_conditional_layout_property( 'grid-template-areas', 'content accordion', 'layout', '==', 'inline' )
			->add_sidebar_section(
				$this->factory->sidebar()->section()->set_label( 'Layout Settings' )
					->add_field(
						$this->factory->sidebar()->field()->image_select( 'layout' )
							->set_label( 'Layout' )
							->add_option( 'stacked', 'Stacked', 'https://via.placeholder.com/100x60.png?text=Stacked' )
							->add_option( 'inline', 'Inline', 'https://via.placeholder.com/100x60.png?text=Inline' )
							->set_default( 'stacked' )
							->build()
					)
					->build()
			)
			->add_content_section(
				$this->factory->content()->section()
					->set_layout_property( 'grid-area', 'content' )
					->add_field(
						$this->factory->content()->field()->text( 'title' )
							->add_class( 'h2' )
							->build()
					)
					->add_field(
						$this->factory->content()->field()->richtext( 'description' )
							->build()
					)
					->build()
			)
			->add_content_section(
				$this->factory->content()->section()
					->set_layout_property( 'grid-area', 'accordion' )
					->add_field(
						$this->factory->content()->field()->flexible_container( 'accordion' )
							->set_label( 'Accordion' )
							->add_template_block( Accordion_Section::NAME )
							->add_block_type( Accordion_Section::NAME )
							->set_min_blocks( 1 )
							->build()
					)
					->build()
			)
			->build();
	}
}
