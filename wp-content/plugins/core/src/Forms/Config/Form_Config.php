<?php
namespace Tribe\Project\Forms\Config;


interface Form_Config {

	/**
	 * @return string The identifier for the form
	 */
	public function get_name(): string;

	/**
	 * @return string The title of the form
	 */
	public function get_title(): string;

	/**
	 * Get the configuration for the form. The returned array
	 * should contain the field definitions on the `field`
	 * property. See \Tribe\Project\Forms\Form_Override::fill_defaults()
	 * for other possible keys
	 *
	 * @return array
	 */
	public function get_config(): array;

}