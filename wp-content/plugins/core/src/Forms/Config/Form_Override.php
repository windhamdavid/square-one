<?php


namespace Tribe\Project\Forms\Config;


class Form_Override {
	/**
	 * @var Form_Config[]
	 */
	private $forms = [];

	public function __construct( Form_Config ...$forms ) {
		$this->forms = $forms;
	}

	/**
	 * @return void
	 * @action admin_init
	 */
	public function create_forms() {
		$existing = \GFAPI::get_forms( true, false );

		$existing_classes = array_column( $existing, 'cssClass' );

		foreach ( $this->forms as $form ) {
			$name = $form->get_name();
			if ( ! in_array( $name, $existing_classes ) ) {
				$form_id = \GFAPI::add_form( $this->fill_defaults( $form ) );
				do_action( 'tribe/forms/created', $form_id, $form );
			}
		}
	}

	/**
	 * Fill in all the required attributes of the form
	 *
	 * @param Form_Config $form
	 *
	 * @return array
	 */
	private function fill_defaults( Form_Config $form ) {
		$config = $form->get_config();
		$config = wp_parse_args( $config, [
			'title'                => $form->get_title(),
			'description'          => '',
			'labelPlacement'       => 'top_label',
			'subLabelPlacement'    => 'below',
			'descriptionPlacement' => 'below',
			'cssClass'             => $form->get_name(),
			'is_active'            => true,
			'date_created'         => current_time( 'mysql' ),
			'fields'               => [],
			'button'               => [
				'type' => 'text',
				'text' => __( 'Submit', 'tribe' ),
			],
		] );

		return $config;
	}

	/**
	 * @param array $field_groups
	 *
	 * @return array
	 * @filter gform_add_field_buttons
	 */
	public function remove_fields_gui( $field_groups ) {
		if ( isset( $_GET['id'] ) && $this->is_managed_form_id( (int) $_GET['id'] ) ) {
			$field_groups = [];
		}

		return $field_groups;
	}

	/**
	 * @param string $button
	 *
	 * @return string
	 * @filter gform_save_form_button
	 */
	public function remove_save_button( $button ) {
		if ( isset( $_GET['id'] ) && $this->is_managed_form_id( (int) $_GET['id'] ) ) {
			return '';
		}

		return $button;
	}

	/**
	 * Removes the link to send a form to the trash
	 *
	 * @param string $trash_link
	 *
	 * @return string
	 * @filter gform_form_trash_link
	 */
	public function remove_trash_link( $trash_link ) {
		if ( isset( $_GET['id'] ) && $this->is_managed_form_id( (int) $_GET['id'] ) ) {
			return '';
		}

		return $trash_link;
	}

	/**
	 * Removes the link delete a field
	 *
	 * @param string $field_link
	 *
	 * @return string
	 * @filter gform_delete_field_link
	 */
	public function remove_delete_field_link( $field_link ) {
		if ( isset( $_GET['id'] ) && $this->is_managed_form_id( (int) $_GET['id'] ) ) {
			return '';
		}

		return $field_link;
	}

	/**
	 * Removes the link duplicate a field
	 *
	 * @param string $field_link
	 *
	 * @return string
	 * @filter gform_duplicate_field_link
	 */
	public function remove_duplicate_field_link( $field_link ) {
		if ( isset( $_GET['id'] ) && $this->is_managed_form_id( (int) $_GET['id'] ) ) {
			return '';
		}

		return $field_link;
	}

	/**
	 * Removes items from the toolbar
	 *
	 * @param array $menu_items
	 * @param int $id
	 *
	 * @return array
	 * @filter gform_toolbar_menu
	 */
	public function remove_toolbar_menu_items( $menu_items, $id ) {
		if ( $this->is_managed_form_id( (int) $id ) ) {
			unset( $menu_items['settings'] );
			unset( $menu_items['entries'] );
		}

		return $menu_items;
	}

	/**
	 * Removes form actions
	 *
	 * @param array $form_actions
	 * @param int $id
	 *
	 * @return array
	 * @filter gform_form_actions
	 */
	public function remove_form_actions( $form_actions, $id ) {
		if ( $this->is_managed_form_id( (int) $id ) ) {
			unset( $form_actions['edit'] );
			unset( $form_actions['settings'] );
			unset( $form_actions['duplicate'] );
			unset( $form_actions['trash'] );
		}

		return $form_actions;
	}

	/**
	 * @param array $tabs
	 * @param int $id
	 *
	 * @return array
	 * @filter gform_form_settings_menu
	 */
	public function remove_settings_tabs( $tabs, $id ) {
		if ( $this->is_managed_form_id( (int) $id ) ) {
			return []; // no editing any settings for a managed form
		}

		return $tabs;
	}

	/**
	 * Ensure that the form state always matches the coded state
	 *
	 * @param array $form
	 *
	 * @return array
	 * @filter gform_form_post_get_meta
	 */
	public function override_form_meta( $form ) {
		//return $form;
		foreach ( $this->forms as $form_config ) {
			if ( $form_config->get_name() == ( $form['cssClass'] ?? '' ) ) {
				$config           = \GFFormsModel::convert_field_objects( array_merge( $form, $this->fill_defaults( $form_config ) ) );
				$confirmation_ids = array_keys( $form['confirmations'] );
				$notification_ids = array_keys( $form['notifications'] );

				// preserve the stored keys for confirmations/notifications or everything gets confused
				foreach ( $config['confirmations'] as $key => $confirmation ) {
					if ( empty( $confirmation_ids ) ) {
						break;
					}
					$new_key                             = array_shift( $confirmation_ids );
					$confirmation['id']                  = $new_key;
					$config['confirmations'][ $new_key ] = $confirmation;
					unset( $config['confirmations'][ $key ] );
				}
				foreach ( $config['notifications'] as $key => $notification ) {
					if ( empty( $notification_ids ) ) {
						break;
					}
					$new_key                             = array_shift( $notification_ids );
					$notification['id']                  = $new_key;
					$config['notifications'][ $new_key ] = $notification;
					unset( $config['notifications'][ $key ] );
				}

				return $config;
			}
		}

		return $form;
	}

	private function is_managed_form_id( $form_id ) {
		$form_id = intval( $form_id );
		$form    = \GFAPI::get_form( $form_id );

		return $this->is_managed_form( $form );
	}

	private function is_managed_form( $form ) {
		if ( $form === false ) {
			return false;
		}
		foreach ( $this->forms as $form_config ) {
			if ( $form_config->get_name() == ( $form['cssClass'] ?? '' ) ) {
				return true;
			}
		}

		return false;
	}
}