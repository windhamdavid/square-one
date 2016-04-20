<?php

return array(
	'allowed_post_types' => array(
		'type' => 'multi-select',
		'ui' => array(
			'title'   => __( 'Allowed Post Types', 'cornerstone' ),
			'description' => __( 'Select which post types to enable for Cornerstone.', 'cornerstone' ),
		),
		'options' => array(
			'placeholder' => __( 'Click to select post types.', 'cornerstone' ),
			'choices' => $this->component( 'Settings_Handler' )->get_post_type_choices()
		)
	),
	'permitted_roles' => array(
		'type' => 'multi-select',
		'ui' => array(
			'title'       => __( 'Additional Roles', 'cornerstone' ),
			'description' => __( 'Allow editing with Cornerstone for roles other than Administrator.', 'cornerstone' ),
		),
		'options' => array(
			'placeholder' => __( 'Click to choose additional roles.', 'cornerstone' ),
			'choices' => $this->component( 'Settings_Handler' )->get_role_choices()
		)
	),
	'show_wp_toolbar' => array(
		'type' => 'checkbox',
		'ui' => array(
			'title'       => __( 'Show WordPress Toolbar', 'cornerstone' ),
			'description' => __( 'While editing in Cornerstone, you may opt to display the WordPress toolbar.', 'cornerstone' ),
		)
	),
	'visual_enhancements' => array(
		'type' => 'checkbox',
		'ui' => array(
			'title'       => __( 'Fun Mode', 'cornerstone' ),
			'description' => __( 'Turns on creative save messages.', 'cornerstone' ),
		)
	),
);
