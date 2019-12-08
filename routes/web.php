<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

///TEST
Route::get('/test/', function() {



});



Route::get('/', function () { return redirect()->route('dashboard'); });

Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

Route::get('/server/{servercode}','ServerController@index')->name('server');
Route::get('/server/api/start/{servercode}','ApisController@start')->name('serverstart');
Route::get('/server/api/finalize/{servercode}','ApisController@finalize')->name('serverfinalize');
Route::get('/server/api/status/{servercode}','ApisController@status')->name('serverstatus');
Route::get('/server/api/ping/{servercode}','ApisController@ping')->name('serversping');
Route::get('/server/api/sslapplication/{applicationcode}','ApisController@sslapplication')->name('sslapplication');
Route::get('/server/api/sslalias/{aliascode}','ApisController@sslalias')->name('sslalias');

Route::get('/ajaxservers/','ApisController@ajaxservers')->name('ajaxservers');
Route::get('/ajaxapplications/{server}','ApisController@ajaxapplications')->name('ajaxapplications');

Route::get('/servers','ServersController@index')->name('servers');
Route::post('/servers/create/','ServersController@create')->name('servercreate');
Route::post('/servers/delete/','ServersController@delete')->name('serverdelete');

Route::get('/scripts/install/{servercode}','ScriptsController@install')->name('serverinstall');
Route::get('/scripts/hostadd/{servercode}','ScriptsController@hostadd');
Route::get('/scripts/hostdel/{servercode}','ScriptsController@hostdel');
Route::get('/scripts/hostssl/{servercode}','ScriptsController@hostssl');
Route::get('/scripts/passwd/{servercode}','ScriptsController@passwd');
Route::get('/scripts/aliasadd/{servercode}','ScriptsController@aliasadd');
Route::get('/scripts/aliasdel/{servercode}','ScriptsController@aliasdel');
Route::get('/scripts/status/{servercode}','ScriptsController@status');
Route::get('/scripts/deploy/{servercode}','ScriptsController@deploy');

Route::get('/databases','DatabasesController@index')->name('databases');

Route::get('/users','UsersController@index')->name('users');
Route::post('/users/reset/','UsersController@reset')->name('usersreset');

Route::get('/backups','BackupsController@index')->name('backups');

Route::get('/applications','ApplicationsController@index')->name('applications');
Route::post('/applications','ApplicationsController@create')->name('applicationcreate');
Route::post('/applicationdelete','ApplicationsController@delete')->name('applicationdelete');

Route::get('/pdf/{applicationcode}/','ApplicationsController@pdf')->name('pdf');

Route::get('/aliases','AliasesController@index')->name('aliases');
Route::post('/aliases','AliasesController@create')->name('aliascreate');
Route::post('/aliasdelete','AliasesController@delete')->name('aliasdelete');

Route::get('/profile','ProfileController@index')->name('profile');
Route::post('/editprofile','ProfileController@edit')->name('editprofile');
Route::post('/password','ProfileController@password')->name('editpassword');

Auth::routes();

