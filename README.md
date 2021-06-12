# My Google Client
Setup a ready to use Google API client.

## Usage

      // Alter the client
      add_filter('my_google_client', function ($client) {

        // Set scopes
        $client->setScopes(\Google_Service_Calendar::CALENDAR);

        return $client
      });

## Requirements
- [Composer](https://getcomposer.org/)
- PHP >= 5.6
- [WordPress](https://wordpress.org/) >= 5.0

## Installation
1. [Download](https://github.com/mmaarten/my-google-client/archive/master.zip) and extract zip into `wp-content/plugins/` folder.
1. Run `npm install` to install dependencies.
1. Run `composer install` to install dependencies.
1. Run `npm run build` to compile assets.
1. Activate plugin via WordPress admin menu: Plugins.

## Development
Run `composer install` to install dependencies.
