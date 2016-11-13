<?php

// Patterns

Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
Route::pattern('statusCode', '[1-5][0-9][0-9]');

Route::get('/', 'HomeController@index');

Route::group(['middleware' => ['api']], function () {
    Route::any('{uuid}/{statusCode?}', 'RequestController@create');
    Route::get('token/{uuid}/requests', 'RequestController@all');

    Route::post('token', 'TokenController@create');
    Route::get('token/{uuid}', 'TokenController@find');
    Route::delete('token/{uuid}', 'TokenController@delete');
});
