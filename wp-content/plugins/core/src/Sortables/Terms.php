<?php
namespace Tribe\Project\Sortables;

use Tribe\Project\Taxonomies\Category\Category;

class Terms {

	public $taxonomies;

	public function __construct() {
		$this->taxonomies = apply_filters( 'tribe/sortables/taxonomies', [ Category::NAME ] );
	}

	public function enqueue_assets() {
		if( ! isset( $_GET['taxonomy'] ) ) {
			return false;
		}

		if( ! in_array( $_GET['taxonomy'], $this->taxonomies ) ) {
			return false;
		}

		wp_enqueue_script( 'tribe-term-sorter',
			plugins_url( 'core/assets/admin/sortables/term-sorter.js' ),
			[
				'jquery',
				'jquery-ui-sortable'
			]
		);

		$url = get_admin_url().'images/';
		wp_localize_script('tribe-term-sorter', 'adminImagesUrl', $url);
	}

	/**
	 * This method runs one time to alter the table structure
	 */
	public function check_for_database_column(){
		if( empty( get_option( 'term_sorter_column_exists' ) ) ) {
			global $wpdb;
			$wpdb->query( "ALTER TABLE $wpdb->terms ADD term_order INT" );
			update_option( 'term_sorter_column_exists', true, false );
		}
	}

	/**
	 * Sort Terms By Order
	 *
	 * Sort any call to get_terms by term_order
	 *
	 * @uses added to the get_terms_orderby filter by self::hooks()
	 *
	 * @param string $orderby
	 *
	 * @return string
	 */
	public static function sort_terms_by_order( string $orderby ): string {

		// In event of manual sort, don't term sort. Use core behaviour
		if( ! empty( $_GET['orderby'] ) ) {
			return $orderby;
		}

		if( ! empty( $orderby ) ) {
			if( false !== strpos( $orderby, 'term_order' ) ){
				return $orderby;
			} else {
				$orderby = 't.term_order, '.$orderby;
			}
		} else {
			$orderby = 't.term_order, name';
		}

		return $orderby;
	}

	/**
	 * Update Order
	 *
	 * Updates the order of the terms via ajax
	 *
	 * @uses added to the wp_ajax_term_sort_update hook by self::hooks()
	 *
	 * @return void
	 */
	public function update_order(){
		if ( empty( $_POST['id'] ) || empty( $_POST['taxonomy']) || ( ! isset( $_POST['previd'] ) && ! isset( $_POST['nextid'] ) ) ) {
			wp_send_json_success();
		}

		if ( ! $term = get_term( $_POST['id'], $_POST['taxonomy'] ) ) {
			wp_send_json_success();
		}

		$taxonomy = $_POST['taxonomy'];
		$previd = empty( $_POST['previd'] ) ? false : (int) $_POST['previd'];
		$nextid = empty( $_POST['nextid'] ) ? false : (int) $_POST['nextid'];
		$start = empty( $_POST['start'] ) ? 1 : (int) $_POST['start'];
		$excluded = empty( $_POST['excluded'] ) ? array( $term->term_id ) : array_filter( (array) $_POST['excluded'], 'intval' );
		$new_pos = array();
		$return_data = new \stdClass();

		$parent_id = $term->parent;

		//get the term after this one's parent
		if( !empty( $nextid ) ){
			$next_term = get_term( $nextid, $taxonomy );
			$next_term_parent = $next_term->parent;
		} else {
			$next_term_parent = false;
		}


		//if the term before this one is the parent of the term after this one all 3 should share same parent
		if ( $previd == $next_term_parent ) {
			$parent_id = $next_term_parent;

			//if the term after this one does not share the same parent we will use the parent of the one before
		} elseif ( $next_term_parent != $parent_id ) {

			if( !empty( $previd ) ){
				$prev_term = get_term( $nextid, $taxonomy );
				$prev_term_parent = $prev_term->parent;
			} else {
				$prev_term_parent = false;
			}

			if ( $prev_term_parent != $parent_id ) {
				$parent_id = ( $prev_term_parent != false ) ? $prev_term_parent : $next_term_parent;
			}
		}

		// if the next post's parent isn't our parent, we no longer care about it
		if ( $next_term_parent != $parent_id ) {
			$nextid = false;
		}

		$siblings = get_terms( $taxonomy,
			array(
				'parent'     => $parent_id,
				'orderby'    => 'term_order name',
				'hide_empty' => false
			)
		);

		foreach( $siblings as $sibling ) {

			// don't handle the actual term
			if ( $sibling->term_id == $term->term_id ) {
				continue;
			}

			// if this is the post that comes after our repositioned post, set our repositioned post position and increment menu order
			if ( $nextid == $sibling->term_id ) {
				wp_update_term( $term->term_id, $taxonomy,
					array(
						'parent'     => $parent_id
					)
				);
				$this->update_term_order($term->term_id, $start);

				$ancestors = get_ancestors( $term->term_id, $taxonomy );

				$new_pos[$term->term_id] = array(
					'term_order'	=> $start,
					'parent'	    => $parent_id,
					'depth'			=> count( $ancestors ),
				);
				$start++;
			}

			// if repositioned term has been set, and new items are already in the right order, we can stop
			if ( isset( $new_pos[$term->term_id] ) && $sibling->term_order >= $start ) {
				$return_data->next = false;
				break;
			}

			// set the term order of the current sibling and increment the term order
			if ( $sibling->term_order != $start ) {
				$this->update_term_order($sibling->term_id, $start);
			}
			$new_pos[$sibling->term_id] = $start;
			$start++;

			if ( !$nextid && $previd == $sibling->term_id ) {
				wp_update_term( $term->term_id, $taxonomy,
					array(
						'parent'     => $parent_id
					)
				);
				$this->update_term_order($term->term_id, $start);

				$ancestors = get_ancestors( $term->term_id, $taxonomy );
				$new_pos[$term->term_id] = array(
					'term_order'	=> $start,
					'parent' 	    => $parent_id,
					'depth' 		=> count($ancestors) );
				$start++;
			}

		}

		//if we moved a term with children we must refresh the page
		$children = get_terms( $taxonomy,
			array(
				'parent'     => $term->term_id,
				'orderby'    => 'term_order name',
				'hide_empty' => false
			)
		);
		if ( ! empty( $children ) ){
			wp_send_json_error( 'children' );
		}
		$return_data->new_pos = $new_pos;

		wp_send_json_success( $return_data );
	}

	/**
	 * Update Term Order
	 *
	 * Updates a term_order column in the database
	 *
	 * @param int $term_id - term to update
	 * @param int $term_order - new order
	 *
	 * @return void
	 *
	 */
	function update_term_order( $term_id, $term_order ){
		global $wpdb;

		$wpdb->update(
			$wpdb->terms,
			array(
				'term_order' => $term_order
			),
			array(
				'term_id' => $term_id
			)
		);

	}

	public static function instance() {
		return tribe_project()->container()['sortables.terms'];
	}
}