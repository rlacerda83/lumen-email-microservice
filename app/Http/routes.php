<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => 'api.auth'], function ($api) {
    $api->post('emails/send', 'App\Http\Controllers\EmailController@send');

    $api->get('emails', 'App\Http\Controllers\V1\EmailController@index');

    $api->get('emails/{id}', 'App\Http\Controllers\EmailController@get');

    $api->delete('emails/{id}', 'App\Http\Controllers\EmailController@delete');
});

$api->version('v2',  function ($api) {
    $api->get('emails/', 'App\Http\Controllers\V2\EmailController@index');
});
