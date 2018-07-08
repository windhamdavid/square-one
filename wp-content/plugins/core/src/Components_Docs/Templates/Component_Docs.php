<?php

namespace Tribe\Project\Components_Docs\Templates;

use Tribe\Project\Components_Docs\Registry;
use Tribe\Project\Facade\Items\Request;
use Tribe\Project\Templates\Components\Accordion;
use Tribe\Project\Twig\Twig_Template;

class Component_Docs extends Twig_Template {

	protected $registry;

	public function __construct( string $template, \Twig_Environment $twig = null, Registry $components_registry ) {
		parent::__construct( $template, $twig );

		$this->registry = $components_registry;
	}

	public function get_data(): array {
		$current_component = $this->get_current_component();

		$data = [
			'nav_items' => $this->get_nav_items( $current_component ),
			'item'      => $this->get_item( $current_component ),
		];

		return $data;
	}

	protected function get_current_component(): string {
		return Request::query()->query_vars['current_component'] ?? 'accordion';
	}

	protected function get_nav_items( $current ) {
		$items     = $this->registry->get_items();
		$nav_items = [];

		foreach ( $items as $key => $item ) {
			$nav_items[] = [ 'url' => home_url( sprintf( 'components_docs/%s', $key ) ), 'slug' => $key, 'label' => $item->get_label(), 'current' => $key === $current ];
		}

		return $nav_items;
	}

	protected function get_item( $current ): string {
		$item = $this->registry->get_item( $current );

		if ( empty( $item ) ) {
			return '';
		}

		$dev_docs   = $item->get_dev_docs();
		$sales_docs = $item->get_sales_docs();
		$constants  = $item->get_constants();
		$rendered   = $item->get_rendered_template();

		$rows = [];

		$rows[] = [
			'content'     => $sales_docs,
			'header_text' => 'Sales Documentation',
			'content_id'  => 'sales_docs',
			'header_id'   => 'sales_docs',
		];

		$rows[] = [
			'content'     => $dev_docs,
			'header_text' => 'Dev Documentation',
			'content_id'  => 'dev_docs',
			'header_id'   => 'dev_docs',
		];

		$constants    = Constants::factory( [ Constants::CONSTANTS => $constants ] );
		$preview_body = $constants->render() . $rendered;

		$rows[] = [
			'content'     => $preview_body,
			'header_text' => 'Preview',
			'content_id'  => 'preview',
			'header_id'   => 'preview',
		];

		$options = [
			Accordion::ROWS => $rows,
		];

		$accordion = Accordion::factory( $options );

		return $accordion->render();
	}

}