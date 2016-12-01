<?php
/**
 * Tribe Cloudinary
 *
 * @author Mat Lipe
 * @version 1.0
 * @copyright Modern Tribe, Inc. 2012
 * @package Cloudinary
 **/

// Block direct requests
if( !defined( 'ABSPATH' ) )
	die( '-1' );

if( !class_exists( 'Tribe_Cloudinary' ) ) {

	/**
	 * Cloudinary
	 *
	 * Main class for the Cloudinary plugin.
	 * This implements neccesary hooks etc
	 *
	 * @class Tribe_Cloudinary'
	 * @package Cloudinary
	 *
	 */
	class Tribe_Cloudinary {

		const META_KEY = 'cloudinary';
		const FOLDER_META_KEY = 'cloudinary_folder';

		/**
		 * Successful Uploads
		 *
		 * Holds a list of files which were successfuly uploaded to cloudinary
		 * @uses to prevent generating meta pointing to a failed upload
		 *
		 * @var array
		 */
		public static $successful_uploads = array( );

		/**
		 * Plugin path
		 *
		 * Used along with self::plugin_path() to return path to this plugins files
		 *
		 * @var string
		 */
		private static $plugin_path = false;

		/**
		 * Plugin url
		 * To keep track of this plugins root dir
		 * Used along with self::plugin_url() to return url to plugin files
		 *
		 * @var string
		 *
		 */
		private static $plugin_url;

		private static $folder;

		/**
		 * Constructor
		 *
		 * @uses called via self::init()
		 *
		 */
		public function __construct() {

			//bad things will happen if setting are none existant
			if( !Cloudinary_Settings::has_settings( ) ) {
				return;
			}

			\Cloudinary::config( array(
				"cloud_name"   => get_option( 'cloudinary_cloud_name' ),
				"api_key"   => get_option( 'cloudinary_api_key' ),
				"api_secret"   => get_option( 'cloudinary_api_secret' )
			) );

			$this->hooks( );

		}


		/**
		 * Hooks
		 *
		 * Add the necessary hooks and filters
		 *
		 * @return void
		 */
		public function hooks() {
			add_filter( 'wp_image_editors', array( $this, 'change_image_editor' ), 9, 1 );
			add_filter( 'wp_get_attachment_url', array( $this, 'change_image_url' ), 99, 2 );
			add_filter( 'image_downsize', array( $this, 'image_downsize' ), 99, 3 );

			add_filter( 'wp_generate_attachment_metadata', array( $this, 'add_cloudinary_meta' ), 9, 2 );
			add_filter( 'update_attached_file', array( $this, 'update_transformed_image_meta' ), 9, 2 );
			add_filter( 'wp_handle_upload', array( $this, 'add_file_to_cloudinary' ), 1, 1 );

		}


		/**
		 * add_cloudinary_tag
		 *
		 * Add a tag to an existing cloudinary image
		 * Will verify the image was saved to cloundiary previously
		 * Will replace any existing tags
		 *
		 * @param int $attachment_id
		 * @param string $tag
		 *
		 *
		 * @return void|\Cloudinary\Api\Response
		 *
		 */
		public function add_cloudinary_tag( $attachment_id, $tag ){
			if( wp_is_post_autosave( $attachment_id ) || wp_is_post_revision( $attachment_id ) ){
				return;
			}

			if( !$url = $this->is_cloudinary_image( $attachment_id ) ){
				return;
			}


			$public_id = pathinfo( $url, PATHINFO_FILENAME );

			if( $folder = get_post_meta( $attachment_id, self::FOLDER_META_KEY, true ) ){
				$public_id = $folder . "/" . $public_id;

			}

			$api = new \Cloudinary\Api();

			$success = $api->update(
				$public_id,
				array( "tags"    => $tag,
				       'command' => 'replace'
				)
			);

			return $success;

		}



		/**
		 * Update Transformed Image Meta
		 *
		 * When you transform an image this will fire to update cloudinary url
		 *
		 * @param string $file - full location
		 * @param int    $attachment_id
		 *
		 * @return $file ( untouched !!! )
		 *
		 */
		public function update_transformed_image_meta( $file, $attachment_id ){

			if( in_array( basename( $file ), array_keys( self::$successful_uploads )) ){
				update_post_meta( $attachment_id, self::META_KEY, self::$successful_uploads[ basename( $file ) ] );
				if( self::get_cloudinary_folder() ){
					update_post_meta( $attachment_id, self::FOLDER_META_KEY, self::get_cloudinary_folder() );
				} else {
					delete_post_meta( $attachment_id, self::FOLDER_META_KEY );
				}
			}

			//filter must return
			return $file;

		}


		/**
		 * Get Cloudinary Folder
		 *
		 * Retrieve the folder to use if set
		 *
		 * @return string
		 */
		public static function get_cloudinary_folder() {
			if( !empty( self::$folder ) ){
				return self::$folder;
			}
			return self::$folder = get_option( 'cloudinary_folder' );

		}


		/**
		 * Updates just for the round so when we append the folder
		 * to image meta etc. it will know where the image belongs
		 *
		 * @param $folder
		 *
		 * @static
		 *
		 * @return void
		 */
		public static function update_cloudinary_folder( $folder ){
			self::$folder = $folder;
		}


		/**
		 * Add Cloudinary Meta
		 *
		 * Add a marker to let us know this image is avaialbe via cloundiary
		 *
		 * @param array $metadata      An array of attachment meta data.
		 * @param int   $attachment_id Current attachment ID.
		 *
		 * @return array
		 */
		public function add_cloudinary_meta($meta, $attachment_id) {
			if( isset($meta[ 'file' ]) && in_array( basename( $meta[ 'file' ] ), array_keys( self::$successful_uploads ) ) ) {
				add_post_meta( $attachment_id, self::META_KEY, self::$successful_uploads[ basename( $meta[ 'file' ] ) ], true );
				if( self::get_cloudinary_folder() ){
					add_post_meta( $attachment_id, self::FOLDER_META_KEY, self::get_cloudinary_folder(), true );
				}

			}

			return $meta;
		}


		/**
		 * Add File to Cloundinary
		 *
		 * Uploads any new file to cloudinary so we can pull the images from there
		 *
		 * @uses Cloudinary\Uploader
		 *
		 * @param array - newly uploaded file info
		 *
		 * @return array - same data we are just hijacking the filter
		 *
		 */
		public function add_file_to_cloudinary( $params ) {

			if( !@exif_imagetype( $params[ 'file' ] ) ){
				return $params;
			}

			$info = pathinfo( $params[ 'file' ] );
			$ext = $info[ 'extension' ];
			$name = basename( $params[ 'file' ], '.' . $ext );
			$filename = $params[ 'file' ];

			self::upload_to_cloudinary($name, $filename);

			return $params;
		}


		/**
		 * Upload to Cloudinary
		 *
		 * @param string $name - name of file without extension
		 * @param string $filname - file name and extension ( full paths are ok )
		 *
		 * @return void
		 */
		public static function upload_to_cloudinary( $name, $filename ){
			$folder = Tribe_Cloudinary::get_cloudinary_folder();

			$args = array(
				'public_id'   	 => $name,
				'use_filename'   => true
			);
			if( !empty( $folder ) && ($folder == get_option( 'cloudinary_folder' ) ) ) {
				$args[ 'folder' ] = $folder . '/' . date( 'Y/m/d' );
				Tribe_Cloudinary::update_cloudinary_folder( $args[ 'folder' ] );
			}

			$upload_file_info = \Cloudinary\Uploader::upload( $filename, $args );

			if( !empty( $args[ 'folder' ] ) ) {
				$upload_file_info[ "public_id" ] = str_replace( $args[ 'folder' ] . '/', '', $upload_file_info[ "public_id" ] );
			}

			if( !empty( $upload_file_info[ "public_id" ] ) && $upload_file_info[ "public_id" ] == $name ) {
				Tribe_Cloudinary::$successful_uploads[ basename( $filename ) ] = $upload_file_info[ "secure_url" ];
			}

		}


		/**
		 * Change Image Url
		 *
		 * Change the default image url to cloudinary
		 *
		 * @param string $url
		 * @param int    $post_id
		 *
		 * @return string Url pointing to cloudinary
		 */
		public function change_image_url($url, $post_id) {

			//does not exist on cloudinary so use default
			if( !$cloud_url = self::is_cloudinary_image( $post_id ) ) {
				return $url;
			}

			return $cloud_url;

		}


		/**
		 * Image Downsize
		 *
		 * Filter out the folder to prevent duplicate folders in url
		 *
		 * @param bool $bool
		 * @param int $id
		 * @param string $size
		 *
		 * @return array|bool
		 *
		 */
		public function image_downsize($bool, $id, $size) {
			$img_url = wp_get_attachment_url( $id );

			$meta = wp_get_attachment_metadata( $id );
			$width = $height = 0;
			$is_intermediate = false;
			$img_url_basename = wp_basename( $img_url );

			// try for a new style intermediate size
			if( $intermediate = image_get_intermediate_size( $id, $size ) ) {
				if( self::is_cloudinary_image( $id ) ){
					$img_url = $this->maybe_replace_folder( $img_url, $id );
					$img_url = str_replace( $img_url_basename, $intermediate[ 'file' ], $img_url );
					$img_url = $this->maybe_reorder_url( $img_url, $id );

				} else {
					$img_url = $intermediate[ 'url' ];
				}

				$width = $intermediate[ 'width' ];
				$height = $intermediate[ 'height' ];
				$is_intermediate = true;

			} elseif( $size == 'thumbnail' ) {

				if( ($thumb_file = wp_get_attachment_thumb_file( $id )) && $info = getimagesize( $thumb_file ) ) {
					$img_url = $this->maybe_replace_folder( $img_url, $id );

					$img_url = str_replace( $img_url_basename, wp_basename( $thumb_file ), $img_url );
					$img_url = $this->maybe_reorder_url( $img_url, $id );

					$width = $info[ 0 ];
					$height = $info[ 1 ];
					$is_intermediate = true;
				}
			}

			if( !$width && !$height && isset( $meta[ 'width' ], $meta[ 'height' ] ) ) {
				// any other type: use the real image
				$width = $meta[ 'width' ];
				$height = $meta[ 'height' ];
			}

			$img_url = apply_filters( 'tribe_cloudinary_img_url', $img_url, $id, $size );

			if( $img_url ) {
				// we have the actual image size, but might need to further constrain it if
				// content_width is narrower
				list($width, $height) = image_constrain_size_for_editor( $width, $height, $size );

				return array(
					$img_url,
					$width,
					$height,
					$is_intermediate
				);
			}
			return false;
		}


		/**
		 * Maybe Reorder Url
		 *
		 * Done this way to fix the strange cloudinary bug.
		 * If size after id, the first time won't work
		 * The second time it does...
		 *
		 *
		 * @example http://images.steelcase.com/image/upload/c_fill,h_150,w_150/v1406246667/v4/1455916_10152018436486294_1070753939_n17.jpg - will work the first time
		 * http://images.steelcase.com/image/upload/v1406246667/c_fill,h_150,w_150/v4/1455916_10152018436486294_1070753939_n17.jpg - will not work until the first time url is called, then works fine !!! :(
		 *
		 * @param string $img_url
		 *
		 * @return string
		 *
		 */
		public function maybe_reorder_url( $img_url, $id ){

			if( !self::is_cloudinary_image( $id ) ){
				return $img_url;
			}


			$parts = explode( '/', $img_url );

			$k = array_search( 'upload', $parts );
			if( empty( $k ) ){
				return $img_url;
			}
			$k++;

			array_splice( $parts, $k, 0, $parts[ $k+1 ] );

			unset( $parts[ $k+2 ] );
			return implode( '/', $parts );

		}


		/**
		 * Maybe Replace Folder
		 *
		 * If we are working with a cloudinary url there was a folder at the time of upload
		 *
		 * @param string $img_url
		 * @param int $post_id
		 *
		 * @return string
		 *
		 */
		public function maybe_replace_folder($img_url, $post_id) {
			$cloudinary_url = self::is_cloudinary_image( $post_id );
			if( $cloudinary_url ) {
				if( $folder = get_post_meta( $post_id, self::FOLDER_META_KEY, true ) ) {
					$img_url = str_replace( $folder . '/', '', $cloudinary_url );
				}
			}

			return $img_url;

		}


		/**
		 * Is Cloudinary Image
		 *
		 * @param int $id - attachment id
		 *
		 * @return bool|string - if true will return url
		 */
		public static function is_cloudinary_image( $id ){
			return get_post_meta( $id, self::META_KEY, true );

		}


		/**
		 * Swap the default image editor with ours
		 *
		 * @param array $editors
		 *
		 * @return array( 'Cloudinary_Image_Editor' )
		 *
		 */
		public function change_image_editor($editors) {
			if( !Cloudinary_Settings::has_settings( ) ) {
				return $editors;
			}

			if( class_exists( 'Cloudinary_Image_Editor' ) ) {
				return array( 'Cloudinary_Image_Editor' );
			}

			return $editors;
		}


		/**
		 * Plugin Path
		 *
		 * Retreive the path this plugins dir
		 *
		 * @param string [$append] - optional path file or name to add
		 * @return string
		 *
		 * @uses self::$plugin_path
		 *
		 * @return string
		 */
		public static function plugin_path($append = '') {

			if( !self::$plugin_path ) {
				self::$plugin_path = trailingslashit( dirname( dirname( __FILE__ ) ) );
			}

			return self::$plugin_path . $append;
		}


		/**
		 * Plugin Url
		 *
		 * Retreive the url this plugins dir
		 *
		 * @param string [$append] - optional path file or name to add
		 * @return string
		 *
		 * @uses self::$plugin_url
		 *
		 *
		 * @return string
		 */
		public static function plugin_url($append = '') {

			if( !self::$plugin_url ) {
				self::$plugin_url = plugins_url( ) . '/' . trailingslashit( basename( self::plugin_path( ) ) );
			}

			return self::$plugin_url . $append;
		}


		/********** SINGLETON FUNCTIONS **********/

		/**
		 * Instance of this class for use as singleton
		 */
		private static $instance;

		/**
		 * Create the instance of the class
		 *
		 * @static
		 * @return void
		 */
		public static function init() {
			self::$instance = self::get_instance( );
		}


		/**
		 * Get (and instantiate, if necessary) the instance of the class
		 *
		 * @static
		 * @return self
		 */
		public static function get_instance() {
			if( !is_a( self::$instance, __CLASS__ ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


	}



}
