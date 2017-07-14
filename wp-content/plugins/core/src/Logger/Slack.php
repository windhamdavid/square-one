<?php
namespace Tribe\Project\Logger;

use Tribe\Libs\ACF\Field;
use Tribe\Libs\ACF\Group;
use Tribe\Project\Settings\Logger_Settings;

class Slack extends Base_Logger {

	const NAME = 'slack';

	const SLACK_API_KEY = 'slack-api-key';
	const SLACK_CHANNEL = 'slack-channel';

	public function get_label() {
		return __( 'Slack Integration', 'tribe' );
	}

	/**
	 * Every connector must register itself
	 */
	public function register_logger() {}

	/**
	 * @param Group $group
	 * @return  Group
	 */
	function get_acf_settings_group( Group $group ) {
		$field = new Field( self::NAME . self::SLACK_API_KEY );
		$field->set_attributes( [
			'label'             => __( 'Slack Webhook URL', 'tribe' ),
			'name'              => self::SLACK_API_KEY,
			'type'              => 'text',
			'instructions'      => sprintf( __( 'Enter your Slack Webhook URL. Retrieve it <a href="%s">here</a>', 'tribe' ), '' ),
			'conditional_logic' => $this->get_conditional_logic()
		] );
		$group->add_field( $field );

		$field = new Field( self::NAME . self::SLACK_CHANNEL );
		$field->set_attributes( [
			'label'             => __( 'Slack Channel', 'tribe' ),
			'name'              => self::SLACK_CHANNEL,
			'type'              => 'text',
			'instructions'      => __( 'Enter the channel you wish to log to. Include the leading #', 'tribe' ),
			'conditional_logic' => $this->get_conditional_logic()
		] );
		$group->add_field( $field );

	}

	public function get_conditional_logic() {
		return [
			[
				[
					'field'     => Logger_Settings::AVAILABLE_LOGGERS,
					'operator'  => '==',
					'value'     => self::NAME
				]
			]
		];
	}

}