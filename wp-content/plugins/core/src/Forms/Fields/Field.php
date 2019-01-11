<?php
namespace Tribe\Project\Forms\Fields;

abstract class Field {

	protected $type = null;

	protected $form_id;

	protected $atts;

	public function __construct( int $form_id ) {
		$this->form_id = $form_id;
		$this->atts = new Defaults();
	}

	/**
	 * @param float $field_id
	 * @param array $args
	 **
	 * @return array|\WP_Error
	 */
	public function set_attributes( float $field_id, array $args = [] ) {
		if( empty( $this->type ) ) {
			return new \WP_Error( 'form-type-empty', __( 'Form type cannot be empty.', 'tribe' ) );
		}

		$this->atts->set_attribute( 'id', $field_id );
		$this->atts->set_attribute( 'type', $this->type );
		$this->atts->set_attribute( 'formId', $this->form_id );

		foreach( $args as $key => $arg ) {
			if( ! property_exists( $this->atts, $key ) ) {
				return new \WP_Error( 'form-invalid-property', sprintf( __( 'Unable to set %s as it is not a valid property.', 'tribe' ), esc_html( $key ) ) );
			}

			$this->atts->set_attribute( $key, $arg );
		}

		return (array) $this->atts;
	}
}