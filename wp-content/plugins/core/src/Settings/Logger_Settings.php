<?php
namespace Tribe\Project\Settings;

use Tribe\Libs\ACF\Field;
use Tribe\Libs\ACF\Group;

class Logger_Settings extends Contracts\ACF_Settings {

	const NAME = 'logger-settings';
	const AVAILABLE_LOGGERS = 'available-connections';

	public function get_title() {
		return __( 'Logger Settings', 'tribe' );
	}

	public function get_capability() {
		return 'manage_options';
	}

	public function get_parent_slug() {
		return 'options-general.php';
	}

	public function get_fields() {
		acf_add_local_field_group( $this->get_settings_group() );
	}

	private function get_settings_group() {
		$key = self::NAME;
		$group = new Group( $key );
		$group->set_attributes( [
			'title'      => __( 'Logger Settings', 'tribe' ),
			'location'   => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => $this->slug,
					],
				],
			],
		] );

		$connections = $this->get_available_connectors();
		if( empty( $connections ) ) {
			return [];
		}

		$choices = [];
		foreach( $connections as $connection ) {
			$choices[ $connection::NAME ] = $connection->get_label();
		}

		$field = new Field( self::AVAILABLE_LOGGERS );
		$field->set_attributes( [
			'label'         => __( 'Available Loggers', 'tribe' ),
			'name'          => self::AVAILABLE_LOGGERS,
			'type'          => 'checkbox',
			'choices'       => $choices,
			'instructions'  => __( 'Check all Loggers you wish to activate', 'tribe' )
		] );

		$group->add_field( $field );

		return $group->get_attributes();
	}

	public function get_available_connectors() {
		return apply_filters( 'tribe_logger_connections', [] );
	}
}