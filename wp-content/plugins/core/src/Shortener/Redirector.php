<?php

namespace Tribe\Project\Shortener;


class Redirector {

	/**
	 * @var Database
	 */
	protected $db;

	public function __construct( Database $db ) {
		$this->db = $db;
	}

	public function redirect() {
		global $wp;

		$redirect_url = $this->db->get_url( $wp->request );

		if ( filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		wp_redirect( apply_filters( 'tribe/shortener/no-redirect-found', constant( 'URL_SHORTENER_NO_REDIRECT_FOUND' ) ) );
		exit;
	}

}