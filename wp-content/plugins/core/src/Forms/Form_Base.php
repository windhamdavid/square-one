<?php
namespace Tribe\Project\Forms;

use Tribe\Project\Forms\Config\Form_Config;

abstract class Form_Base {

	//const NAME = self::NAME;

	/**
	 * Stores the form ID, on creation, in the options table
	 *
	 * @action tribe/forms/created
	 *
	 * @param int $form_id
	 * @param Form_Config $form_config
	 */
	public function set_form_id_option( int $form_id, Form_Config $form_config ) {
		update_option( 'form-' . static::NAME, $form_id );
	}

	/**
	 * @return mixed
	 */
	public function get_form_id() {
		return get_option( 'form-' . static::NAME );
	}
}