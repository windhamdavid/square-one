<?php

namespace Tribe\Project;

use Tribe\Tests\SquareOneTestCase;

class Core_Test extends SquareOneTestCase {
	public function test_we_set_up_the_test_suite_correctly() {
		$this->assertTrue( true );
		$this->assertTrue( is_plugin_active( 'core/core.php' ) );
	}
}