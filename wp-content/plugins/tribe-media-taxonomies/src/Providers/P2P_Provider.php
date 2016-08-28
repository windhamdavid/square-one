<?php

namespace Tribe\Media\Providers;

use Pimple\Container;
use Tribe\Media\Fields\P2P_Field;

class P2P_Provider {

    private $container;

    public function __construct( Container $container ) {
        $this->container = $container;
    }

    public function init() {
        add_filter( 'attachment_fields_to_edit', [ $this, 'add_attachment_p2p_fields' ], 50, 2 );
    }

    public function add_attachment_p2p_fields( $fields, $post ) {

        $screen = get_current_screen();

        if ( isset( $screen->id ) && 'attachment' == $screen->id ) {
            return $fields;
        }

        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $fields;
        }

        foreach ( $this->container['connections']->get_connection_types() as $relationship ) {

            $post_type = $this->get_post_type_from_relationship( $relationship );

            // A flag so that we can verify which fields were submitted
            $fields[ 'p2p-flag-' . $relationship->name ] = [
                'input'        => 'hidden',
                'value'        => 1,
                'show_in_edit' => true,
            ];

            $field_label = sprintf( 'Related %s', $post_type['label'] );

            $field = new P2P_Field( $field_label );
            $field->set_post( $post );
            $field->set_relationship( $relationship->name );
            $field->set_post_type( $post_type['types'] );

            $fields[ 'p2p-' . $relationship->name ] = $field->get_field_array();
            if ( empty( $fields[ 'p2p-' . $relationship->name ][ 'html' ] ) ) {
                // might be empty if user can't relate any sections
                unset( $fields[ 'p2p-' . $relationship->name ] );
            }
        }

        return $fields;
    }

    private function get_post_type_from_relationship( $relationship ) {

        $types      = $relationship->side['to']->query_vars['post_type'];
        $names      = [];
        $post_types = [];

        foreach ( $types as $type ) {
            $post_object = get_post_type_object( $type );
            $name = $post_object->labels->name;
            $names[] = $name;
            $post_types[] = $type;
        }

        return [ 'types' => $post_types, 'label' => implode( '/', $names ) ];
    }

}