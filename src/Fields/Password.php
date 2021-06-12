<?php

namespace My\GoogleClient\Fields;

class Password extends Base
{
    public function __construct()
    {
        parent::__construct('password');
    }

    public function prepare($field)
    {
        return $field;
    }

    public function render($field)
    {
        $field = wp_parse_args($field, [
            'id'    => '',
            'name'  => '',
            'value' => '',
        ]);

        printf(
            '<input type="password" id="%1$s" class="regular-text" name="%2$s" value="%3$s">',
            esc_attr($field['id']),
            esc_attr($field['name']),
            esc_attr($field['value'])
        );
    }

    public function sanitize($value, $field)
    {
        return $value;
    }
}
