<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('my_google_client_general_options');
delete_option('my_google_client_auth_code');
delete_option('my_google_client_access_token');
delete_option('my_google_client_refresh_token');
