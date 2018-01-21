<?php

// Patterns
Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Route::pattern('statusCode', '[1-5][0-9][0-9]');
Route::pattern('any', '.*');

// SPA view
Route::get('/', function () { return view('app'); });

Route::group(['middleware' => ['api']], function () {
    // Requests
    Route::any('{uuid}/{statusCode?}', 'RequestController@create');
    Route::any('{uuid}/{any}', 'RequestController@create');
    Route::get('token/{uuid}/requests', 'RequestController@all');
    Route::get('token/{tokenId}/request/{requestId}', 'RequestController@find');
    Route::delete('token/{tokenId}/request/{requestId}', 'RequestController@delete');

    // Tokens
    Route::get('token/{uuid}', 'TokenController@find');
    Route::post('token', 'TokenController@create');
    Route::delete('token/{uuid}', 'TokenController@delete');
});
