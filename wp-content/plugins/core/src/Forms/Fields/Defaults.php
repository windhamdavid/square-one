<?php
namespace Tribe\Project\Forms\Fields;

class Defaults {

	/**
	 * @var string
	 */
	public $label = '';

	/**
	 * @var string
	 */
	public $adminLabel = '';

	/**
	 * @var boolean
	 */
	public $isRequired = false;

	/**
	 * @var string
	 */
	public $size = 'medium';

	/**
	 * @var string
	 */
	public $errorMessage = '';

	/**
	 * @var array|null
	 */
	public $inputs = null;

	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * @var boolean
	 */
	public $allowPrepopulate = false;

	/**
	 * @var string
	 */
	public $inputMask = '';

	/**
	 * @var string
	 */
	public $inputMaskValue = '';

	/**
	 * @var string
	 */
	public $inputType = '';

	/**
	 * @var string
	 */
	public $labelPlacement = '';

	/**
	 * @var string
	 */
	public $descriptionPlacement = 'below';

	/**
	 * @var string
	 */
	public $sublabelPlacement = '';

	/**
	 * @var string
	 */
	public $placeholder = '';

	/**
	 * @var string
	 */
	public $cssClass = '';

	/**
	 * @var string
	 */
	public $inputName = '';

	/**
	 * @var string
	 */
	public $visibility = 'visible';

	/**
	 * @var boolean
	 */
	public $noDuplicates = false;

	/**
	 * @var string
	 */
	public $defaultValue = '';

	/**
	 * @var array|string
	 */
	public $choices = [];

	/**
	 * @var array|string
	 */
	public $conditionalLogic = '';

	/**
	 * @var boolean
	 */
	public $enablePasswordInput = false;

	/**
	 * @var int
	 */
	public $maxLength = '';

	/**
	 * @var boolean
	 */
	public $multipleFiles = false;

	/**
	 * @var int
	 */
	public $maxFiles = 1;

	/**
	 * @var string
	 */
	public $calculationFormula = '';

	/**
	 * @var boolean
	 */
	public $calculationRounding = false;

	/**
	 * @var boolean
	 */
	public $enableCalculation = false;

	/**
	 * @var boolean
	 */
	public $disableQuantity = false;

	/**
	 * @var boolean
	 */
	public $displayAllCategories = false;

	/**
	 * @var boolean
	 */
	public $useRichTextEditor = false;

	public function get_attribute( $name ) {
		return $this->$name;
	}

	public function set_attribute( string $name, $value ) {
		$this->{$name} = $value;
	}
}