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
    Route::get('/pf/{appcode}','ShellController@phpfpm');
    Route::get('/aa/{servercode}','ShellController@aliasadd');
    Route::get('/ad/{servercode}','ShellController@aliasdel');
    Route::get('/ag/{appcode}/{domain}','ShellController@aliasget');
    Route::get('/pw/{servercode}','ShellController@passwd');
    Route::get('/rt/{servercode}','ShellController@root');
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
        Route::get('/{servercode}', 'ServersController@get');
        Route::post('/create', 'ServersController@create');
        Route::post('/destroy', 'ServersController@destroy');
        Route::post('/changeip', 'ServersController@changeip');
        Route::post('/changename', 'ServersController@changename');
        Route::get('/reset/{servercode}', 'ServersController@reset');
        Route::get('/nginx/{servercode}', 'ServersController@nginx');
        Route::get('/php/{servercode}', 'ServersController@php');
        Route::get('/mysql/{servercode}', 'ServersController@mysql');
        Route::get('/redis/{servercode}', 'ServersController@redis');
        Route::get('/supervisor/{servercode}', 'ServersController@supervisor');
    });
    Route::group(['prefix' => 'applications'], function () {
        Route::get('/', 'ApplicationsController@index');
        Route::get('/api', 'ApplicationsController@api');
    });
    Route::group(['prefix' => 'application'], function () {
        Route::post('/create', 'ApplicationsController@create');
        Route::post('/destroy', 'ApplicationsController@destroy');
        Route::get('/pdf/{appcode}', 'ApplicationsController@pdf');
        Route::get('/ssl/{appcode}', 'ApplicationsController@ssl');
    });
    Route::get('/aliases', 'AliasesController@index');
    Route::group(['prefix' => 'alias'], function () {
        Route::post('/create', 'AliasesController@create');
        Route::post('/destroy', 'AliasesController@destroy');
        Route::get('/ssl/{aliascode}', 'AliasesController@ssl');
    });
    Route::group(['prefix' => 'databases'], function () {
        Route::get('/', 'DatabasesController@index');
        Route::post('/reset', 'DatabasesController@reset');
    });
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UsersController@index');
        Route::post('/reset', 'UsersController@reset');
    });
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', 'SettingsController@index');
        Route::post('/username', 'SettingsController@updateUsername');
        Route::post('/password', 'SettingsController@updatePassword');
        Route::post('/smtp', 'SettingsController@updateSmtp');
        Route::get('/secret', 'SettingsController@updateSecret');
        Route::get('/export', 'SettingsController@exportCipi');
        Route::post('/import', 'SettingsController@importCipi');
    });
});
