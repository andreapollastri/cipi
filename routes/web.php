<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'remote'], function () {
    Route::get('/start/{servercode}','RemoteController@start');
    Route::get('/finalize/{servercode}','RemoteController@finalize');
    Route::get('/status/{servercode}','RemoteController@status');
    Route::get('/ping/{servercode}','RemoteController@ping');
});

Route::group(['prefix' => 'sh'], function () {
    Route::get('/go/{servercode}','ShellController@install');
    Route::get('/ha/{servercode}','ShellController@hostadd');
    Route::get('/hd/{servercode}','ShellController@hostdel');
    Route::get('/hg/{appcode}','ShellController@hostget');
    Route::get('/aa/{servercode}','ShellController@aliasadd');
    Route::get('/ad/{servercode}','ShellController@aliasdel');
    Route::get('/ag/{appcode}/{domain}','ShellController@aliasget');
    Route::get('/pw/{servercode}','ShellController@passwd');
    Route::get('/sc','ShellController@ssl');
    Route::get('/st','ShellController@status');
    Route::get('/dy','ShellController@deploy');
    Route::get('/nx','ShellController@nginx');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', 'DashboardController@index');
    Route::group(['prefix' => 'servers'], function () {
        Route::get('/', 'ServersController@index');
        Route::get('/api', 'ServersController@api');
    });
    Route::group(['prefix' => 'server'], function () {
        Route::get('/{servercode}', 'ServerController@index');
        Route::post('/create', 'ServerController@create');
        Route::post('/destroy', 'ServerController@destroy');
        Route::post('/changeip', 'ServerController@changeip');
    });
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', 'SetupController@index');
        Route::post('/profile', 'SetupController@profile');
        Route::post('/password', 'SetupController@password');
    });
});
