<?php

namespace Tribe\Project\Service_Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tribe\Project\Forms\Config\Form_Config;
use Tribe\Project\Forms\Config\Form_Override;
use Tribe\Project\Forms\Example_Form;

class Forms_Provider implements ServiceProviderInterface {

	const EXAMPLE_FORM = 'example-form';

	const FORM_OVERRIDE = 'forms.form_override';
	const FORM_LIST     = 'forms.form_list';


	public function register( Container $container ) {

		$container[ self::EXAMPLE_FORM ] = function ( $container ) {
			return new Example_Form();
		};

		$container[ self::FORM_LIST ] = function ( Container $container ) {
			return [
				$container[ self::EXAMPLE_FORM ],
			];
		};

		$container[ self::FORM_OVERRIDE ] = function ( Container $container ) {
			return new Form_Override( ...$container[ self::FORM_LIST ] );
		};

		if ( ! class_exists( '\GFAPI' ) ) {
			return; // do not hook if gforms is not present
		}

		add_action( 'admin_init', function () use ( $container ) {
			$container[ self::FORM_OVERRIDE ]->create_forms();
		}, 999, 0 );

		add_filter( 'gform_add_field_buttons', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_fields_gui( ...$args );
		}, 10, 99 );

		add_filter( 'gform_save_form_button', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_save_button( ...$args );
		}, 10, 99 );

		add_filter( 'gform_form_trash_link', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_trash_link( ...$args );
		}, 10, 99 );

		add_filter( 'gform_delete_field_link', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_delete_field_link( ...$args );
		}, 10, 99 );

		add_filter( 'gform_duplicate_field_link', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_duplicate_field_link( ...$args );
		}, 10, 99 );

		add_filter( 'gform_toolbar_menu', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_toolbar_menu_items( ...$args );
		}, 10, 99 );

		add_filter( 'gform_form_actions', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_form_actions( ...$args );
		}, 10, 99 );

		add_filter( 'gform_form_settings_menu', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->remove_settings_tabs( ...$args );
		}, 10, 99 );

		add_filter( 'gform_form_post_get_meta', function ( ...$args ) use ( $container ) {
			return $container[ self::FORM_OVERRIDE ]->override_form_meta( ...$args );
		}, 10, 99 );

		add_filter( 'pre_option_rg_gforms_disable_css', '__return_true' );
		add_filter( 'pre_option_rg_gforms_enable_html5', '__return_true' );

		$this->example_form( $container );
	}

	protected function example_form( Container $container ) {
		add_action( 'gform_pre_submission', function ( array $form ) use ( $container ) {
			$container[ self::EXAMPLE_FORM ]->handle_submission( $form );
		} );

		add_action( 'tribe/forms/created', function( int $form_id, Form_Config $form_config ) use ( $container ) {
			$container[ self::EXAMPLE_FORM ]->set_form_id_option( $form_id, $form_config );
		}, 10, 2 );
	}
}