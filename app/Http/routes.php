<?php

// Patterns
Route::pattern('requestId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Route::pattern('tokenId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Route::pattern('statusCode', '[1-5][0-9][0-9]');
Route::pattern('any', '.*');

// SPA view
Route::get('/', 'IndexController');

Route::group(['middleware' => ['api']], function () {
    // Requests
    Route::any('{tokenId}/{statusCode?}', 'RequestController@create');
    Route::any('{tokenId}/{any}', 'RequestController@create');
    Route::get('token/{tokenId}/requests', 'RequestController@all');
    Route::get('token/{tokenId}/request/{requestId}', 'RequestController@find');
    Route::delete('token/{tokenId}/request/{requestId}', 'RequestController@delete');

    // Tokens
    Route::get('token/{tokenId}', 'TokenController@find');
    Route::post('token', 'TokenController@create');
    Route::delete('token/{tokenId}', 'TokenController@delete');
});
