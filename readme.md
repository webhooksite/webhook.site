# Webhook.site

API and frontend for a site to test your webhooks.

Built with Laravel for the API and Angular.js for the frontend SPA.

Built by Simon Fredsted ([@fredsted](https://twitter.com/fredsted)).

## Requirements

* PHP 5.5 or greater
* Composer (https://getcomposer.org/download/)
* Web server

## Installation

* Run the following commands:
  * `cp .env.example .env`
  * `php artisan key:generate`
  * `composer install`
  * `touch database/database.sqlite` (change config if you want to use another DB type)
  * `php artisan migrate`
* Setup virtual host pointing to the /public folder.


