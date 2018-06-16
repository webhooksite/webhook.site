# Webhook Tester

With the Webhook Tester app, you can easily test HTTP webhooks and other types of HTTP requests. 

Upon visiting the app, you get a random URL to send your requests and webhooks to, and they're all logged in the app – instantly. Check it out at [https://webhook.site](https://webhook.site). 

Built by Simon Fredsted ([@fredsted](https://twitter.com/fredsted)).

## Acknowledgements

The app was built with Laravel for the API and Angular.js for the frontend SPA.

Thanks to [da-n](https://github.com/da-n) for creating the Docker image.

## Donate

* Patreon: https://www.patreon.com/webhooktester
* Bitcoin address: 1Maf64K9Wkpy7oBGEtqEda8H1H2drLSUuF
* Paypal: https://paypal.me/fredsted

## Requirements

* PHP 7
* Redis
* Composer
* Web server

Version 1.1 switched to using Redis for storage. If you want to use SQLite, you can use version 1.0.

## Installation

### Web Server

1. Run the following commands:
   1. `composer install`
   2. `cp .env.example .env` - adjust settings as needed
   3. `php artisan key:generate`
2. Setup virtual host pointing to the /public folder. DigitalOcean has a guide on [how to configure nginx](https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-16-04#step-5-—-configuring-nginx).

### Docker

A Dockerfile is available at hub.docker.com: https://hub.docker.com/r/dahyphenn/webhook.site/. 

### Push functionality

You can use [laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) or Pusher to enable realtime updates. Take a look at the `.env.example` on how to configure it.

For laravel-echo-server, the app expects socket.io to be available at the `/socket.io` path. This can be done with nginx like so:

```
    location /socket.io {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
```
