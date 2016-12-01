<?php
/**
 * Cloudinary Settings
 *
 * Settings page which is a submenu of Careers
 *
 * @author Mat Lipe
 * @copyright Modern Tribe, Inc. 2014
 * @package Cloudinary
 *
 **/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

if ( !class_exists('Cloudinary_Setttings') ) {

	/**
	 * Cloudinary Settings
	 * 
	 * Generates and manages the settings page
	 *
	 * @package Cloudinary
	 */
	class Cloudinary_Settings {
		const SLUG = 'cloudinary_settings';

		
		/**
		 * Construct
		 * 
		 * Runs when init() is called the first time
		 */
		function __construct(){
			add_action( 'admin_menu', array( $this, 'register_settings_page' ), 10, 0 );
		}
		
		
		/**
		 * Register Setting Page
		 * 
		 * Creates the submenu and registers the option
		 * 
		 * @return void
		 */
		public function register_settings_page() {
			add_options_page(
				__( 'Cloudinary', 'cloudinary'), 
				__( 'Cloudinary', 'cloudinary'), 
				'manage_options', 
				'cloudinary', 
				array( $this, 'display_settings_page' )
			);

			add_settings_section(
				'api',
				__( 'API Settings', 'cloudinary' ),
				array( $this, 'api_description'),
				self::SLUG
			);

			add_settings_field(
				'cloudinary_cloud_name',
				__( 'Cloud Name', 'cloudinary' ),
				array( $this, 'text' ),
				self::SLUG,
				'api',
				'cloud_name'
			);
			
			add_settings_field(
				'cloudinary_api_key',
				__( 'API Key', 'cloudinary' ),
				array( $this, 'text' ),
				self::SLUG,
				'api',
				'api_key'
			);
			
			add_settings_field(
				'cloudinary_api_secret',
				__( 'API Secret', 'cloudinary' ),
				array( $this, 'text' ),
				self::SLUG,
				'api',
				'api_secret'
			);
			
			add_settings_field(
				'cloudinary_folder',
				__( 'Cloudinary Folder (Optional)', 'cloudinary' ),
				array( $this, 'text' ),
				self::SLUG,
				'api',
				'folder'
			);
			
			
			
			register_setting(
				self::SLUG,
				'cloudinary_cloud_name'
			);
			
			register_setting(
				self::SLUG,
				'cloudinary_api_key'
			);
			
			register_setting(
				self::SLUG,
				'cloudinary_api_secret'
			);
			
			register_setting(
				self::SLUG,
				'cloudinary_folder'
			);
		}
		
		
		
		/**
		 * Text
		 * 
		 * Univeral text field
		 * 
		 * @param string $field - field minus the 'cloudinary'
		 * 
		 * @return void
		 */
		public function text( $field ){
			$value = get_option( 'cloudinary_' . $field, null );
			?>
			<input name="cloudinary_<?php echo $field; ?>" value="<?php echo $value; ?>" />	
			<?php
		}
		
		
		/**
		 * Api Description
		 * 
		 * Description for the Api
		 * 
		 * @return void
		 */
		function api_description(){
			echo '<p class="description">';	
				printf( __( 'Please enter your Cloudinary settings bellow. You can look up your settings %shere%s: .', 'cloudinary' ), '<a href="https://cloudinary.com/console">', '</a>' );
			echo '</p>';
			
		}
		
		
		/**
		 * Display Settings Page
		 *
		 * Outputs the settings page
		 *
		 * @return void
		 */
		public function display_settings_page() {
			?>
			<div class="wrap">
				<h2><?php _e( 'Cloudinary Settings', 'cloudinary' ); ?></h2>
				<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
					<?php
					settings_fields( self::SLUG );
					do_settings_sections( self::SLUG );
					submit_button();
					?>
				</form>
			</div>
			<?php

		}
		
		/**
		 * Has Settings
		 * 
		 * Check to make sure settings are entered
		 * 
		 * @return bool
		 */
		public static function has_settings(){
			$secret = get_option( 'cloudinary_api_secret' );
			$key    = get_option( 'cloudinary_api_key' );
			$name   = get_option( 'cloudinary_cloud_name' );
			if( empty( $secret ) || empty( $key )    || empty( $name) ){				
				return false;		
			}
				
			return true;
			
		}
		

		/********** SINGLETON FUNCTIONS **********/

		/* Don't edit below here! */

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
			self::$instance = self::get_instance();
		}

		/**
		* Get (and instantiate, if necessary) the instance of the class
		*
		* @static
		* @return Cloudinary_Setttings
		*/
		public static function get_instance() {
			if ( !is_a(self::$instance, __CLASS__) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

	}
}
	