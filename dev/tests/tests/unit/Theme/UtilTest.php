<?php


class UtilTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		tad\FunctionMocker\FunctionMocker::init();
	}

	protected function _after() {
	}

	// tests
	public function test_class_attribute() {
		$classes   = [ 'foo', 'bar', 'bash' ];
		$attribute = \Tribe\Project\Theme\Util::class_attribute( $classes, true );
		$string    = \Tribe\Project\Theme\Util::class_attribute( $classes, false );

		$expected_attribute = ' class="foo bar bash"';
		$expected_string    = 'foo bar bash';

		$this->assertEquals( $expected_attribute, $attribute );
		$this->assertEquals( $expected_string, $string );
	}

	public function test_array_to_attributes() {
		\tad\FunctionMocker\FunctionMocker::replace( 'esc_attr', function ( $attr ) {
			return $attr;
		} );

		$attributes = [ 'foo' => 'bar', 'bash' => 'baz', 'barz' => true ];
		$string     = \Tribe\Project\Theme\Util::array_to_attributes( $attributes );
		$expected   = "foo='bar' bash='baz' barz";


		$this->assertEquals( $expected, $string );
	}

	public function test_file_extension() {
		$file      = '/path/to/object.png';
		$extension = \Tribe\Project\Theme\Util::file_extension( $file );
		$expected  = 'png';

		$this->assertEquals( $expected, $extension );

		$file      = 'http://foobar.com/path/to/object.pdf';
		$extension = \Tribe\Project\Theme\Util::file_extension( $file );
		$expected  = 'pdf';

		$this->assertEquals( $expected, $extension );
	}
}