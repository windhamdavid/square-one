<?php


namespace Tribe\Project\Service_Providers;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Libs\Assets\Asset_Loader;

class Clockr_Provider implements ServiceProviderInterface {

	public function register( Container $container ) {
		add_action( 'template_include', [ $this, 'show_api_code' ] );
	}

	public function show_api_code( $template ) {
		$code = filter_input( INPUT_GET, 'code' );

		if ( empty( $code ) ) {
			return $template;
		}

		$new_template = locate_template( array( 'page-templates/page-api-code.php' ) );
		if ( '' != $new_template ) {
			return $new_template;
		}

		return $template;
	}
}