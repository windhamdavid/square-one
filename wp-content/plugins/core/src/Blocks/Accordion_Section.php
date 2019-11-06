<?php
declare( strict_types=1 );

namespace Tribe\Project\Blocks;

use Tribe\Gutenpanels\Blocks\Block_Type_Interface;

class Accordion_Section extends Block_Type_Config {
	public const NAME = 'tribe/accordion-section';

	public function build(): Block_Type_Interface {
		return $this->factory->block( self::NAME )
			->set_label( 'Accordion Section' )
			->add_content_section(
				$this->factory->content()->section()
					->add_field(
						$this->factory->content()->field()->text( 'header' )
							->add_class( 'h3' )
							->build()
					)
					->add_field(
						$this->factory->content()->field()->flexible_container( 'content' )
							->add_template_block( 'core/paragraph' )
							->add_block_type( 'core/paragraph' )
							->add_block_type( 'core/list' )
							->add_block_type( 'core/heading' )
							->add_block_type( 'core/image' )
							->build()
					)
					->build()
			)
			->build();
	}

}
