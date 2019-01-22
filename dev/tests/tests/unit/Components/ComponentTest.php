<?php namespace Components;

use Codeception\Util\Stub;
use tad\FunctionMocker\FunctionMocker;
use Tribe\Project\Templates\Components\Component;

class Component_Fake extends Component {
	public function get_data(): array {
		$data                        = [];
		$data['classes_test']        = $this->merge_classes( [ 'foo', 'bar', 'bash' ], [ 'bash', 'baz', 'bing' ] );
		$data['classes_test_string'] = $this->merge_classes( [ 'foo', 'bar', 'bash' ], [ 'baz', 'bing' ], true );
		$data['attrs_test']          = $this->merge_attrs( [ 'foo' => 'bar', 'bash' => 'baz' ], [ 'bing' => 'bang' ] );
		$data['attrs_test_string']   = $this->merge_attrs( [ 'foo' => 'bar', 'bash' => 'baz' ], [ 'bing' => 'bang' ], true );

		return $data;
	}

	public function parse_options( array $options ): array {
		return $options;
	}
}

class ComponentTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected $component_fake;

	protected function _before() {
		FunctionMocker::init();

		$twig_mock = Stub::makeEmpty( \Twig_Environment::class );
		FunctionMocker::replace( 'apply_filters', function ( $filter ) use ( $twig_mock ) {
			if ( $filter === 'tribe/project/twig' ) {
				return $twig_mock;
			}

			return null;
		} );

		FunctionMocker::replace( 'esc_attr', function ( $attr ) {
			return $attr;
		} );

		$this->component_fake = new Component_Fake( '' );
	}

	protected function _after() {
	}

	public function testFactoryReturnsComponent() {
		$component = Component_Fake::factory( [], '' );

		$this->assertTrue( is_a( $component, Component_Fake::class ) );
	}

	public function testMergeClasses() {
		$component = Component_Fake::factory( [], '' );
		$data      = $component->get_data();
		$expected  = [ 'foo', 'bar', 'bash', 'baz', 'bing' ];

		$this->assertEquals( sort( $expected ), sort( $data['classes_test'] ) );
	}

	public function testMergeClassesString() {
		$component = Component_Fake::factory( [], '' );
		$data      = $component->get_data();
		$expected  = 'foo bar bash baz bing';

		$this->assertEquals( $expected, $data['classes_test_string'] );
	}

	public function testMergeAttrs() {
		$component = Component_Fake::factory( [], '' );
		$data      = $component->get_data();
		$expected  = [ 'foo' => 'bar', 'bash' => 'baz', 'bing' => 'bang' ];

		$this->assertEquals( sort( $expected ), sort( $data['attrs_test'] ) );
	}

	public function testMergeAttrsString() {
		$component = Component_Fake::factory( [], '' );
		$data      = $component->get_data();
		$expected  = 'foo=\'bar\' bash=\'baz\' bing=\'bang\'';

		$this->assertEquals( $expected, $data['attrs_test_string'] );
	}
}