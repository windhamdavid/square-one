<?php

namespace Tribe\Project\Components_Docs;

use ModularContent\AdminPreCache;
use ModularContent\Blueprint_Builder;
use ModularContent\Panel;
use ModularContent\PanelCollection;
use ModularContent\Plugin;
use ModularContent\Util;
use Tribe\Project\Components_Docs\Templates\Preview_Wrapper;
use Tribe\Project\Templates\Components\Component;

class Panel_Item extends Item {

	/**
	 * @var string $item_class
	 */
	protected $item_class;

	/**
	 * @var \ReflectionClass $reflection
	 */
	protected $reflection;

	/**
	 * @var Panel $panel
	 */
	protected $panel;

	public function __construct( string $item_class, Panel $panel ) {
		if ( ! class_exists( $item_class ) ) {
			throw new \InvalidArgumentException( 'Provided panel class does not exist.' );
		}

		$this->item_class = $item_class;
		$this->reflection = new \ReflectionClass( $item_class );
		$this->panel      = $panel;
	}

	public function get_slug(): string {
		return $this->panel->get( 'type' );
	}

	public function get_label(): string {
		$vars = $this->panel->get_template_vars();
		return $vars['title'] ?? $this->panel->get( 'label' );
	}

	public function get_constants(): array {
		return [];
	}

	public function get_sales_docs(): string {
		$short_name = $this->get_slug();
		$path       = $this->get_home_path() . 'docs/sales/panels';
		$docs_name  = sprintf( '%s/%s.md', $path, strtolower( $short_name ) );

		if ( ! file_exists( $docs_name ) ) {
			return '';
		}

		$contents  = file_get_contents( $docs_name );
		$parsedown = new Parsedown();
		$rendered  = $parsedown->text( $contents );

		return $rendered;
	}

	public function get_dev_docs(): string {
		$short_name = $this->get_slug();
		$path       = $this->get_home_path() . 'docs/panels/default';
		$docs_name  = sprintf( '%s/%s.md', $path, strtolower( $short_name ) );

		if ( ! file_exists( $docs_name ) ) {
			return '';
		}

		$contents  = file_get_contents( $docs_name );
		$parsedown = new Parsedown();
		$rendered  = $parsedown->text( $contents );

		return $rendered;
	}

	public function get_twig_src(): string {
		$twig_template = sprintf( '%s/content/panels/', get_template_directory(), $this->item_class::NAME );

		if ( ! file_exists( $twig_template ) ) {
			return '';
		}

		return file_get_contents( $twig_template );
	}

	public function get_panel_ui_preview() {
		$post     = get_post( 2 );
		$filtered = $post->post_content_filtered;
		$decoded  = json_decode( $filtered, true );
		$title    = $this->panel->get( 'title' );

		$decoded['panels'] = array_filter( $decoded['panels'], function ( $panel ) use ( $title ) {
			return $panel['data']['title'] === $title;
		} );

		$collection = PanelCollection::create_from_json( json_encode( $decoded ) );

		ob_start();
		$this->render_panel_ui( $collection );

		$box = ob_get_clean();

		return sprintf( '  <input type="hidden" id="title" value="foobar">
		            <input type="hidden" id="post_ID" value="foobar">
		            <div class="inside">
		                %s
		            </div>', $box );
	}

	private function render_panel_ui( $collection ) {

		$registry  = Plugin::instance()->registry();
		$blueprint = new Blueprint_Builder( $registry );
		$cache     = new AdminPreCache();
		foreach ( $collection->panels() as $panel ) {
			$panel->update_admin_cache( $cache );
		}
		$localization = [
			'delete_this_panel' => __( 'Are you sure you want to delete this?', 'modular-content' ),
			'save_gallery'      => __( 'Save Gallery', 'modular-content' ),
			'untitled'          => __( 'Untitled', 'modular-content' ),
			'loading'           => __( 'Loading...', 'modular-content' ),
		];

		$meta_box_data = [
			'blueprint'    => $blueprint,
			'cache'        => $cache,
			'localization' => $localization,
			'panels'       => $collection->build_tree(),
			'preview_url'  => '',
		];

		$meta_box_data       = apply_filters( 'modular_content_metabox_data', $meta_box_data, $post );
		$json_encoded_panels = Util::json_encode( $collection );

		include( Plugin::plugin_path( 'admin-views/meta-box-panels.php' ) );
	}

	public function get_rendered_template( $options = [] ): string {
		return $this->cleanup_html( $this->panel->render() );
	}

	public function get_class_name(): string {
		return $this->item_class;
	}
}