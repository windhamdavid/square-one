<?php
namespace Tribe\Project\Forms\Fields;

class Defaults {

	/**
	 * @var string
	 */
	private $label = '';

	/**
	 * @var string
	 */
	private $admin_label = '';

	/**
	 * @var boolean
	 */
	private $is_required = false;

	/**
	 * @var string
	 */
	private $size = 'medium';

	/**
	 * @var string
	 */
	private $error_message = '';

	/**
	 * @var array|null
	 */
	private $inputs = null;

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var boolean
	 */
	private $allow_prepopulate = false;

	/**
	 * @var string
	 */
	private $input_mask = '';

	/**
	 * @var string
	 */
	private $input_mask_value = '';

	/**
	 * @var string
	 */
	private $input_type = '';

	/**
	 * @var string
	 */
	private $label_placement = '';

	/**
	 * @var string
	 */
	private $description_placement = 'below';

	/**
	 * @var string
	 */
	private $sublabel_placement = '';

	/**
	 * @var string
	 */
	private $placeholder = '';

	/**
	 * @var string
	 */
	private $css_class = '';

	/**
	 * @var string
	 */
	private $input_name = '';

	/**
	 * @var string
	 */
	private $visibility = 'visible';

	/**
	 * @var boolean
	 */
	private $no_duplicates = false;

	/**
	 * @var string
	 */
	private $default_value = '';

	/**
	 * @var array|string
	 */
	private $choices = [];

	/**
	 * @var array|string
	 */
	private $conditional_logic = '';

	/**
	 * @var boolean
	 */
	private $enable_password_input = false;

	/**
	 * @var int
	 */
	private $max_length = '';

	/**
	 * @var boolean
	 */
	private $multiple_files = false;

	/**
	 * @var int
	 */
	private $max_files = 1;

	/**
	 * @var string
	 */
	private $calculation_formula = '';

	/**
	 * @var boolean
	 */
	private $calculation_rounding = false;

	/**
	 * @var boolean
	 */
	private $enable_calculation = false;

	/**
	 * @var boolean
	 */
	private $disable_quantity = false;

	/**
	 * @var boolean
	 */
	private $display_all_categories = false;

	/**
	 * @var boolean
	 */
	private $use_rich_text_editor = false;

	public function get_attribute( $name ) {
		return $this->$name;
	}

	public function set_attribute( string $name, $value ) {
		$this->$name = $value;
	}
}