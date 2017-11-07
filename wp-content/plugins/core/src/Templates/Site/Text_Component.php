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

	const COMPONENT = 'text';
	const WRITEUP   = 'writeup.md';
	const OPTIONS   = 'options.md';
	const EXAMPLES  = 'example.md';

	/**
	 * Array of component options.
	 *
	 * @var $options array
	 */
	public $options = [
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
		$data['parsedown']          = $this->get_component_text_markdown();

		return $data;
	}

	/**
	 * Render the component
	 *
	 * @return string
	 */
	protected function get_component_text(): string {

		$text_object = Text::factory( $this->options );

		return $text_object->render();
	}

	protected function get_component_text_markdown() {
		$markdown = new \Tribe_Parsedown();

		return [
			'example' => $markdown->factory( self::COMPONENT, self::EXAMPLES ),
			'writeup' => $markdown->factory( self::COMPONENT, self::WRITEUP ),
			'options' => $markdown->factory( self::COMPONENT, self::OPTIONS ),
		];
	}
}
