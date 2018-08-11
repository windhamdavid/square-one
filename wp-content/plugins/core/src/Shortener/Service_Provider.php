<?php

namespace Tribe\Project\Shortener;

use Pimple\Container;
use Tribe\Project\Shortener\Post_Type\Provider;

class Service_Provider implements \Pimple\ServiceProviderInterface {

	const DATABASE   = 'shortener.database';
	const SHORTENER  = 'shortener.shortener';
	const REDIRECTOR = 'shortener.redirector';

	public function register( Container $container ) {
		if ( ! is_multisite() ) {
			return;
		}

		$container[ self::DATABASE ] = function () {
			return new Database();
		};

		$container[ self::SHORTENER ] = function () use ( $container ) {
			return new Shortener( $container[ self::DATABASE ] );
		};

		$container[ self::REDIRECTOR ] = function () use ( $container ) {
			return new Redirector( $container[ self::DATABASE ] );
		};

		// Create the table if it doesn't exist.
		add_action( 'admin_init', function () use ( $container ) {
			if ( $container[ self::DATABASE ]->update_required() ) {
				$container[ self::DATABASE ]->do_updates();
			}
		}, 10, 0 );

		// Register the table.
		add_action( 'init', function () use ( $container ) {
			$container[ self::DATABASE ]->register();
		} );

		if ( get_current_blog_id() === constant( 'URL_SHORTENER_SITE_ID' ) ) {
			add_action( 'template_redirect', function () use ( $container ) {
				$container[ self::REDIRECTOR ]->redirect();
			} );
		}

	}
}

/**
 * Endpoints
 * - Create (restricted to whitelisted domains? Verify a url is not already shortened?)
 * - Update
 * - Delete
 */