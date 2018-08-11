<?php

namespace Tribe\Project\Shortener;


class Shortener {

	/**
	 * @var Database
	 */
	private $db;

	public function __construct( Database $db ) {
		$this->db = $db;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 * @throws \Exception on invalid URL.
	 */
	public function shorten( $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \Exception( 'Please provide a valid URL' );
		}

		if ( $this->shortened_url_exists( $url ) ) {
			return $this->get_shortened_url( $url );
		}

		return $this->shorten_url( $url );
	}

	/**
	 * @param $key
	 *
	 * @return string
	 * @throws \Exception on key thas has no associate URL.
	 */
	public function expand( $key ) {
		$url = $this->db->get_url( $key );
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new \Exception( 'We were not able to retrieve a URL with that information' );
		}

		return $url;
	}

	private function shortened_url_exists( $url ) {
		return (bool) $this->get_shortened_url( $url );
	}

	private function get_shortened_url( $url ) {
		static $short_url;

		if ( $short_url !== null ) {
			return $short_url;
		}

		$short_url = $this->db->get_shortened_url( $url );

		return $short_url;
	}

	private function shorten_url( $url ) {
		$short_url = substr( md5( mt_rand() ), 0, 8 );

		try {
			$this->db->save_url( $short_url, $url );
		} catch ( \Exception $e ) {
			return $url;
		}

		return $short_url;
	}

}