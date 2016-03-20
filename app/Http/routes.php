<?php

Route::get('/', function() {
   return view('welcome');
});

Route::group(['middleware' => ['api']], function () {
    Route::any('{uuid}/{statusCode?}', 'RequestController@create')
        ->where([
            'uuid' => '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$',
            'statusCode' => '^[1-9]{3}$',
        ]);

    Route::post('token', 'TokenController@create');
    Route::get('token/{uuid}', 'TokenController@find');
    Route::delete('token/{uuid}', 'TokenController@delete');
    
    Route::get('token/{uuid}/requests', 'TokenController@requests');
});
