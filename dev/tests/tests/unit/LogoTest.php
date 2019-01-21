<?php


class LogoTest extends \PHPUnit\Framework\TestCase {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Tribe\Project\Theme\Logo
	 */
	protected $logo;

	protected function _before() {
		tad\FunctionMocker\FunctionMocker::init();
	}

	protected function _after() {
	}

	// tests
	public function test_get_logo_internal() {
		\tad\FunctionMocker\FunctionMocker::replace( 'is_front_page', function () {
			return false;
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'esc_url', function ( $url ) {
			return $url;
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'home_url', function () {
			return 'http://foobar.com';
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'get_bloginfo', function () {
			return 'Foobar';
		} );

		$logo              = \Tribe\Project\Theme\Logo::logo( [ 'echo' => false ] );
		$expected_internal = '<div class="logo" data-js="logo"><a href="http://foobar.com" rel="home">Foobar</a></div>';

		$this->assertEquals( $expected_internal, $logo );
	}

	public function test_get_logo_front() {
		\tad\FunctionMocker\FunctionMocker::replace( 'is_front_page', function () {
			return true;
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'esc_url', function ( $url ) {
			return $url;
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'home_url', function () {
			return 'http://foobar.com';
		} );
		\tad\FunctionMocker\FunctionMocker::replace( 'get_bloginfo', function () {
			return 'Foobar';
		} );

		$logo           = \Tribe\Project\Theme\Logo::logo( [ 'echo' => false ] );
		$expected_front = '<h1 class="logo" data-js="logo"><a href="http://foobar.com" rel="home">Foobar</a></h1>';

		$this->assertEquals( $expected_front, $logo );
	}
}