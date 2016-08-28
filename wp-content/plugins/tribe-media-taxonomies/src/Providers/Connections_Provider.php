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

        $this->connection_types[] = $ctype;
    }

}