<?php

namespace My\GoogleClient;

use \My\GoogleClient\MenuPages\OptionsPage;

class App
{
    /**
     * Init
     */
    public static function init()
    {
        OptionsPage::getInstance()->init();

        add_action('init', [__CLASS__, 'loadTextdomain']);
        add_action('admin_init', [__CLASS__, 'authenticate']);
        add_action('admin_init', [__CLASS__, 'addFieldTypes']);
    }

    /**
     * Load textdomain
     */
    public static function loadTextdomain()
    {
        load_plugin_textdomain('my-google-client', false, dirname(plugin_basename(MY_GOOGLE_CLIENT_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Add settings fieldtypes
     */
    public static function addFieldTypes()
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

    /**
     * Get client authentication redirect URI
     */
    public static function getAuthRedirectURL()
    {
        $redirect_url = OptionsPage::getInstance()->getPageURL();

        if (defined('MY_GOOGLE_CLIENT_AUTH_REDIRECT_URL')) {
            $redirect_url = MY_GOOGLE_CLIENT_AUTH_REDIRECT_URL;
        }

        return $redirect_url;
    }

    /**
     * Get option value from options page
     */
    public static function getOption($name)
    {
        return OptionsPage::getInstance()->getOption($name);
    }

    /**
     * Authenticate
     */
    public static function authenticate()
    {
        if (! OptionsPage::getInstance()->isCurrentPage()) {
            return;
        }

        // Save the authentication code received from Google.
        if (isset($_GET['scope']) && isset($_GET['code'])) {
            $scope = urldecode($_GET['scope']);
            $code  = urldecode($_GET['code']);

            error_log('auth code set').

            update_option('my_google_client_auth_code', $code);
        }
    }

    /**
     * Get Google API client
     *
     * @link https://developers.google.com/calendar/quickstart/php
     */
    public static function getClient()
    {
        $client = null;

        try {
            $client = new \Google_Client();
            $client->setApplicationName('My Google Client');
            // see: https://developers.google.com/identity/protocols/oauth2/scopes
            //$client->setScopes(\Google_Service_Calendar::CALENDAR_READONLY);
            $client->setAuthConfig([
                'client_id'     => self::getOption('client_id'),
                'client_secret' => self::getOption('client_secret'),
                'redirect_uris' => [self::getAuthRedirectURL()],
            ]);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');
            $client->setState(OptionsPage::getInstance()->getPageURL());

            // Alter client settings
            $client = apply_filters('my_google_client', $client);

            // Set previously authorized token, if it exists.
            if (get_option('my_google_client_access_token')) {
                $client->setAccessToken(get_option('my_google_client_access_token'));
            }

            // If there is no previous token or it's expired.
            if ($client->isAccessTokenExpired()) {
                error_log('Token expired');
                // Refresh the token if possible, else fetch a new one.
                $refresh_token = $client->getRefreshToken();
                if (! $refresh_token && get_option('my_google_client_refresh_token')) {
                    $refresh_token = get_option('my_google_client_refresh_token');
                }
                if ($refresh_token) {
                    error_log('Use refresh token');
                    $access_token = $client->fetchAccessTokenWithRefreshToken($refresh_token);
                    $client->setAccessToken($access_token);
                } else {
                    error_log('Use auth code');
                    // Exchange authorization code for an access token.
                    $access_token = $client->fetchAccessTokenWithAuthCode(get_option('my_google_client_auth_code'));
                    $client->setAccessToken($access_token);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $access_token)) {
                        throw new Exception(join(', ', $access_token));
                    }
                }

                // Save the tokens.
                update_option('my_google_client_access_token', $client->getAccessToken());
                update_option('my_google_client_refresh_token', $client->getRefreshToken());
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $client;
    }
}
