<?php

namespace Tribe\Media\Fields;

class P2P_Field extends Filter_Field {

    private $post;
    private $relationship;
    private $post_type;
    private $post_list;

    public function set_post( $post ) {
        $this->post = $post;
    }

    public function set_relationship( $relationship ) {
        $this->relationship = $relationship;
    }

    public function set_post_type( $post_type ) {
        $this->post_type = $post_type;
    }

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

    private function get_related_posts( $post ) {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql = "SELECT p2p_to FROM {$wpdb->p2p} WHERE p2p_from=%d AND p2p_type=%s";
        $sql = $wpdb->prepare( $sql, $post->ID, $this->relationship );
        return $wpdb->get_col( $sql );
    }

}