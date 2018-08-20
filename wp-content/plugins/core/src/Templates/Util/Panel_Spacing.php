<?php


namespace Tribe\Project\Templates\Util;


use ModularContent\PanelCollection;
use Tribe\Project\Templates\Components\Tabs;
use Tribe\Project\Twig\Twig_Template;

class Panel_Spacing extends Twig_Template {
	public function get_data(): array {
		$data                  = [];
		$data['sorted_panels'] = $this->get_sorted_panels();

		return $data;
	}

	private function get_sorted_panels() {
		$panels = PanelCollection::find_by_post_id( get_the_ID() )->panels();
		$tabs   = [];

		$spacing_vars = \Tribe\Project\Util\Panel_Spacing::get_spacer_vars();
		$spacer       = new Panel_Spacer( 'content/util/panel-spacer.twig' );
		$spacer->set_vars( $spacing_vars );
		$spacer_template = $spacer->render();

		foreach ( $panels as $panel ) {
			$content = '';
			$title   = $panel->get( 'title' );

			foreach ( $panels as $other_panel ) {
				$content .= '<h2>' . $panel->get( 'title' ) . ' + ' . $other_panel->get( 'title' ) . '</h2>' . $panel->render() . $spacer_template . $other_panel->render();
			}

			$tabs[] = [
				'tab_text'   => $title,
				'content'    => $content,
				'tab_id'     => uniqid( 'tab-' ),
				'content_id' => uniqid( 'content-' ),
			];
		}

		$options = [
			Tabs::TABS => $tabs,
		];

		$template = Tabs::factory( $options );

		return $template->render();
	}
}