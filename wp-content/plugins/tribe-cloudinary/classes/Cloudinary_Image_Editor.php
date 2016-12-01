<?php
/**
 * Cloudinary Image Editor
 *
 * @author Mat Lipe
 * @version 1.0
 * @copyright Modern Tribe, Inc. 2012
 * @package Cloudinary
 **/

// Block direct requests
if( !defined( 'ABSPATH' ) )
	die( '-1' );

if( !class_exists( 'Cloudinary_Image_Editor' ) ) {

	/**
	 * Cloudinary Image Editor
	 *
	 * Ovverides the WP_Image_Editor class to pass everything through cloudinary
	 *
	 * @class Cloudinary_Image_Editor
	 * @package Cloudinary
	 *
	 */
	class Cloudinary_Image_Editor extends WP_Image_Editor_GD {
		
		
		/**
		 * _Save
		 * 
		 * Send an image to Cloudinary during any image transformation save
		 * 
		 * @param string $filename
		 * @param string $mime_type
		 * 
		 * 
		 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
		 */
		protected function _save( $image, $filename = null, $mime_type = null ) {
			
			$return = parent::_save( $this->image, $filename, $mime_type );

			if( empty( $filename ) ){
				if( !empty( $return[ 'path' ] ) ){
					$filename = $return[ 'path' ];
				} else {
					return $return;
				}
			}
			$info = pathinfo( $filename );
			$ext = $info[ 'extension' ];
			$name = basename( $filename, '.' .$ext );
			
			Tribe_Cloudinary::upload_to_cloudinary($name, $filename);

			return $return;
			
		}
		
		

		/**
		 * Multi Resize
		 * 
		 * Generate sizes meta data that points to cloudinary
		 * 
		 * @override
		 * 
		 * @see WP_Image_Editor_GD::multi_resize()
		 * 
		 *
		 * @param array $sizes {
		 *     An array of image size arrays. Default sizes are 'small', 'medium',
		 * 'large'.
		 *
		 *     Either a height or width must be provided.
		 *     If one of the two is set to null, the resize will
		 *     maintain aspect ratio according to the provided dimension.
		 *
		 *     @type array $size {
		 *         @type int  ['width']  Optional. Image width.
		 *         @type int  ['height'] Optional. Image height.
		 *         @type bool ['crop']   Optional. Whether to crop the image. Default
		 * false.
		 *     }
		 * }
		 * 
		 * 
		 * @return array An array of resized images' metadata by size.
		 */
		public function multi_resize($sizes) {		
			//image did not make it to cloudinary
			if( !in_array( basename( $this->file ), array_keys( Tribe_Cloudinary::$successful_uploads ) ) ){	
				return parent::multi_resize( $sizes );	
			}
			
			$metadata = array();
			$orig_size = $this->size;

			list( $width, $height ) = getimagesize( $this->file );

			foreach( $sizes as $size   => $size_data ) {
				if( !isset( $size_data[ 'width' ] ) && !isset( $size_data[ 'height' ] ) ) {
					continue;
				}

				//skip default sizes which are too large
				if( "large" == $size || "medium" == $size ){
					if( ((int)$size_data[ 'width' ] > $width ) && ( (int)$size_data[ 'height' ] > $height ) ){
						continue;
					}
				}


				if( !isset( $size_data[ 'width' ] ) ) {
					$size_data[ 'width' ] = null;
				}
				if( !isset( $size_data[ 'height' ] ) ) {
					$size_data[ 'height' ] = null;
				}

				if( !isset( $size_data[ 'crop' ] ) ) {
					$size_data[ 'crop' ] = false;
				}

				//$image = $this->_resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
				//$resized = $this->_save( $image );
				
				$this->update_size( $size_data['width'], $size_data['height'] );

				list($filename, $extension, $mime_type) = $this->get_output_format( null, null );

				$url = $this->generate_cloudinary_size_url( $extension, $size_data['width'], $size_data['height'], $size_data['crop'] );
				
				$parts = explode( '/', $url );
				
				if( is_array( $parts ) && sizeof( $parts) > 2 ){
					$img = array_pop( $parts );
					$params = array_pop( $parts );
					
					$folder = Tribe_Cloudinary::get_cloudinary_folder();
					if( !empty( $folder ) ){
						$img =  $folder . '/' . $img;	
					}
					
					$filename = $params . '/' . $img;
				}
				
				
				//full url to cloudinary
				$url = $this->create_size_url( $url, $filename );

				$resized = array(
					'path'        => $filename,
					'file'        => $filename,
					'width'       => $size_data['width'],
					'height'   	  => $size_data['height'],
					'mime-type'   => $mime_type,
					'url'         => $url
				);

				$metadata[ $size ] = $resized;

				$this->size = $orig_size;
			}

			return $metadata;
		}



		/**
		 * Create size url
		 * 
		 * Turn the url into a full working url with size data attached
		 * When using subfolder you must use the id in the url instead of generic
		 * 
		 * @param string $url - url received for api
		 * @param string $filename - filename with size params attached
		 * 
		 * @return string - combined into a working url
		 * 
		 */
		private function create_size_url( $url, $filename ){
			
			$basename = basename( $filename );
			if( !array_key_exists( $basename, Tribe_Cloudinary::$successful_uploads ) ){
				return $url;	
			}
			
			$params = explode('/', $filename );
			$params = array_shift( $params );
			
			$parts = explode( '/', Tribe_Cloudinary::$successful_uploads[ $basename ] );	
			
			foreach( $parts as $k => $part ){
				if( $part == 'upload' ){
					break;	
				}
			}
			$k++;
			$first = array_slice( $parts, 0, $k );
			$second = array_slice( $parts, $k, count( $parts ) );
			array_unshift( $second, $params );
			$parts = array_merge( $first, $second );
			$url = implode( '/', $parts );
			
			return $url;
			
		}


		/**
		 * Generate Cloudinary Size Url
		 * 
		 * Generated the url to the resource on cloudinary resized 
		 * 
		 * @see WP_Image_Editor::generate_filename() - this ecentially replaces that
		 * 
		 * @param [$extension] - optional specify extension
		 * @param int $width
		 * @param int $height
		 * @param bool $crop - like standard add_image_size() crop param
		 * 
		 * @return string url
		 * 
		 */
		private function generate_cloudinary_size_url( $extension = null, $width, $height, $crop = false ) {
			$suffix = $this->get_suffix();
			$info = pathinfo( $this->file );
			$ext = $info[ 'extension' ];

			$name = wp_basename( $this->file, ".$ext" );
			$new_ext = strtolower( $extension ? $extension : $ext );
			
			$options = array(
				'height' => $height,
				'width'  => $width,
				'dpr'    => 'auto',
				'secure' => true,
			);
			
			if( $crop ){
				$options[ 'crop' ] = 'fill';	
			} else {
				$options[ 'crop' ] = 'limit';
			}
			
			$url = Cloudinary::cloudinary_url( "{$name}.{$new_ext}", $options );

			return $url;
		}

	}



}
