<?php


namespace Tribe\Project\Service_Providers;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Util\Panel_Spacing;
use Tribe\Project\Util\SVG_Support;

class Util_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {

		$container[ 'util.svg_support' ] = function ( $container ) {
			return new SVG_Support();
		};

		add_action( 'init', function () use ( $container ) {
			$container[ 'util.svg_support' ]->hook();
		}, 10, 0 );

		$container['util.panel_spacing'] = function ( $container ) {
			return new Panel_Spacing();
		};

		add_action( 'wp_head', function() use ( $container ) {
			$container['util.panel_spacing']->init_panel_spacing_template();
		}, 10, 0 );

		add_action( 'wp_enqueue_scripts', function() use ( $container ) {
			$container['util.panel_spacing']->enqueue_scripts();
		}, 10, 0 );

		add_filter( 'core_js_config', function ( $data ) use ( $container ) {
			return $container['util.panel_spacing']->localize_spacer_vars( $data );
		}, 10, 1 );
	}
}