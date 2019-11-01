<?php
declare( strict_types=1 );

namespace Tribe\Project\Blocks;

use Tribe\Gutenpanels\Blocks\Block_Type_Interface;

class Image_Text_Paragraph extends Block_Type_Config {
	public const NAME = 'tribe/image-text-paragraph';

	public function build(): Block_Type_Interface {
		return $this->factory->block( self::NAME )
			->set_label( 'Paragraph' )
			->add_content_section(
				$this->factory->content()->section()
					->add_field(
						$this->factory->content()->field()->richtext( 'content' )->build()
					)
					->build()
			)
			->build();
	}

}
