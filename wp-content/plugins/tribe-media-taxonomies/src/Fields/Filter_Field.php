<?php
/**
 * Filter Field - and abstract class for setting up fields to be added to the upload screen.
 *
 * @package Tribe\Media
 * @version 1.0
 * @since 2.0
 */

namespace Tribe\Media\Fields;

/**
 * Class Filter_Field
 */
abstract class Filter_Field {

    /**
     * @var string
     */
    protected $label;

    /**
     * Filter_Field constructor.
     *
     * @param string $label
     */
    function __construct( string $label ) {
        $this->label = $label;
    }

    /**
     * Get the HTML for a given field.
     *
     * @return mixed
     */
    abstract protected function get_field_html();

    /**
     * Get an array of information for a field.
     *
     * @return array
     */
    public function get_field_array() {
        return [
            'label'        => $this->label,
            'input'        => 'html',
            'html'         => $this->get_field_html(),
            'show_in_edit' => true,
        ];
    }

}