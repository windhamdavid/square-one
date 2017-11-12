<?php

namespace Tribe\Project\Parsedown;

/**
 * Class Tribe_Parsedown
 */
class Tribe_Parsedown {

	const DOCS_ROOT = '/docs/theme/components';

	/**
	 * Get the root /docs directory of the repo.
	 *
	 * @return string
	 */
	protected function get_docs_dir() : string {
		return trailingslashit( dirname( __FILE__, 6 ) . self::DOCS_ROOT );
	}

	/**
	 * Get the component name directory within the main docs directory.
	 *
	 * @param string $name Name of the component directory.
	 *
	 * @return string
	 */
	protected function get_components_docs_dir( $name ) : string {
		return trailingslashit( $this->get_docs_dir() . $name );
	}

	/**
	 * Markdown factory.
	 *
	 * @param string $component_name The name used to locate the directory with in the main docs directory.
	 * @param string $component_md   The file name to find within the component directory.
	 *
	 * @return string
	 */
	public function render( $component_name, $component_md ): string {
		if ( ! $component_md && $component_name ) {
			return false;
		}

		$component_doc = $this->get_components_docs_dir( $component_name ) . $component_md;
		$file          = file_exists( $component_doc );
		if ( ! $file ) {
			return sprintf( "Uh oh, Spaghetti-o's. Your <code>%s</code> document wasn't found in the <code>/%s</code> directory. 👀", $component_md, $component_name );
		}

		$container = tribe_project()->container();
		$contents  = file_get_contents( $component_doc );

		return $container['parsedown']->text( $contents );
	}
}
