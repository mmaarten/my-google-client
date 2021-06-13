<?php

namespace My\GoogleClient\MenuPages;

use My\GoogleClient\App;
use My\GoogleClient\Helpers;

final class OptionsPage extends Base
{
    private static $instance = null;

    public static function getInstance()
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        parent::__construct('general', [
            'page_title' => __('Google Client', 'my-google-client'),
            'menu_title' => __('Google Client', 'my-google-client'),
        ]);

        add_action('my_google_client_render_field/type=redirect_uri', [$this, 'renderRedirectURI']);
        add_action('my_google_client_render_field/type=authorization', [$this, 'renderAuthorization']);
    }

    public function init()
    {
        $this->addField([
            'label' => __('Client ID', 'my-google-client'),
            'name'  => 'client_id',
        ]);

        $this->addField([
            'label' => __('Client Secret', 'my-google-client'),
            'name'  => 'client_secret',
            'type'  => 'password',
        ]);

        $this->addField([
            'label'       => __('Authorized redirect URI', 'my-google-client'),
            'description' => __('Copy this URL into the "Authorized redirect URIs" field of your Google web application.', 'my-google-client'),
            'name'        => 'redirect_uri',
            'type'        => 'redirect_uri',
        ]);

        $this->addField([
            'label' => __('Authorization', 'my-google-client'),
            'name'  => 'authorization',
            'type'  => 'authorization',
        ]);
    }

    public function renderRedirectURI($field)
    {
        printf(
            '<input type="text" id="%1$s" class="regular-text" value="%2$s" readonly>',
            esc_attr($field['id']),
            esc_url(App::getAuthRedirectURL())
        );
    }

    public function renderAuthorization($field)
    {
        $client_id     = $this->getOption('client_id');
        $client_secret = $this->getOption('client_secret');

        try {
            $client = App::getClient();

            printf(
                '<a href="%1$s" class="button">%2$s</a>',
                esc_url($client->createAuthUrl()),
                esc_html__('Allow plugin to use your Google account', 'my-google-client')
            );

            printf(
                '<p class="description">%s</p>',
                esc_html__('Click the button above to confirm authorization.', 'my-google-client')
            );
        } catch (\Exception $e) {
            Helpers::adminNotice($e->getMessage(), 'error', true);
        }
    }
}
