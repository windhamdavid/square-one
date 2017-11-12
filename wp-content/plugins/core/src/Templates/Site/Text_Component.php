<?php

namespace Tribe\Project\Templates\Site;

use Tribe\Project\Templates\Base;
use Tribe\Project\Templates\Components\Text;

/**
 * Class Text_Component
 *
 * @package Tribe\Project\Templates\Site
 */
class Text_Component extends Base {

	const COMPONENT_NAME = 'text';
	const WRITEUP_MD     = 'writeup.md';
	const OPTIONS_MD     = 'options.md';
	const EXAMPLES_MD    = 'example.md';
	const OPTIONS        = [
		// TODO: @aaron We could probably come up with a more efficient way to set static data for these components. What escapes me is how to handle images. Another /site dir for static images?
		Text::ATTRS   => [
			'data-js' => 'text-component',
		],
		Text::CLASSES => [
			'example-class',
			'and-another-class',
		],
		Text::TEXT    => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vel ante nec diam placerat vulputate. Fusce ut dui sagittis lacus rutrum tristique ut vitae leo. Phasellus ultrices nunc nec tempus condimentum. Sed facilisis scelerisque lobortis. Suspendisse potenti. Duis iaculis at mi eget porttitor. Donec elementum lacus ut fermentum aliquet. In lacinia odio neque, cursus congue purus bibendum a. Nulla bibendum urna eu laoreet consectetur. Fusce id consectetur purus. Morbi malesuada tempus placerat. Donec et euismod nulla, vitae ultrices leo. Aliquam et neque semper, dictum arcu eget, finibus ligula. Fusce mauris est, varius nec egestas id, eleifend eget erat. Sed finibus quis ex eu auctor.',
	];

	/**
	 * Template rendered data.
	 *
	 * @return array
	 */
	public function get_data(): array {
		$data                       = parent::get_data();
		$data['component_rendered'] = $this->get_component_text();
		$data['parsedown_writeup']  = $this->get_writeup_markdown();
		$data['parsedown_examples'] = $this->get_examples_markdown();
		$data['parsedown_options']  = $this->get_options_markdown();

		return $data;
	}

	/**
	 * Render the component
	 *
	 * @return string
	 */
	protected function get_component_text(): string {

		$text_object = Text::factory( self::OPTIONS );

		return $text_object->render();
	}

	/**
	 * Return the writeup markdown for this component.
	 *
	 * @return string
	 */
	protected function get_writeup_markdown(): string {
		$container = tribe_project()->container();

		return $container['tribe_parsedown']->render( self::COMPONENT_NAME, self::WRITEUP_MD );
	}

	/**
	 * Get the examples as a block of items to be out put in succession.
	 *
	 * @return array
	 */
	protected function get_examples_markdown(): array {
		$container = tribe_project()->container();

		return [
			$container['tribe_parsedown']->render( self::COMPONENT_NAME, self::EXAMPLES_MD ),
		];
	}

	/**
	 * Return the options markdown for this component.
	 *
	 * @return string
	 */
	protected function get_options_markdown(): string {
		$container = tribe_project()->container();

		return $container['tribe_parsedown']->render( self::COMPONENT_NAME, self::OPTIONS_MD );
	}
}
