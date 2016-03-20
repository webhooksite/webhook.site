# Webhook.site

API and frontend for a site to test your webhooks.

Installation:

* `cp .env.example .env`
* `composer install`
* `touch database/database.sqlite` (change config if you want to use another DB type)
* `php artisan migrate`
* Setup virtual host pointing to the /public folder.


