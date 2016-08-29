<?php
/**
 * The Asset Provider. Deals with enqueueing and localizing the JS and CSS assets for the plugin.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Providers;

use Pimple\Container;
use Tribe\Libs\Assets\Asset_Loader;

/**
 * Class Asset_Provider
 */
class Asset_Provider {

    /**
     * @var Asset_Loader
     */
    private $asset_loader;

    /**
     * @var Container
     */
    private $container;

    /**
     * Asset_Provider constructor.
     *
     * @param Container $container
     */
    function __construct( Container $container ) {
        $this->container = $container;
        $this->asset_loader = $container['asset_loader'];
    }

    /**
     * Initialize the hooks.
     */
    public function init() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );
    }

    /**
     * Enqueue the various assets.
     */
    public function admin_enqueue_assets() {

        $tax_info = $this->gather_taxonomy_information();

        // Taxonomies @sam let's consider this the main js file; I'll localize everything to it.
        $this->asset_loader->register_and_enqueue_script( 'media-taxonomies', 'javascript/media-taxonomies.js', [ 'jquery' ], false );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mt_taxonomy_l10n', $this->taxonomy_js_l10n() );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mediaTaxonomies', $tax_info['taxonomies'] );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mediaTerms', $tax_info['terms'] );
        $this->asset_loader->localize_script( 'media-taxonomies', 'TribeMediaAdminFilterData', $this->get_p2p_data() );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mt_media', $this->add_tag_data() );
        $this->asset_loader->register_and_enqueue_stylesheet( 'media-taxonomies', 'css/media-taxonomies.css', [], '1.0' );

        // P2P @sam I assume you'll be combining all of the scripts together; we can get rid of these next references once you do.
        $this->asset_loader->register_and_enqueue_script( 'selectize', 'javascript/vendor/selectize.js', [ 'jquery' ], '', false );
        $this->asset_loader->register_and_enqueue_script( 'media-admin', 'javascript/media-admin.js', [ 'jquery', 'media-views', 'selectize' ], '', true );
        $this->asset_loader->register_and_enqueue_stylesheet( 'selectize', 'javascript/vendor/selectize.default.css' );
        $this->asset_loader->register_and_enqueue_stylesheet( 'media-admin', 'css/media-admin.css' );

        // Add Tag
        $this->asset_loader->register_and_enqueue_script( 'media-uploader-edits-scripts', 'javascript/media-uploader-edits.js', [ 'jquery' ], false );
    }

    /**
     * Language strings for js
     */
    private function taxonomy_js_l10n() {

        $js_l10n = [
            'tax_filters' => [
                'toggle_heading' => __( 'Filter By:' ),
                'toggle_type'    => __( 'Type' )
            ]
        ];

        return $js_l10n;
    }

    /**
     * Gather taxonomy information
     *
     * @access public
     * @since  v0.9
     * @author Ralf Hortt
     */
    public function gather_taxonomy_information() {

        $return = [
            'taxonomies' => [],
            'terms' => [],
        ];

        $taxonomies = apply_filters( 'media-taxonomies', get_object_taxonomies( 'attachment', 'objects' ) );

        if ( ! $taxonomies ) {
            return $return;
        }

        $attachment_taxonomies = $attachment_terms = [];

        foreach ( $taxonomies as $name => $taxonomy ) :

            $attachment_taxonomies[ $taxonomy->name ] = $taxonomy->labels->name;

            $terms = get_terms( $taxonomy->name, [
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
            ] );

            if ( ! $terms ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $attachment_terms[ $taxonomy->name ][] = [
                    'id'    => $term->term_id,
                    'label' => $term->name,
                    'slug'  => $term->slug
                ];
            }

        endforeach;

        $key_terms = 'json_att_terms_' . md5( serialize( $attachment_terms ) );
        $att_terms = wp_cache_get( $key_terms );
        $key_tax   = 'json_att_taxonomies';
        $att_tax   = wp_cache_get( $key_tax );
        $force_tax = true; // set to true because we have per-user taxonomy differences

        if ( false === $att_terms ) {
            $att_terms = json_encode( $attachment_terms );
            wp_cache_set( $key_terms, $att_terms );
        }

        if ( ( false === $att_tax ) || $force_tax ) {
            $att_tax = json_encode( $attachment_taxonomies );
            wp_cache_set( $key_tax, $att_tax );
        }

        $return['taxonomies'] = json_decode( $att_tax, true );
        $return['terms'] = json_decode( $att_terms, true );

        return $return;
    }

    /**
     * Get the relationship data from any registered P2P connections.
     *
     * @return array
     */
    private function get_p2p_data() {
        $data = [
            'p2p' => [ ],
        ];
        foreach ( $this->container['connections']->get_connection_types() as $p2p ) {

            foreach ( $p2p['post_types'] as $post_type ) {
                $data['p2p'][] = $this->get_relationship_data( $p2p[ 'relationship_name' ], $post_type );
            }

        }
        return $data;
    }

    /**
     * Get the data for a specific relationship.
     *
     * @param string $relationship_id - the relationship name.
     * @param string $post_type_id - the post type name.
     *
     * @return array
     */
    private function get_relationship_data( $relationship_id, $post_type_id ) {
        $pto = get_post_type_object( $post_type_id );
        return [
            'name'      => $relationship_id,
            'label'     => $pto->label,
            'all_label' => $pto->labels->all_items,
            'query_var' => $relationship_id . ':' . $post_type_id,
            'posts'     => $this->get_post_data( $relationship_id, $post_type_id ),
        ];
    }

    /**
     * Get data about connected posts for a specific relationship.
     *
     * @param string $relationship - the relationship name.
     * @param string $post_type - the post type name.
     *
     * @return array
     */
    private function get_post_data( $relationship, $post_type ) {
        $groupby_filter = function( $group_by, $query ) {
            /** @var \wpdb $wpdb */
            global $wpdb;
            return "{$wpdb->posts}.ID";
        };
        add_filter( 'posts_groupby', $groupby_filter, 10, 2 );
        $posts = get_posts( [
            'post_type'           => $post_type,
            'post_status'         => 'any',
            'connected_type'      => $relationship,
            'connected_items'     => 'any',
            'connected_direction' => 'from',
            'posts_per_page'      => -1,
            'suppress_filters'    => false,
            'orderby'             => 'post_title',
            'order'               => 'ASC',
        ] );
        remove_filter( 'posts_groupby', $groupby_filter, 10 );

        return $posts;
    }

    /**
     * Add localization data for the 'add tag' functionality.
     *
     * @return array
     */
    private function add_tag_data() {
        $js_config = [
            'config' => [
                'add_tag_ajax_nonce' => wp_create_nonce( 'media-add-tag' ),
            ],
            'l10n'   => [
                'alerts'       => [
                    'warnDelete' => __( 'Please be cautious when deleting images%s as other users may have included them in their content.%s %s\'Cancel\' to stop, \'OK\' to delete.' ),
                    'warnDeleteTax' => __( 'Are you completely sure you want to delete the term %s?%n Other users may have applied it to their posts/media.%n%n \'Cancel\' to stop, \'OK\' to delete.' )
                ],
                'meta_toggles' => [
                    'sidebar_acf'  => __( 'Additional Image Meta' ),
                    'sidebar_hide' => __( 'Hide' ),
                    'sidebar_show' => __( 'Show' )
                ]
            ]
        ];

        return $js_config;
    }
}