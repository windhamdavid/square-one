<?php
/**
 * Taxonomies Provider - provides functionality for adding and filtering attachments by taxonomies.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Providers;

use Tribe\Media\Fields\Term_Checkbox;

/**
 * Class Taxonomies_Provider
 */
class Taxonomies_Provider {

    /**
     * Initialize the hooks.
     */
    public function init() {
        add_filter( 'attachment_fields_to_edit', [ $this, 'attachment_fields_to_edit' ], 10, 2 );
        add_action( 'ajax_query_attachments_args', [ $this, 'filter_attachments_by_taxonomy' ], 0, 1 );
        add_action( 'restrict_manage_posts', [ $this, 'add_tax_filters_to_listing' ] );
        add_action( 'wp_ajax_save-media-terms', [ $this, 'save_media_terms' ], 0,  1 );
        add_filter( 'attachment_fields_to_save', [ $this, 'patch_taxonomies_save_ajax_callback' ], 10, 2 );
        add_action( 'wp_ajax_media-add-tag', [ $this, 'add_tag_from_manager' ], 0,  1 );
    }

    /**
     * Add the taxonomy fields to the attachment edit screen in the uploader.
     *
     * @param $fields
     * @param $post
     *
     * @return mixed
     */
    public function attachment_fields_to_edit( $fields, $post ) {

        $screen = get_current_screen();

        if ( isset( $screen->id ) && 'attachment' == $screen->id ) {
            return $fields;
        }

        $taxonomies = apply_filters( 'media-taxonomies', get_object_taxonomies( 'attachment', 'objects' ) );

        if ( ! empty( $taxonomies ) ) {

            foreach ( $taxonomies as $tax_name => $taxonomy ) {

                if ( isset( $taxonomy->skip_media_ui ) && $taxonomy->skip_media_ui ) {
                    continue;
                }

                $tax_field = new Term_Checkbox( $taxonomy->labels->singular_name );
                $tax_field->set_field_details( $taxonomy, $post->ID );

                $fields[ $tax_name ] = $tax_field->get_field_array();
            }

        }

        return $fields;

    }

    /**
     * Add taxonomy parameters to the attachment query.
     *
     *
     * @param array $args - an array of arguments for this AJAX call.
     *
     * @return array
     */
    public function filter_attachments_by_taxonomy( $args ) {

        $posted_query = filter_input( INPUT_POST, 'query', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        $taxonomies   = apply_filters( 'media-taxonomies', get_object_taxonomies( 'attachment', 'objects' ) );

        if ( empty( $taxonomies ) ) {
            return $args;
        }

        $tax_query = [];

        foreach ( $taxonomies as $tax_name => $taxonomy ) :

            $term = null;


            if ( ! array_key_exists( $tax_name, $posted_query ) || empty( $posted_query[ $tax_name ] ) ) {
                continue;
            }

            $term = $posted_query[ $tax_name ]['term_slug'];

            $posted_term_id = filter_input( INPUT_POST, $tax_name, FILTER_SANITIZE_NUMBER_INT );

            if ( ! empty( $posted_term_id ) ) {
                $term_obj = get_term( (int) $posted_term_id, $tax_name );

                if ( empty( $term_obj ) || is_wp_error( $term_obj ) ) {
                    continue;
                }

                $term = $term_obj->slug;
            }

            if ( $term == 'all' ) {
                continue;
            }

            if ( empty( $term ) ) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $tax_name,
                'field'    => 'slug',
                'terms'    => $term
            ];

        endforeach;

        $args['tax_query'] = $tax_query;

        return $args;
    }

