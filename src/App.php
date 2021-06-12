<?php

namespace My\GoogleClient;

use \My\GoogleClient\MenuPages\OptionsPage;

class App
{
    public static function init()
    {
        OptionsPage::getInstance()->init();

        add_action('admin_init', [__CLASS__, 'authenticate']);
        add_action('admin_init', [__CLASS__, 'initFields']);
    }

    public static function initFields()
    {
        $fields = [
            'Text',
            'Password',
        ];
        foreach ($fields as $class) {
            $class = __NAMESPACE__ . '\\Fields\\' . $class;
            $instance = new $class();
        }
    }

    public static function getClientRedirectURI()
    {
        return apply_filters('my_google_client_redirect_uri', site_url());
    }

    public static function getOption($name)
    {
        return OptionsPage::getInstance()->getOption($name);
    }

    public static function authenticate()
    {
        if (! OptionsPage::getInstance()->isCurrentPage()) {
            return;
        }

        if (isset($_GET['scope']) && isset($_GET['code'])) {
            $scope = urldecode($_GET['scope']);
            $code  = urldecode($_GET['code']);

            // TODO : check scopes.
            update_option('my_google_client_auth_code', $code);
        }
    }

    public static function getClient()
    {
        $client_id     = self::getOption('client_id');
        $client_secret = self::getOption('client_secret');
        $auth_code     = get_option('my_google_client_auth_code');
        $access_token  = get_option('my_google_client_access_token');

        $client = new \Google_Client();
        $client->setApplicationName('My Google Calendar');
        //$client->setScopes(\Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig([
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uris' => [self::getClientRedirectURI()],
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        // Adds 'state' param to the authorization request
        $client->setState(OptionsPage::getInstance()->getPageURL());

        // Alter client settings
        $client = apply_filters('my_google_client', $client);

        // Set access token if exists.
        if ($access_token) {
            $client->setAccessToken($access_token);
        }

        // If there is no token or it's expired.
        if ($client->isAccessTokenExpired()) {
            error_log('access token expired or does not exist.');
            // Refresh the token if possible, else fetch a new one.
            $access_token = null;
            $refresh_token = $client->getRefreshToken();
            if ($refresh_token) {
                $access_token = $client->fetchAccessTokenWithRefreshToken($refresh_token);
                error_log('Token set via refresh token.');
            } elseif ($auth_code) {
                $access_token = $client->fetchAccessTokenWithAuthCode($auth_code);
                error_log('Token set via authentication code.');
            }

            if ($access_token) {
                $client->setAccessToken($access_token);

                // Check to see if there was an error.
                if (array_key_exists('error', $access_token)) {
                    throw new Exception(join(', ', $access_token));
                }
            }

            // Save the token.
            update_option('my_google_client_access_token', $client->getAccessToken());
        }
        return $client;
    }
}
