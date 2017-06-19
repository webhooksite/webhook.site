# Webhook Tester

With the Webhook Tester app, you can easily test HTTP webhooks and other types of HTTP requests. Upon visiting the app, you get a random URL to send your requests and webhooks to, and they're all logged in the app – instantly. Check it out at [https://webhook.site](https://webhook.site). 

Built with Laravel for the API and Angular.js for the frontend SPA.

Built by Simon Fredsted ([@fredsted](https://twitter.com/fredsted)).

Thanks to [Pusher](https://pusher.com) for sponsoring a plan with a higher connection limit!

## Requirements

* PHP 5.5 or greater
* Composer (https://getcomposer.org/download/)
* Web server

## Installation

### On a Web server

*Prerequisites: [Linux, HTTP server, PHP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04).*

1. Run the following commands:
  * `cp .env.example .env`
  * `php artisan key:generate`
  * `composer install`
  * `touch database/database.sqlite` (change config if you want to use another DB type)
  * `php artisan migrate`
2. Setup virtual host pointing to the /public folder.

### Install with Docker

A Dockerfile is available at hub.docker.com: https://hub.docker.com/r/dahyphenn/webhook.site/. Thanks to [da-n](https://github.com/da-n).
