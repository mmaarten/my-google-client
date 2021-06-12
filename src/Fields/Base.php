<?php

namespace My\GoogleClient\Fields;

abstract class Base
{
    protected $id = null;

    public function __construct($id)
    {
        $this->id = $id;

        add_filter("my_google_client_prepare_field/type={$this->id}", [$this, 'prepare']);
        add_action("my_google_client_render_field/type={$this->id}", [$this, 'render']);
        add_filter("my_google_client_sanitize_field/type={$this->id}", [$this, 'sanitize'], 10, 2);
    }

    public function prepare($field)
    {
        return $field;
    }

    abstract public function render($field);

    public function sanitize($value, $field)
    {
        return $value;
    }
}