    /**
     * Add custom filters in attachment listing
     *
     * @access public
     * @since  v0.9
     * @author Ralf Hortt
     **/
    public function add_tax_filters_to_listing() {

        $screen = get_current_screen();

        if ( empty( $screen ) || $screen->id != 'upload' ) {
            return;
        }

        $hidden = get_hidden_columns( $screen );

        $taxonomies = apply_filters( 'media-taxonomies', get_object_taxonomies( 'attachment', 'objects' ) );

        if ( $taxonomies ) {

            foreach ( $taxonomies as $tax_name => $taxonomy ) {

                $class = in_array( 'taxonomy-' . $tax_name, $hidden ) ? 'hidden' : '';

                if ( $tax_name == 'post_tag' && in_array( 'tags', $hidden ) ) {
                    $class = 'hidden';
                }

                // Don't make a dropdown if there's no terms
                if ( wp_count_terms( $taxonomy->name ) === "0" ) { // wp_count_terms returns a string for some reason
                    continue;
                }

                ob_start();
                wp_dropdown_categories( [
                    'show_option_all' => sprintf( _x( 'View all %s', '%1$s = plural, %2$s = singular', 'media-taxonomies' ), $taxonomy->labels->name, $taxonomy->labels->singular_name ),
                    'taxonomy'        => $tax_name,
                    'name'            => $tax_name,
                    'orderby'         => 'name',
                    'selected'        => ( isset( $_GET[ $tax_name ] ) ? $_GET[ $tax_name ] : '' ),
                    'hierarchical'    => true,
                    'hide_empty'      => false,
                    'class'           => $class,
                    'id'              => 'taxonomy-' . $tax_name,
                    'value_field'     => 'slug',
                ] );
                $dropdown = ob_get_clean();

                echo $dropdown;
            }

        }

    }

    /**
     * Save the terms set on the edit media screen of the uploader.
     */
    public function save_media_terms() {

        $post_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );
        $term_ids = filter_input( INPUT_POST, 'term_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        $taxonomy = filter_input( INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING );

        if ( empty( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) || empty( $taxonomy ) ) {
            die();
        }

        $term_ids = array_map( 'intval', $term_ids );

        wp_set_post_terms( $post_id, $term_ids, $taxonomy );
        wp_update_term_count_now( $term_ids, $taxonomy );
    }


    /**
     * Background information: A race condition occurs with multiple AJAX calls are initiated.
     * Refer to media-taxnomies.js, AJAX call is initiated when taxonomy checkboxes change.
     * At the same time, a default WP ajax call is initiated for save-attachment-compat action,
     * which reverses the changes made to post tags from the initial AJAX call.
     *
     * This patch is to make sure save-attachment-compat action applies the same change to
     * post tags.
     *
     * @sam @daniel I'm not entirely convinced that this is actually necessary any more. But I'll leave it in just
     * in case I'm missing some edge case somewhere.
     *
     * @param $post
     * @param $attachment_data
     *
     * @return void|WP_Post
     */
    function patch_taxonomies_save_ajax_callback( $post, $attachment_data ) {

        if ( ! $_POST ||
            ! isset( $_POST['action'] ) ||
            ( $_POST['action'] != 'save-attachment-compat' ) ||
            ! isset( $_POST['tax_input'] ) ) {

            return $post;
        }

        $post_tags = [];
        if ( isset( $_POST['tax_input']['post_tag'] ) && is_array( $_POST['tax_input']['post_tag'] ) ) {
            foreach( $_POST['tax_input']['post_tag'] as $tag_id ) {
                $term = get_term( $tag_id, 'post_tag' );
                $post_tags[] = $term->name;
            }
        }

        $post['tags_input'] = $post_tags;

        return $post;
    }

    /**
     * Add tags from the manager and return json for use in templating in the media manager or elsewhere
     *
     * @return array
     */
    public function add_tag_from_manager() {

        check_ajax_referer( 'media-add-tag', '_wpnonce_media-add-tag' );

        $post_id  = (int) $_POST['post_id'];
        $taxonomy = ! empty( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : 'post_tag';
        $tax      = get_taxonomy( $taxonomy );

        if ( $tax === false || ! current_user_can( $tax->cap->edit_terms ) ) {
            wp_die( - 1 );
        }

        $tag     = wp_insert_term( $_POST['tag-name'], $taxonomy );
        $term_id = $tag['term_id'];

        if ( ! $tag || is_wp_error( $tag ) || ( ! $tag = get_term( $tag['term_id'], $taxonomy ) ) ) {
            $success = false;
            $message = __( 'An unknown error has occurred.' );
            if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
                $message = $tag->get_error_message();
            }
        } else {
            $success = true;
            $message = 'Tag successfully added';
        }

        if ( $success ) {

            // we need to set this tag active on this attachment now

            $terms   = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
            $terms[] = $term_id;

            wp_set_object_terms( $post_id, $terms, $taxonomy );

            _update_post_term_count( $terms, get_taxonomy( $taxonomy ) );
        }

        $response = [
            'success'  => $success,
            'message'  => $message,
            'tag_data' => $tag,
            'tags'     => isset( $terms ) ? $terms : 'No tags, something went wrong.'
        ];

        header( 'Content-type: application/json' );
        echo json_encode( $response );
        die();
    }

}