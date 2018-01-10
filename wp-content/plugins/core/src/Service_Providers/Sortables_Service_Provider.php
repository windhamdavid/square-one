<?php
namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Sortables\Terms;

class Sortables_Service_Provider implements ServiceProviderInterface {
	public function register( Container $container ) {

		$container['sortables.terms'] = function ( Container $container ) {
			return new Terms();
		};

		add_action( 'init', function() use( $container ) {
			$container['sortables.terms']->check_for_database_column();
		} );

		if(
			(
				isset( $_GET['taxonomy'] ) || is_admin()
			) ||
			(
				defined( 'DOING_AJAX' ) && DOING_AJAX
			) ||
			(
				! is_admin()
			)
		) {

			add_filter( 'get_terms_orderby', function ( string $orderby ) use ( $container ) {
				return $container['sortables.terms']->sort_terms_by_order( $orderby );
			} );

			add_action( 'admin_enqueue_scripts', function () use ( $container ) {
				$container['sortables.terms']->enqueue_assets();
			} );

			add_action( 'wp_ajax_term_sort_update', function () use ( $container ) {
				$container['sortables.terms']->update_order();
			} );
		}
	}
}