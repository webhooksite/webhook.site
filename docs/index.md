# [Webhook.site](https://webhook.site)

[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/fredsted/webhook.site.svg)](https://hub.docker.com/r/fredsted/webhook.site)
[![GitHub last commit](https://img.shields.io/github/last-commit/fredsted/webhook.site.svg)](https://github.com/fredsted/webhook.site/commits/master)

With [Webhook.site](https://webhook.site), you instantly get a unique, random URL that you can use to test and debug Webhooks and HTTP requests, as well as to create your own workflows using the Custom Actions graphical editor or WebhookScript, a simple scripting language, to transform, validate and process HTTP requests.

## What are people using it for?

* Receive Webhooks without needing an internet-facing Web server
* Send Webhooks to a server that’s behind a firewall or private subnet
* Transforming Webhooks into other formats, and re-sending them to different systems
* Connect different APIs that aren’t compatible
* Building contact forms that send emails
* Instantly build APIs without needing infrastructure
Built by Simon Fredsted (@fredsted).

## Open Source

There are two versions of Webhook.site:

* The completely open-source, MIT-licensed version is available on Github, which can be self-hosted using e.g. Docker, is great for testing Webhooks, but doesn’t include features like Custom Actions.

* The cloud version at [https://webhook.site](https://webhook.site) which has more features, some of them requiring a paid subscription.

## Acknowledgements

* The app was built with [Laravel](https://laravel.com) for the API and Angular.js for the frontend SPA.
* WebhookScript based on [Primi](https://github.com/smuuf/Primi) Copyright (c) Přemysl Karbula.
* The WebhookScript editor is using the [Ace](https://ace.c9.io/).
* JSONPath extraction provided by [FlowCommunications](https://github.com/FlowCommunications/JSONPath).
* This documentation site uses [Just the Docs](https://github.com/pmarsceill/just-the-docs), a documentation theme for Jekyll.

**[Full Documentation at docs.webhook.site](https://docs.webhook.site)**


## Prerequisites
Install php and composer
```
brew install php
brew install composer
```

