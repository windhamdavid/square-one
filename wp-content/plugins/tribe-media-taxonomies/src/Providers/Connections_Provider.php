<?php

namespace Tribe\Media\Providers;

class Connections_Provider {

    private $connection_types;

    public function init() {
        add_action( 'p2p_registered_connection_type', [ $this, 'maybe_add_connection_type' ], 10, 2 );
    }

    public function get_connection_types() {
        return $this->connection_types;
    }

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