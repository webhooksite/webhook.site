<?php
/** @var $router \Illuminate\Routing\Router */

// Patterns
$router->pattern('requestId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
$router->pattern('tokenId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
$router->pattern('statusCode', '[1-5][0-9][0-9]');
$router->pattern('any', '.*');

// SPA view
$router->get('/', 'IndexController');

$router->group(['middleware' => ['api']], function () use ($router) {
    // Requests
    $router->any('{tokenId}/{statusCode?}', 'RequestController@create');
    $router->any('{tokenId}/{any}', 'RequestController@create');
    $router->get('token/{tokenId}/requests', 'RequestController@all');
    $router->get('token/{tokenId}/request/{requestId}', 'RequestController@find');
    $router->get('token/{tokenId}/request/{requestId}/raw', 'RequestController@raw');
    $router->delete('token/{tokenId}/request/{requestId}', 'RequestController@delete');
    $router->delete('token/{tokenId}/request', 'RequestController@deleteByToken');

    // Tokens
    $router->get('token/{tokenId}', 'TokenController@find');
    $router->post('token', 'TokenController@create');
    $router->delete('token/{tokenId}', 'TokenController@delete');
    $router->put('token/{tokenId}', 'TokenController@update');
    $router->put('token/{tokenId}/cors/toggle', 'TokenController@toggleCors');
});
