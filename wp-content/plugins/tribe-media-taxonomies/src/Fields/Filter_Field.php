<?php

namespace Tribe\Media\Fields;

abstract class Filter_Field {

    protected $label;

    function __construct( $label ) {
        $this->label = $label;
    }

    abstract protected function get_field_html();

    public function get_field_array() {
        return [
            'label'        => $this->label,
            'input'        => 'html',
            'html'         => $this->get_field_html(),
            'show_in_edit' => true,
        ];
    }

}