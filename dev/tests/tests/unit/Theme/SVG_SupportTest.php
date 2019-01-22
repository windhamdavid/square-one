<?php


class SVG_SupportTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Tribe\Project\Util\SVG_Support
	 */
	protected $svg_support;

	protected function _before() {
		$this->svg_support = new \Tribe\Project\Util\SVG_Support();
	}

	protected function _after() {
	}

	// tests
	public function test_filter_mce_css() {
		$expected = 'foo, /wp-admin/admin-ajax.php?action=adminlc_mce_svg.css';
		$filtered = $this->svg_support->filter_mce_css( 'foo' );
		$this->assertEquals( $expected, $filtered );
	}

	public function test_remove_dimensions_svg() {
		$html     = '<div width="1" height="1">stuff</div>';
		$expected = '<div>stuff</div>';

		$filtered = $this->svg_support->remove_dimensions_svg( $html );

		$this->assertEquals( $expected, $filtered );
	}

	public function test_filter_mimes() {
		$mimes    = [ 'foo' => 'bar', 'bash' => 'baz' ];
		$expected = [ 'foo' => 'bar', 'bash' => 'baz', 'svg' => 'image/svg+xml' ];

		$filtered = $this->svg_support->filter_mimes( $mimes );

		$this->assertEquals( $expected, $filtered );
	}
}