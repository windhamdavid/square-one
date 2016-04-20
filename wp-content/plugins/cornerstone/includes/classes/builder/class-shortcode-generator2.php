<?php

class Cornerstone_Shortcode_Generator2 extends Cornerstone_Plugin_Component {

  static $instance;
  private $shortcodes = array();
  private $sections = array();

  public function setup() {

  	//add_action( 'cornerstone_load_builder', array( $this, 'start' ) );

  }

  public function start() {

    add_action( 'media_buttons', array( $this, 'addMediaButton' ), 999 );
    add_action( 'cornerstone_generator_preview_before', array( $this, 'previewBefore' ) );

  }

  public function enqueue( ) {

  	$this->plugin->component( 'Core_Scripts' )->register_scripts();

    wp_enqueue_style( 'cs-generator2-css' , CS()->css( 'admin/generator2' ), array(), CS()->version() );

    wp_register_script( 'cs-generator2', CS()->js( 'admin/generator2' ), array( 'cs-core' ), CS()->version(), true );
    wp_localize_script( 'cs-generator2', 'csGeneratorData', $this->getData() ) ;
    wp_enqueue_script( 'cs-generator2' );

  }

  public function getData() {
    return array(
      'previewContentBefore' => $this->getPreviewContentBefore(),
      'previewContentAfter' => $this->getPreviewContentAfter(),
      'strings' => CS()->config( 'builder/strings-generator' )
    );
  }

  public function getPreviewContentBefore() {
    ob_start();
    do_action('cornerstone_generator_preview_before');
    return ob_get_clean();
  }

  public function getPreviewContentAfter() {
    ob_start();
    do_action('cornerstone_generator_preview_after');
    return ob_get_clean();
  }

  public function previewBefore() {
    return '<p>' . __('Click the button below to check out a live example of this shortcode', 'cornerstone' ) . '</p>';
  }

  public function addMediaButton( $editor_id ) {
    $this->enqueue();
    $title = sprintf( __( 'Insert Shortcodes', 'cornerstone' ) );
    $contents = CS()->view( 'svg/nav-elements-solid', false );
    echo "<button href=\"#\" title=\"{$title}\" id=\"cs-insert-shortcode-button\" class=\"button cs-insert-btn\" data-editor=\"$editor_id\">{$contents}</button>";
  }

}