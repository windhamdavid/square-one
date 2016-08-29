<?php
/**
 * Connections Provider - provides information about any registered P2P connections for the attachments PT.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Providers;

/**
 * Class Connections_Provider
 */
class Connections_Provider {

    /**
     * @var array
     */
    private $connection_types = [];

    /**
     * Initialize the hooks.
     */
    public function init() {
        add_action( 'p2p_registered_connection_type', [ $this, 'maybe_add_connection_type' ], 10, 2 );
    }

    /**
     * Get the connection types.
     *
     * @return array
     */
    public function get_connection_types() {
        return $this->connection_types;
    }

    /**
     * Add a connection type if it is for Attachments.
     *
     * @param $ctype
     * @param $args
     */
    public function maybe_add_connection_type( $ctype, $args ) {

        if ( 'attachment' !== $args['from'] ) {
            return;
        }

        $types      = $ctype->side['to']->query_vars['post_type'];
        $names      = [];
        $post_types = [];

        foreach ( $types as $type ) {
            $post_object = get_post_type_object( $type );
            $name = $post_object->labels->name;
            $names[] = $name;
            $post_types[] = $type;
        }

        $post_types_label = implode( '/', $names );

        $relationship_name = $ctype->name;

        $this->connection_types[] = [
            'relationship_name' => $relationship_name,
            'post_types' => $post_types,
            'post_types_label' => $post_types_label,
        ];
    }

}