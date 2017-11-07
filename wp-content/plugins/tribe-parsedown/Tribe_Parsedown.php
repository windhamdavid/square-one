<?php

/**
 * Class Tribe_Parsedown
 */
class Tribe_Parsedown {

	/**
	 * Used to ensure this class is available in our environment.
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Represents the instantiation of the Parsedown class to be used in factory.
	 *
	 * @var Parsedown
	 */
	public $component_doc;

	const DOCS_ROOT = '/docs/theme/components';

	/**
	 * Tribe_Parsedown constructor.
	 */
	public function __construct() {
		require_once 'Parsedown.php';
		$this->component_doc = new \Parsedown();
	}

	/**
	 * Get the root /docs directory of the repo.
	 *
	 * @return string
	 */
	protected function get_docs_dir() : string {
		return trailingslashit( dirname( __FILE__, 4 ) . self::DOCS_ROOT );
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
	public function factory( $component_name, $component_md ) : string {
		if ( ! $component_md && $component_name ) {
			return false;
		}

		$component_doc = $this->get_components_docs_dir( $component_name ) . $component_md;
		$file          = file_exists( $component_doc );

		if ( ! $file ) {
			return sprintf( "Uh oh, Spaghetti-o's. Your <code>%s</code> document wasn't found in the <code>/%s</code> directory. 👀", $component_md, $component_name );
		}

		// TODO: @aaron should we do this as URI instead of server /dir path?
		$contents = file_get_contents( $component_doc );

		return $this->component_doc->text( $contents );
	}

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {

		self::$instance = self::get_instance();
	}

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Tribe_Parsedown
	 */
	public static function get_instance() {

		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
