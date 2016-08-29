<?php
/**
 * P2P Provider - provides functionality for adding Relationship information and filtering to attachments.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Providers;

use Pimple\Container;
use Tribe\Media\Fields\P2P_Field;

/**
 * Class P2P_Provider
 */
class P2P_Provider {

    /**
     * @var Container
     */
    private $container;

    /**
     * P2P_Provider constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container ) {
        $this->container = $container;
    }

    /**
     * Initialize the hooks.
     */
    public function init() {
        add_filter( 'attachment_fields_to_edit', [ $this, 'add_attachment_p2p_fields' ], 50, 2 );
        add_filter( 'attachment_fields_to_save', [ $this, 'handle_save_request' ], 4, 2 );
        add_action( 'ajax_query_attachments_args', [ $this, 'filter_attachment_ajax_query_args' ], 10, 1 );
    }

    /**
     * Add P2P fields to the edit attachment area.
     *
     * @param array $fields  - an array of Fields for the attachment.
     * @param \WP_Post $post - the Post object.
     * @return mixed
     */
    public function add_attachment_p2p_fields( $fields, $post ) {

        $screen = get_current_screen();

        if ( isset( $screen->id ) && 'attachment' == $screen->id ) {
            return $fields;
        }

        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $fields;
        }

        foreach ( $this->container['connections']->get_connection_types() as $relationship ) {

            // A flag so that we can verify which fields were submitted
            $fields[ 'p2p-flag-' . $relationship['relationship_name'] ] = [
                'input'        => 'hidden',
                'value'        => 1,
                'show_in_edit' => true,
            ];

            $field_label = sprintf( 'Related %s', $relationship['post_types_label'] );

            $field = new P2P_Field( $field_label );
            $field->set_post( $post );
            $field->set_relationship( $relationship['relationship_name'] );
            $field->set_post_type( $relationship['post_types'] );

            $fields[ 'p2p-' . $relationship['relationship_name'] ] = $field->get_field_array();
            if ( empty( $fields[ 'p2p-' . $relationship['relationship_name'] ][ 'html' ] ) ) {
                // might be empty if user can't relate any sections
                unset( $fields[ 'p2p-' . $relationship['relationship_name'] ] );
            }
        }

        return $fields;
    }

    /**
     * Handle the save request for attachments.
     *
     * @param array $post       An array of post data.
     * @param array $attachment An array of attachment metadata.
     * @return array
     */
    public function handle_save_request( $post, $attachment ) {
        if ( ! current_user_can( 'edit_post', $post[ 'ID' ] ) ) {
            return $post;
        }

        if ( get_post_type( $post[ 'ID' ] ) !== 'attachment' ) {
            return $post;
        }

        foreach ( $this->container['connections']->get_connection_types() as $relationship ) {

            $relationship_name = $relationship['relationship_name'];

            if ( ! empty( $attachment[ 'p2p-flag-' . $relationship_name ] ) ) {
                $related_posts = [ ];
                if ( ! empty( $attachment[ 'p2p' ][ $relationship_name ] ) ) {
                    $related_posts = $attachment[ 'p2p' ][ $relationship_name ];
                }
                $this->set_related_posts( (int) $post[ 'ID' ], $related_posts, $relationship_name );
            }
        }

        return $post;
    }

    /**
     * Set the related_media relationships for the post
     *
     * @param int   $attachment_id
     * @param int[] $related_post_ids
     * @param string $relationship_name
     * @return void
     */
    private function set_related_posts( $attachment_id, $related_post_ids, $relationship_name ) {
        $related_post_ids = array_filter( array_map( 'intval', $related_post_ids ) );

        $prior_related_posts = p2p_get_connections( $relationship_name, [
            'from'   => $attachment_id,
            'fields' => 'p2p_to',
        ] );
        $prior_related_posts = array_filter( $prior_related_posts );

        sort( $related_post_ids );
        sort( $prior_related_posts );
        if ( $related_post_ids == $prior_related_posts ) {
            return; // nothing to do here
        }

        $connections_to_delete = array_diff( $prior_related_posts, $related_post_ids );
        $connections_to_add = array_diff( $related_post_ids, $prior_related_posts );

        /*
         * A user can only add/remove connections to posts that
         * he can edit. All other connections should be left as
         * we found them.
         */
        $connections_to_delete = array_filter( $connections_to_delete, function ( $related_post_id ) {
            return current_user_can( 'edit_post', $related_post_id );
        } );
        $connections_to_add = array_filter( $connections_to_add, function ( $related_post_id ) {
            return current_user_can( 'edit_post', $related_post_id );
        } );

        if ( ! empty( $connections_to_delete ) ) {
            p2p_delete_connections( $relationship_name, [
                'from' => $attachment_id,
                'to'   => $connections_to_delete,
            ] );;
        }

        foreach ( $connections_to_add as $connection_post_id ) {
            p2p_create_connection( $relationship_name, [
                'from' => $attachment_id,
                'to'   => $connection_post_id,
            ] );
        }
    }

    /**
     * Add arguments for the attachments AJAX call.
     *
     * @param array $args - and array of arguments for this call.
     * @return mixed
     */
    public function filter_attachment_ajax_query_args( $args ) {
        foreach ( $this->container['connections']->get_connection_types() as $relationship ) {

            foreach ( $relationship['post_types'] as $post_type ) {
                $key = $relationship[ 'relationship_name' ] . ':' . $post_type;
                if ( ! empty( $_POST[ 'query' ][ $key ] ) ) {
                    $args[ 'connected_type' ][] = $relationship[ 'relationship_name' ];
                    $args[ 'connected_items' ][] = (int) $_POST[ 'query' ][ $key ];
                    $args[ 'connected_type' ] = array_unique( $args[ 'connected_type' ] );
                    $args[ 'connected_items' ] = array_unique( $args[ 'connected_items' ] );
                }
            }

        }

        return $args;
    }

}