<?php
/**
 * Term Checkbox - a Filter Field which adds fields for Taxonomies to the Attachment edit screen.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Fields;

/**
 * Class Term_Checkbox
 */
class Term_Checkbox extends Filter_Field {

    /**
     * @var object
     */
    private $taxonomy;

    /**
     * @var int
     */
    private $post_id;

    /**
     * @param object $taxonomy - the taxonomy for this field.
     * @param integer $post_id - the post ID for this field.
     */
    public function set_field_details( $taxonomy, $post_id ) {
        $this->taxonomy = $taxonomy;
        $this->post_id = $post_id;
    }

    /**
     * Get the HTML content for this field.
     *
     * @return mixed|void
     */
    protected function get_field_html() {
        $terms = get_terms( $this->taxonomy->name, [
            'hide_empty' => false,
        ] );

        if ( ! $terms ) {
            return apply_filters( 'media-checkboxes', '', $this->taxonomy, $terms, $this->post_id );
        }

        $attachment_terms = wp_get_object_terms( $this->post_id, $this->taxonomy->name, [
            'fields' => 'ids'
        ] );

        $output = '<div class="media-terms" data-id="' . $this->post_id . '" data-taxonomy="' . $this->taxonomy->name . '">';
        $output .= '<ul>';

        ob_start();
        wp_terms_checklist( 0, [
            'selected_cats' => $attachment_terms,
            'taxonomy'      => $this->taxonomy->name,
            'checked_ontop' => false
        ] );
        $output .= ob_get_contents();
        ob_end_clean();

        $output .= '</ul>';
        $output .= '</div>';

        return apply_filters( 'media-checkboxes', $output, $this->taxonomy, $terms, $this->post_id );
    }

}