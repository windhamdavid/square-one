<?php
/**
 * P2P Field - a Filter Field which provides relationship fields for attachments.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Fields;

/**
 * Class P2P_Field
 */
class P2P_Field extends Filter_Field {

    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var string
     */
    private $relationship;

    /**
     * @var string
     */
    private $post_type;

    /**
     * @var array
     */
    private $post_list;

    /**
     * @param \WP_Post $post
     */
    public function set_post( $post ) {
        $this->post = $post;
    }

    /**
     * @param string $relationship
     */
    public function set_relationship( $relationship ) {
        $this->relationship = $relationship;
    }

    /**
     * @param string $post_type
     */
    public function set_post_type( $post_type ) {
        $this->post_type = $post_type;
    }

    /**
     * Get the HTML content for this field.
     *
     * @return string
     */
    protected function get_field_html() {
        $post_list = $this->get_post_list( $this->post_type );
        $related = $this->get_related_posts( $this->post );
        $list = [ ];
        foreach ( $post_list as $post_option ) {
            if ( current_user_can( 'edit_post', $post_option->ID ) ) {
                $list[] = sprintf(
                    '<option value="%d" %s>%s</option>',
                    (int) $post_option->ID,
                    selected( in_array( $post_option->ID, $related ), true, false ),
                    esc_html( $post_option->post_title )
                );
            }
        }
        if ( empty( $list ) ) {
            return '';
        }
        $html = sprintf( '<select class="p2p-select" name="attachments[%d][p2p][%s][]" multiple="multiple">', $this->post->ID, $this->relationship ) . implode( '', $list ) . '</select>';
        return $html;
    }

    /**
     * Retrieve all posts for a given type.
     *
     * @param string $post_type
     *
     * @return mixed
     */
    private function get_post_list( $post_type ) {
        $key = json_encode( $post_type );

        if ( ! isset( $this->post_list[ $key ] ) ) {
            $this->post_list[ $key ] = get_posts( [
                'post_type'      => $post_type,
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ] );
        }
        return $this->post_list[ $key ];
    }

    /**
     * Get related posts for a given post.
     *
     * @param \WP_Post $post
     *
     * @return array
     */
    private function get_related_posts( $post ) {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql = "SELECT p2p_to FROM {$wpdb->p2p} WHERE p2p_from=%d AND p2p_type=%s";
        $sql = $wpdb->prepare( $sql, $post->ID, $this->relationship );
        return $wpdb->get_col( $sql );
    }

}