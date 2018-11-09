<?php

namespace Tribe\Project\Widgets;

/**
 * Class Widget
 * @package Tribe\Project\Widgets
 */
abstract class Widget extends \WP_Widget {

	/**
	 * Widget constructor.
	 */
	public function __construct() {
		parent::__construct( $this->get_slug(), $this->get_title(), $this->get_options() );
	}

	/**
	 *
	 */
	public function register_widget() {
		register_widget( \get_class( $this ) );
	}

	/**
	 * @param array $args
	 * @param array $options
	 */
	public function widget( $args, $options ) {
		echo $args['before_widget'];
		echo $args['before_title'];
		echo $args['widget_name'];
		echo $args['after_title'];
		echo $this->render_display( $args, $options );
		echo $args['after_widget'];
	}

	/**
	 * @param array $options
	 *
	 * @return string|void
	 */
	public function form( $options ) {
		echo $this->render_admin( $options );
	}

	/**
	 * @param array $new_options
	 * @param array $old_options
	 *
	 * @return array
	 */
	public function update( $new_options, $old_options ) {
		return $new_options;
	}

	abstract function get_slug();
	abstract function get_title();
	abstract function get_options();

	abstract function render_admin( $options );
	abstract function render_display( $args, $options );
}
