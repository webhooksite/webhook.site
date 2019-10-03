---
title: Installation
order: 20
---

You can choose to run Webhook.site either via Docker, or install it on a Web server with PHP7 support.

### 1 Docker

The provided Docker Compose file sets up a complete environment that runs the Webhook.site image and all dependencies (Redis, Laravel Echo Server, etc.). Note that if running this in production, you should probably run a Redis server that persists data to disk. The Docker image is also not tuned for large amount of traffic.

1. Run `docker-compose up`
2. The app is available on [http://127.0.0.1:8084](http://127.0.0.1:8084).

### 2 Web Server


#### 2.1 Requirements

* PHP 7
* Redis
* Composer
* Web server – nginx, apache2

Version 1.1 switched to using Redis for storage. If you want to use SQLite, you can use version 1.0.

DigitalOcean has a guide on [how to configure nginx](https://www.digitalocean.com/community/tutorials/how-to-deploy-a-laravel-application-with-nginx-on-ubuntu-16-04#step-5-—-configuring-nginx).

#### 2.1 Installation Guide

1. Run the following commands:
   1. `composer install`
   2. `cp .env.example .env` - adjust settings as needed
   3. `php artisan key:generate`
2. Setup virtual host pointing to the `/public` folder. 

### 3 Push functionality

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