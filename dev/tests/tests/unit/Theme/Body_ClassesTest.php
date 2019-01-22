<?php namespace Theme;

use tad\FunctionMocker\FunctionMocker;
use Tribe\Project\Theme\Body_Classes;

class Body_ClassesTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		FunctionMocker::init();
	}

	protected function _after() {
	}

	public function testNotSingular() {
		FunctionMocker::replace( 'is_singular', false );
		$body_classes = new Body_Classes();
		$expected     = [ 'foo', 'bar', 'bash' ];
		$classes      = $body_classes->body_classes( $expected );

		$this->assertEquals( $expected, $classes );
	}

	public function testSingular() {
		FunctionMocker::replace( 'is_singular', true );
		FunctionMocker::replace( 'sanitize_html_class', function ( $class ) {
			return $class;
		} );
		global $post;
		$post            = new \stdClass();
		$post->ID        = 1;
		$post->post_type = 'foobar-type';
		$post->post_name = 'foobar-post';

		$body_classes = new Body_Classes();
		$expected     = [ 'foo', 'bar', 'bash', 'foobar-type-foobar-post' ];
		$classes      = $body_classes->body_classes( $expected );

		$this->assertEquals( sort( $expected ), sort( $classes ) );
	}

	public function testSingularWithPanelsAndContent() {
		FunctionMocker::replace( 'is_singular', true );
		FunctionMocker::replace( 'sanitize_html_class', function ( $class ) {
			return $class;
		} );
		global $post;
		$post               = new \stdClass();
		$post->ID           = 1;
		$post->post_type    = 'foobar-type';
		$post->post_name    = 'foobar-post';
		$post->post_content = 'content';

		$body_classes = new Body_Classes();
		$expected     = [ 'foo', 'bar', 'bash', 'foobar-type-foobar-post', 'has-panels', 'has-page-content' ];
		$classes      = $body_classes->body_classes( $expected );

		$this->assertEquals( sort( $expected ), sort( $classes ) );
	}

	public function testSingularWithPanelsNoContent() {
		FunctionMocker::replace( 'is_singular', true );
		FunctionMocker::replace( 'sanitize_html_class', function ( $class ) {
			return $class;
		} );
		global $post;
		$post               = new \stdClass();
		$post->ID           = 1;
		$post->post_type    = 'foobar-type';
		$post->post_name    = 'foobar-post';

		$body_classes = new Body_Classes();
		$expected     = [ 'foo', 'bar', 'bash', 'foobar-type-foobar-post', 'has-panels', 'is-panels-page' ];
		$classes      = $body_classes->body_classes( $expected );

		$this->assertEquals( sort( $expected ), sort( $classes ) );
	}
}