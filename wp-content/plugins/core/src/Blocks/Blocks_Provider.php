<?php
declare( strict_types=1 );

namespace Tribe\Project\Blocks;

use Pimple\Container;
use Tribe\Gutenpanels\Builder\Factories\Builder_Factory;
use Tribe\Gutenpanels\Container\Provider;
use Tribe\Gutenpanels\Registration\Registry;

class Blocks_Provider extends Provider {
	public const BLOCKS          = 'blocks.blocks';
	public const BUILDER_FACTORY = 'blocks.builder_factory';

	public function register( Container $container ) {

		$container[ self::BUILDER_FACTORY ] = function ( Container $container ) {
			return new Builder_Factory();
		};

		$container[ self::BLOCKS ] = function ( Container $container ) {
			return [
				new Image_Text( $container[ self::BUILDER_FACTORY ] ),
				new Image_Text_Paragraph( $container[ self::BUILDER_FACTORY ] ),
				new Accordion( $container[ self::BUILDER_FACTORY ] ),
				new Accordion_Section( $container[ self::BUILDER_FACTORY ] ),
			];
		};

		add_action( 'tribe/gutenpanels/register', function ( Registry $registry ) use ( $container ) {
			foreach ( $container[ self::BLOCKS ] as $block ) {
				/** @var Block_Type_Config $block */
				$registry->register( $block->build() );
			}
		}, 10, 1 );
	}
}
