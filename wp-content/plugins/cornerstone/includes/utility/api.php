<?php

/**
 * Public API
 * These functions expose Cornerstone APIs, allowing it to be extended.
 * The processes represented here are otherwise handled internally.
 */

/**
 * Set which post types should be enabled by default when Cornerstone is first
 * activated.
 * @param  array $types Array of strings specifying post type names.
 * @return none
 */
function cornerstone_set_default_post_types( $types ) {
	CS()->component( 'Common' )->set_default_post_types( $types );
}

/**
 * Allows integrating themes to disable Themeco cross-promotion, and other
 * presentational items. Example:
 *
		cornerstone_theme_integration( array(
			'remove_global_validation_notice' => true,
			'remove_themeco_offers'           => true,
			'remove_purchase_link'            => true,
			'remove_support_box'              => true
		) );
 *
 * @param  array $args List of items to flag
 * @return none
 */
function cornerstone_theme_integration( $args ) {
	CS()->component( 'Integration_Manager' )->theme_integration( $args );
}

/**
 * Register a new element
 * @param  $class_name Name of the class you've created in definition.php
 * @param  $name       slug name of the element. "alert" for example.
 * @param  $path       Path to the folder containing a definition.php file.
 */
function cornerstone_register_element( $class_name, $name, $path ) {
	CS()->component( 'Element_Orchestrator' )->add( $class_name, $name, $path );
}

/**
 * Remove a previously added element from the Builder interface.
 * @param  string $name Name used when the element's class was added
 * @return none
 */
function cornerstone_remove_element( $name ) {
	CS()->component( 'Element_Orchestrator' )->remove( $name );
}

/**
 * Registers a class as a candidate for Cornerstone Integration
 * Call from within this hook: cornerstone_integrations (happens before init)
 * @param  string $name       unique handle
 * @param  string $class_name Class to test conditions for, and eventually load
 * @return  none
 */
function cornerstone_register_integration( $name, $class_name ) {
	CS()->component( 'Integration_Manager' )->register( $name, $class_name );
}

/**
 * Unregister an integration that's been added so far
 * Call from within this hook: cornerstone_integrations (happens before init)
 * You may need to call on a later priority to ensure it was already registered
 * @param  string $name       unique handle
 * @return  none
 */
function cornerstone_unregister_integration( $name ) {
	CS()->component( 'Integration_Manager' )->unregister( $name );
}


/**
 * Deprecated
 */
function cornerstone_add_element( $class_name ) {
	CS()->component( 'Element_Orchestrator' )->add_mk1_element( $class_name );
}
