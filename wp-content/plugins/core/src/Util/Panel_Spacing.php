<?php

namespace Tribe\Project\Util;

class Panel_Spacing {

	public function init_panel_spacing_template() {
		if ( ! is_page_template( 'page-templates/page-panel-spacing.php' ) ) {
			return;
		}

		remove_all_actions( 'the_panels' );

		add_action( 'the_panels', [ $this, 'render_panel_spacing' ] );
	}

	public function render_panel_spacing() {
		$template = new \Tribe\Project\Templates\Util\Panel_Spacing( 'content/util/panel-spacing.twig' );
		echo $template->render();
	}

	public function enqueue_scripts() {
		if ( ! is_page_template( 'page-templates/page-panel-spacing.php' ) ) {
			return;
		}

		wp_enqueue_script( 'js-prettify', 'https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?lang=css&skin=desert' );
	}

	public function localize_spacer_vars( $data ) {
		$data['panel_spacer_vars'] = self::get_spacer_vars();

		return $data;
	}

	public static function get_spacer_vars(): array {
		$path = get_template_directory() . '/pcss/utilities/variables/_panel-spacing.pcss';

		if ( ! file_exists( $path ) ) {
			return [];
		}

		$contents = file_get_contents( $path );

		preg_match_all( '/(--spacer-[a-z]+): ([0-9]+px);/', $contents, $matches );

		if ( empty( $matches ) ) {
			return [];
		}

		$vars = [];

		for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
			$vars[ $matches[1][ $i ] ] = $matches[2][ $i ];
		}

		return $vars;
	}

}