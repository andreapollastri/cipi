<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Auth::routes(['register' => false]);

Route::group(['prefix' => 'tools'], function () use ($router) {
    Route::get('/start/{servercode}','ApisController@start');
    Route::get('/finalize/{servercode}','ApisController@finalize');
    Route::get('/status/{servercode}','ApisController@status');
    Route::get('/ping/{servercode}','ApisController@ping');
});

Route::group(['prefix' => 'sh'], function () use ($router) {
    Route::get('/go/{servercode}','ShellController@install');
    Route::get('/ha/{servercode}','ShellController@hostadd');
    Route::get('/hd/{servercode}','ShellController@hostdel');
    Route::get('/hm/{servercode}','ShellController@hostmod');
    Route::get('/hg/{servercode}','ShellController@hostget');
    Route::get('/pw/{servercode}','ShellController@passwd');
    Route::get('/st/{servercode}','ShellController@status');
    Route::get('/dy/{servercode}','ShellController@deploy');
});

Route::group(['middleware' => 'auth'], function () use ($router) {
    Route::get('/dashboard', 'DashboardController@index');
});
