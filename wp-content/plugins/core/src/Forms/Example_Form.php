<?php

namespace Tribe\Project\Forms;

use Tribe\Project\Forms\Config\Form_Config;
use Tribe\Project\Forms\Fields\Text;

class Example_Form extends Form_Base implements Form_Config {
	const NAME = 'example-form';

	const FIELD_FULL_NAME = 1;

	/**
	 * @return string
	 */
	public function get_name(): string {
		return self::NAME;
	}

	/**
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Example Form', 'tribe' );
	}

	/**
	 * @return array
	 */
	public function get_config(): array {
		return [
			'fields'        => $this->get_fields(),
			'button'        => $this->get_submit_button(),
			'confirmations' => $this->get_confirmation(),
			'notifications' => $this->get_notifications(),
		];
	}

	private function get_fields(): array {
		return [
			$this->get_name_field(),
		];
	}

	/**
	 * Form fields should be defined as an array of options. It would be difficult to define every single field with
	 * every single configuration. Generally, it's recommended to define the field in the Admin UI and export the form
	 * which will provide a JSON copfig of the form and it's fields. Frokm here, you can derive what needs to go into
	 * an individual field's configuration.
	 *
	 * Also note that the `id` parameter must be a unique number.
	 **
	 * @return array
	 */
	private function get_name_field(): array {
		$field = new Text( $this->get_form_id() );
		
		return $field->set_attributes( self::FIELD_FULL_NAME, [
			'label'        => esc_html__( 'Full Name', 'tribe' ),
			'isRequired'   => true,
			'size'         => 'medium',
			'errorMessage' => esc_html__( 'Invalid Data', 'tribe' ),
			'cssClass'     => 'example-form-full-name',
			'placeholder'  => esc_attr__( 'Full Name', 'tribe ' ),
		] );
	}

	/**
	 * Possible values can be derived from the JSON export of a form. See the `button` property.
	 *
	 * @return array
	 */
	private function get_submit_button() : array {
		return [
			'type' => 'text',
			'text' => esc_attr__( 'Submit', 'tribe' ),
		];
	}

	/**
	 * Possible values can be derived from the JSON export of a form. See the `confirmations` property.
	 *
	 * @return array
	 */
	private function get_confirmation() : array {
		$confirmation_id = uniqid();

		return [
			$confirmation_id => [
				'id'          => $confirmation_id,
				'name'        => esc_html__( 'Default Confirmation', 'tribe' ),
				'isDefault'   => true,
				'type'        => 'message',
				'message'     => esc_html__( 'Data Submitted.', 'tribe' ),
				'url'         => '',
				'pageId'      => '',
				'queryString' => '',

			],
		];
	}

	/**
	 * Possible values can be derived from the JSON export of a form. See the `notifications` property.
	 *
	 * @return array
	 */
	private function get_notifications() : array {
		$notification_id = uniqid();

		return [
			$notification_id => [
				'id'                => $notification_id,
				'name'              => esc_html__( 'Example Admin Notification', 'tribe' ),
				'type'              => 'message',
				'event'             => 'form_submission',
				'to'                => "{admin_email}",
				'toType'            => 'email',
				'bcc'               => '',
				'subject'           => sprintf( esc_html__( 'New submission from %s', 'tribe' ), '{form_title}' ),
				'message'           => '<p>{all_fields}</p>',
				'from'              => '{admin_email}',
				'fromName'          => get_bloginfo( 'name' ),
				'replyTo'           => '',
				'routing'           => null,
				'conditionalLogic'  => null,
				'disableAutoFormat' => false,
			],
		];
	}

	/**
	 * @param array $form
	 *
	 * @action gform_pre_submission
	 */
	public function handle_submission( array $form ) {
		// Do any custom processing of the form.
	}
}