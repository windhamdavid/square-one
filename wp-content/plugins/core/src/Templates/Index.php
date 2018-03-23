<?php


namespace Tribe\Project\Templates;

use Tribe\Project\Templates\Components\Button;
use Tribe\Project\Templates\Components\Breadcrumbs;
use Tribe\Project\Templates\Components\Pagination;

use Tribe\Project\Theme\Pagination_Util;
use Tribe\Project\Theme\Util;
use Tribe\Project\Twig\Stringable_Callable;

class Index extends Base {

	public function get_data(): array {
		$data               = parent::get_data();
		$data['panels_box'] = $this->get_panels_box();
		$data['wp_editor']  = new Stringable_Callable( [ $this, 'get_wp_editor' ] );

		return $data;
	}

	public function get_wp_editor() {
		$content   = 'This content gets loaded first.';
		$settings  = array(
			'wpautop'       => true, // use wpautop?
			'media_buttons' => true, // show insert/upload button(s)
			'textarea_name' => 'content', // set the textarea name to something different, square brackets [] can be used here
			'textarea_rows' => get_option( 'default_post_edit_rows', 10 ), // rows="..."
			'tabindex'      => '',
			'editor_css'    => '', //  extra styles for both visual and HTML editors buttons,
			'editor_class'  => '', // add extra class(es) to the editor textarea
			'teeny'         => false, // output the minimal editor config used in Press This
			'dfw'           => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
			'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
			'quicktags'     => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
		);
		wp_editor( $content, 'content' );
	}

	protected function get_panels_box() {
		$post    = get_post( 2 );
		$metabox = new \ModularContent\MetaBox();
		ob_start();
		$metabox->render( $post );
		return ob_get_clean();
	}

	protected function get_posts() {
		$data = [];
		while ( have_posts() ) {
			the_post();
			$data[] = $this->get_single_post();
		}

		rewind_posts();

		return $data;
	}

	protected function get_single_post() {
		$template = new Content\Loop\Item( $this->template, $this->twig );
		$data     = $template->get_data();

		return $data['post'];
	}

	protected function get_current_page() {
		return max( 1, get_query_var( 'paged' ) );
	}

	protected function get_breadcrumbs() {

		if ( ! is_archive() ) {
			return '';
		}

		$news_url = get_permalink( get_option( 'page_for_posts' ) );

		$items = [
			[
				'url'   => $news_url,
				'label' => __( 'All News', 'tribe' ),
			],
		];

		$options = [
			Breadcrumbs::ITEMS => $items,
		];

		$crumbs = Breadcrumbs::factory( $options );

		return $crumbs->render();
	}

	public function get_pagination(): string {
		$links = $this->get_pagination_numbers();

		$options = [
			Pagination::LIST_CLASSES       => [ 'g-row', 'g-row--no-gutters', 'c-pagination__list' ],
			Pagination::LIST_ITEM_CLASSES  => [ 'g-col', 'c-pagination__item' ],
			Pagination::WRAPPER_CLASSES    => [ 'c-pagination', 'c-pagination--loop' ],
			Pagination::WRAPPER_ATTRS      => [ 'aria-labelledby' => 'c-pagination__label-single' ],
			Pagination::PAGINATION_NUMBERS => $links,
		];

		return Pagination::factory( $options )->render();
	}

	public function get_pagination_numbers(): array {
		$links = [];

		$pagination = new Pagination_Util();
		$numbers    = $pagination->numbers( 2, true, false, false );

		if ( empty( $numbers ) ) {
			return $links;
		}

		foreach ( $numbers as $number ) {

			if ( $number['active'] ) {
				$number['classes'][] = 'active';
			}

			if ( $number['prev'] ) {
				$number['classes'][] = 'icon icon-cal-arrow-left';
			}

			if ( $number['next'] ) {
				$number['classes'][] = 'icon icon-cal-arrow-right';
			}

			$options = [
				Button::CLASSES     => $number['classes'],
				Button::URL         => $number['url'],
				Button::LABEL       => $number['label'],
				Button::BTN_AS_LINK => true,
			];

			$links[] = Button::factory( $options )->render();
		}

		return $links;
	}
}