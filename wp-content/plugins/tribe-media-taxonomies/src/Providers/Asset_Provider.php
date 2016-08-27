<?php

namespace Tribe\Media\Providers;

use Pimple\Container;

class Asset_Provider {

    private $asset_loader;

    function __construct( Container $container ) {
        $this->asset_loader = $container['asset_loader'];
    }

    public function init() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
    }

    public function admin_enqueue_assets() {

        $tax_info = $this->gather_taxonomy_information();

        $this->asset_loader->register_and_enqueue_script( 'media-taxonomies', 'javascript/media-taxonomies.js', array( 'jquery' ), '1.0' );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mt_taxonomy_l10n', $this->taxonomy_js_l10n() );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mediaTaxonomies', $tax_info['taxonomies'] );
        $this->asset_loader->localize_script( 'media-taxonomies', 'mediaTerms', $tax_info['terms'] );
        $this->asset_loader->register_and_enqueue_stylesheet( 'media-taxonomies', 'css/media-taxonomies.css', array(), '1.0' );
    }

    /**
     * Language strings for js
     *
     */
    private function taxonomy_js_l10n() {

        $js_l10n = array(
            'tax_filters' => array(
                'toggle_heading' => __( 'Filter By:' ),
                'toggle_type'    => __( 'Type' )
            )
        );

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

        $return = [ 'taxonomies', 'terms' ];

        $taxonomies = apply_filters( 'media-taxonomies', get_object_taxonomies( 'attachment', 'objects' ) );

        if ( ! $taxonomies ) {
            return $return;
        }

        $attachment_taxonomies = $attachment_terms = array();

        foreach ( $taxonomies as $name => $taxonomy ) :

            $attachment_taxonomies[ $taxonomy->name ] = $taxonomy->labels->name;

            $terms = get_terms( $taxonomy->name, array(
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
            ) );

            if ( ! $terms ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $attachment_terms[ $taxonomy->name ][] = array(
                    'id'    => $term->term_id,
                    'label' => $term->name,
                    'slug'  => $term->slug
                );
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
}