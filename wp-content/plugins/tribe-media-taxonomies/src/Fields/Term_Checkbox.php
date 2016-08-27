<?php

namespace Tribe\Media\Fields;

class Term_Checkbox extends Filter_Field {

    private $taxonomy;
    private $post_id;

    public function set_field_details( $taxonomy, $post_id ) {
        $this->taxonomy = $taxonomy;
        $this->post_id = $post_id;
    }

    protected function get_field_html() {
        $terms = get_terms( $this->taxonomy->name, array(
            'hide_empty' => false,
        ) );

        if ( ! $terms ) {
            return apply_filters( 'media-checkboxes', '', $this->taxonomy, $terms, $this->post_id );
        }

        $attachment_terms = wp_get_object_terms( $this->post_id, $this->taxonomy->name, array(
            'fields' => 'ids'
        ) );

        $output = '<div class="media-terms" data-id="' . $this->post_id . '" data-taxonomy="' . $this->taxonomy->name . '">';
        $output .= '<ul>';

        ob_start();
        wp_terms_checklist( 0, array(
            'selected_cats' => $attachment_terms,
            'taxonomy'      => $this->taxonomy->name,
            'checked_ontop' => false
        ) );
        $output .= ob_get_contents();
        ob_end_clean();

        $output .= '</ul>';
        $output .= '</div>';

        return apply_filters( 'media-checkboxes', $output, $this->taxonomy, $terms, $this->post_id );
    }

}