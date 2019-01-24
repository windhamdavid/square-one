<?php namespace Theme;

use tad\FunctionMocker\FunctionMocker;
use Tribe\Project\Theme\Gravity_Forms_Filter;

class Gravity_Forms_FilterTest extends \Codeception\Test\Unit {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		FunctionMocker::init();
	}

	protected function _after() {
	}

	public function testChoiceMarkup() {
		FunctionMocker::replace( '__', function ( $string ) {
			return $string;
		} );

		$choice_markup = '<li>Choice</li>';
		$expected      = '<li>Choice<label for="choice_99_88_1" class="gf-radio-checkbox-other-placeholder"><span class="a11y-visual-hide">Other</span></label></li>';
		$field         = [
			'formId'  => 99,
			'id'      => 88,
			'choices' => [
				0 => 'foo',
				1 => 'bar',
			],
		];
		$choice        = [ 'isOtherChoice' => true ];

		$filter     = new Gravity_Forms_Filter();
		$new_markup = $filter->customize_gf_choice_other( $choice_markup, $choice, $field, '' );

		$this->assertEquals( $expected, $new_markup );
	}

	public function testChoiceMarkupIgnoresNonOther() {
		FunctionMocker::replace( '__', function ( $string ) {
			return $string;
		} );

		$choice_markup = '<li>Choice</li>';
		$field         = [
			'formId'  => 99,
			'id'      => 88,
			'choices' => [
				0 => 'foo',
				1 => 'bar',
			],
		];
		$choice        = [ 'isOtherChoice' => false ];

		$filter     = new Gravity_Forms_Filter();
		$new_markup = $filter->customize_gf_choice_other( $choice_markup, $choice, $field, '' );

		$this->assertEquals( $choice_markup, $new_markup );
	}

	public function testDisableAnimations() {
		$filter   = new Gravity_Forms_Filter();
		$form     = [ 'enableAnimation' => true ];
		$new_form = $filter->deactivate_gf_animations( $form );

		$this->assertFalse( $new_form['enableAnimation'] );
	}

	public function testEnableAnimationsIgnoredIfEmpty() {
		$filter   = new Gravity_Forms_Filter();
		$form     = [];
		$new_form = $filter->deactivate_gf_animations( $form );

		$this->assertFalse( array_key_exists( 'enableAnimation', $new_form ) );
	}

	/**
	 * @dataProvider classProvider
	 */
	public function testClassAdditions( $field, $classes, $expected, $message ) {
		$filter      = new Gravity_Forms_Filter();
		$new_classes = $filter->add_gf_select_field_class( $classes, $field, null );

		$this->assertEquals( $expected, $new_classes, $message );
	}

	public function classProvider() {
		return [
			[
				[ 'type' => 'multiselect', 'inputType' => '', 'enablePasswordInput' => false ],
				'foo bar bash',
				'foo bar bash gf-multi-select',
				'Multiselect receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'multiselect', 'enablePasswordInput' => false ],
				'foo bar bash',
				'foo bar bash gf-multi-select',
				'Multiselect receives correct classes.',
			],
			[
				[ 'type' => 'select', 'inputType' => '', 'enablePasswordInput' => false, 'enableEnhancedUI' => false ],
				'foo bar bash',
				'foo bar bash gf-select gf-select-no-chosen',
				'Select receives correct classes.',
			],
			[
				[ 'type' => 'select', 'inputType' => '', 'enablePasswordInput' => false, 'enableEnhancedUI' => true ],
				'foo bar bash',
				'foo bar bash gf-select',
				'Select receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'select', 'enablePasswordInput' => false, 'enableEnhancedUI' => true ],
				'foo bar bash',
				'foo bar bash gf-select',
				'Select receives correct classes.',
			],
			[
				[ 'type' => 'checkbox', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-checkbox',
				'Checkbox receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'checkbox' ],
				'foo bar bash',
				'foo bar bash gf-checkbox',
				'Checkbox receives correct classes.',
			],
			[
				[ 'type' => 'radio', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-radio',
				'Radio receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'radio' ],
				'foo bar bash',
				'foo bar bash gf-radio',
				'Radio receives correct classes.',
			],
			[
				[ 'type' => 'textarea', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-textarea',
				'Textarea receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'textarea' ],
				'foo bar bash',
				'foo bar bash gf-textarea',
				'Textarea receives correct classes.',
			],
			[
				[ 'type' => 'post_content', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-textarea',
				'Textarea receives correct classes.',
			],
			[
				[ 'type' => 'post_excerpt', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-textarea',
				'Textarea receives correct classes.',
			],
			[
				[ 'type' => 'date', 'inputType' => '', 'dateType' => 'datepicker' ],
				'foo bar bash',
				'foo bar bash gf-date gf-date-layout-datepicker',
				'Date receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'date', 'dateType' => 'datepicker' ],
				'foo bar bash',
				'foo bar bash gf-date gf-date-layout-datepicker',
				'Date receives correct classes.',
			],
			[
				[ 'type' => 'date', 'inputType' => '', 'dateType' => 'not-datepicker' ],
				'foo bar bash',
				'foo bar bash gf-date gf-date-layout-not-datepicker',
				'Date receives correct classes.',
			],
			[
				[ 'type' => 'time', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-time',
				'Time receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'time' ],
				'foo bar bash',
				'foo bar bash gf-time',
				'Time receives correct classes.',
			],
			[
				[ 'type' => 'phone', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-phone',
				'Phone receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'phone' ],
				'foo bar bash',
				'foo bar bash gf-phone',
				'Phone receives correct classes.',
			],
			[
				[ 'type' => 'name', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-name',
				'Name receives correct classes.',
			],
			[
				[ 'type' => 'address', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-address',
				'Address receives correct classes.',
			],
			[
				[ 'type' => 'email', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-email',
				'Email receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'email' ],
				'foo bar bash',
				'foo bar bash gf-email',
				'Email receives correct classes.',
			],
			[
				[ 'type' => 'website', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-url',
				'Website receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'website' ],
				'foo bar bash',
				'foo bar bash gf-url',
				'Website receives correct classes.',
			],
			[
				[ 'type' => 'fileupload', 'inputType' => '' ],
				'foo bar bash',
				'foo bar bash gf-file',
				'File Upload receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => 'fileupload' ],
				'foo bar bash',
				'foo bar bash gf-file',
				'File Upload receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => '', 'enablePasswordInput' => true ],
				'foo bar bash',
				'foo bar bash gf-password',
				'Password Upload receives correct classes.',
			],
			[
				[ 'type' => '', 'inputType' => '', 'enablePasswordInput' => false ],
				'foo bar bash',
				'foo bar bash',
				'Password Upload receives correct classes.',
			],
		];
	}
}