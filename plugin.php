<?php
/**
 * Plugin Name:       My Google Client
 * Plugin URI:        https://github.com/mmaarten/my-google-client
 * Description:       Setup a ready to use Google API client.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            Maarten Menten
 * Author URI:        https://profiles.wordpress.org/maartenm/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * textdomain:        my-google-client
 */

$autoloader = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoloader)) {
    require $autoloader;
}

define('MY_GOOGLE_CLIENT_PLUGIN_FILE', __FILE__);

add_action('plugins_loaded', ['\My\GoogleClient\App', 'init']);
