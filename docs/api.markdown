---
title: API
order: 10
---

The API doesn't require authentication and is relatively easy to use. 

At its core, Webhook.site takes your data (HTTP requests) and shows it back to you, and also letting you execute various actions based on the contents.

## 1 Tokens

### 1.1 Create token

**POST** `/token`

A token is a container for requests. You can create as many as you want. They expire after about a week, deleting all requests, too.

#### Request

```json
{
  "default_status": 200,
  "default_content": "Hello world!",
  "default_content_type": "text/html",
  "timeout": 0
}
```

#### Response

`200 OK`

```json
{
  "redirect": false,
  "alias": null,
  "timeout": 0,
  "premium": true,
  "uuid": "9981f9f4-657a-4ebf-be7c-1915bedd4775",
  "ip": "127.0.0.1",
  "user_agent": "Paw\/3.1.8 (Macintosh; OS X\/10.14.6) GCDHTTPRequest",
  "default_content": "Hello world!",
  "default_status": 200,
  "default_content_type": "text\/plain",
  "premium_expires_at": "2019-10-22 10:52:20",
  "created_at": "2019-09-22 10:52:20",
  "updated_at": "2019-09-22 10:52:20"
}
```

### 1.2 Update token

**PUT** `/token/:id`

*Request*

[*See **POST** `/token`*](#11-create-token)

*Response*

[*See **POST** `/token`*](#11-create-token)

### 1.3 Set password [P]

**PUT** `/token/:id/password`

*[Premium]* Sets a password to view the requests of a token.

*Request*

```json
{"password": "hunter2"}
```

*Response*

[*See **POST** `/token`*](#11-create-token)

### 1.4 Set alias [P]

**PUT** `/token/:id/alias`

*[Premium]* Sets the alias for the token. (Can be used when creating requests.)

*Request*

```json
{"alias": "my-webhook"}
```

*Response*

[*See **POST** `/token`*](#11-create-token)

### 1.5 Get token

**GET** `/token/:id`

*Response*

[*See **POST** `/token`*](#11-create-token)

### 1.6 Delete token

**DELETE** `/token/:id`

*Response*

`204 No Content`

## 2 Requests

### 2.1 Create request

***(any method)*** `/:tokenId` <br>
***(any method)*** `/:tokenId/:statusCode` <br>
***(any method)*** `/:tokenId/(anything)`

This request will be stored as a *request*.

If valid `statusCode`, that HTTP status will be used in the response (instead of the default.)

Instead of `tokenId`, an alias can also be supplied.

#### Request

*(Anything.)*

#### Response

*(The default response of the Token.)*

### 2.2 Get requests

**GET** `/token/:id/requests`

Lists all request sent to a token. 

Takes `?password=` parameter.

*Response*

```json
{
  "data": [
    {
      "uuid": "a2a6a4ae-4130-4063-953a-84fa29d81d43",
      "token_id": "a94a7294-c4aa-4074-ab77-c4cf86fd53b1",
      "ip": "127.0.0.1",
      "hostname": "webhook.site",
      "method": "POST",
      "user_agent": "Paw\/3.1.8 (Macintosh; OS X\/10.14.6) GCDHTTPRequest",
      "content": "{"first_name\":\"Arch\",\"last_name\":\"Weber\"}",
      "query": {
        "action": "create"
      },
      "headers": {
        "content-length": [
          "271"
        ],
        "user-agent": [
          "Paw\/3.1.8 (Macintosh; OS X\/10.14.6) GCDHTTPRequest"
        ]
      },
      "url": "https:\/\/webhook.site\/a94a7294-c4aa-4074-ab77-c4cf86fd53b1\/201?",
      "created_at": "2019-10-03 19:06:35",
      "updated_at": "2019-10-03 19:06:35"
    }
  ],
  "total": 1,
  "per_page": 50,
  "current_page": 1,
  "is_last_page": true,
  "from": 1,
  "to": 1
}
```

### 2.3 Get single request

**GET** `/token/:id/request/:id`

*Response*

```json
{
  "uuid": "a2a6a4ae-4130-4063-953a-84fa29d81d43",
  "token_id": "a94a7294-c4aa-4074-ab77-c4cf86fd53b1",
  "ip": "127.0.0.1",
  "hostname": "webhook.site",
  "method": "POST",
  "user_agent": "Paw\/3.1.8 (Macintosh; OS X\/10.14.6) GCDHTTPRequest",
  "content": "{"first_name\":\"Arch\",\"last_name\":\"Weber\"}",
  "query": {
    "action": "create"
  },
  "headers": {
    "content-length": [
      "271"
    ],
    "user-agent": [
      "Paw\/3.1.8 (Macintosh; OS X\/10.14.6) GCDHTTPRequest"
    ]
  },
  "url": "https:\/\/webhook.site\/a94a7294-c4aa-4074-ab77-c4cf86fd53b1\/201?",
  "created_at": "2019-10-03 19:06:35",
  "updated_at": "2019-10-03 19:06:35"
}
```

### 2.4 Get raw request content

**GET** `/token/:id/request/:id/raw`

Returns the request as a response (body, content-type.)

### 2.5 Delete request

**DELETE** `/token/:tokenId/request/(:id)`

Deletes a request. 

If no ID, all requests related to the token will be deleted.

*Response*

`204 No content`
