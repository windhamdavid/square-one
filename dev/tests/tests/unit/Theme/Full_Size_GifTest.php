<?php namespace Theme;

use tad\FunctionMocker\FunctionMocker;
use Tribe\Project\Theme\Full_Size_Gif;

class Full_Size_GifTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		FunctionMocker::init();

		FunctionMocker::replace( 'image_downsize', 'Image was downsized' );
	}

	public function testFullSizeImagesIgnored() {
		FunctionMocker::replace( 'wp_get_attachment_metadata', [ 'file' => 'this/is/a/gif.gif' ] );
		$full_sized_gif = new Full_Size_Gif();
		$data           = 'Image was not downsized';
		$size           = 'full';

		$image_data = $full_sized_gif->full_size_only_gif( $data, 0, $size );

		$this->assertEquals( $data, $image_data );
	}

	public function testNonGifImagesIgnored() {
		FunctionMocker::replace( 'wp_get_attachment_metadata', [ 'file' => 'this/is/not/a/gif.jpg' ] );
		$full_sized_gif = new Full_Size_Gif();
		$data           = 'Image was not downsized';
		$size           = 'medium';

		$image_data = $full_sized_gif->full_size_only_gif( $data, 0, $size );

		$this->assertEquals( $data, $image_data );
	}

	public function testMissingFileMetaIgnored() {
		FunctionMocker::replace( 'wp_get_attachment_metadata', [] );
		$full_sized_gif = new Full_Size_Gif();
		$data           = 'Image was not downsized';
		$size           = 'medium';

		$image_data = $full_sized_gif->full_size_only_gif( $data, 0, $size );

		$this->assertEquals( $data, $image_data );
	}

	public function testGifsAreResized() {
		FunctionMocker::replace( 'wp_get_attachment_metadata', [ 'file' => 'this/is/a/gif.gif' ] );
		$full_sized_gif = new Full_Size_Gif();
		$data           = 'Image was not downsized';
		$size           = 'medium';

		$image_data = $full_sized_gif->full_size_only_gif( $data, 0, $size );

		$this->assertEquals( 'Image was downsized', $image_data );
	}

	public function testGifsAreRecognized() {
		$full_sized_gif = new Full_Size_Gif();
		$this->assertTrue( $full_sized_gif->is_gif( 'path/to/gif.gif' ) );
		$this->assertFalse( $full_sized_gif->is_gif( 'path/to/jpg.jpg' ) );
	}
}