<?php

namespace My\GoogleClient;

class Helpers
{
    public static function adminNotice($message, $type = 'info', $inline = false)
    {
        printf(
            '<div class="notice notice-%1$s%3$s"><p>%2$s</p></div>',
            sanitize_html_class($type),
            esc_html($message),
            $inline ? ' inline' : ''
        );
    }
}
