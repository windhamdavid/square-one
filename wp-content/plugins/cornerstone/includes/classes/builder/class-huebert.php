<?php

class Cornerstone_Huebert {

  static $instance;

  public static function init() {

    self::$instance = new Cornerstone_Huebert;
  }

  public static function instance() {
  	if ( !isset( self::$instance ) ) {
      self::init();
    }
    return self::$instance;
  }

  function __construct() {
  	add_action( is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', array( $this, 'register' ) );
  }

  public function register() {
    wp_register_style( 'cs-huebert-style' , CS()->css( 'admin/huebert' ), array(), CS()->version() );
    wp_register_script( 'cs-huebert', CS()->js( 'admin/huebert' ), array( 'underscore', 'jquery' ), CS()->version(), true );
    wp_localize_script( 'cs-huebert', 'csHuebert', array(
    	'selectColor' => __( 'Select Color', 'cornerstone' ),
    ) );
  }

  public static function enqueue() {
    if ( !isset( self::$instance ) ) {
      self::init();
    }
    wp_enqueue_style( 'cs-huebert-style' );
    wp_enqueue_script( 'cs-huebert' );
  }

}
