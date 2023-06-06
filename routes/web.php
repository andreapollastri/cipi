<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DatabaseController;

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

Route::redirect('/', '/dashboard');

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});


Route::get('/database', function () {
    return view('database');
});

//phpmyadmin route
Route::get('/pma', function () {
    return redirect()->to('mysecureadmin/index.php');
});
//database
Route::get('/data', [DatabaseController::class, 'viewdatabase'])->name('data');
Route::post('/createdatab', [DatabaseController::class,'createdatabase'])->name('createdatab');
Route::post('/createuser', [DatabaseController::class,'createuser'])->name('createuser');
Route::post('/linkdatabuser', [DatabaseController::class,'linkdatabaseuser'])->name('linkdatabuser');



Route::get('/servers', function () {
    return view('servers');
});

Route::get('/servers/{server_id}', function ($server_id) {
    return view('server', compact('server_id'));
});

Route::get('/sites', function () {
    return view('sites');
});

Route::get('/sites/{site_id}', function ($site_id) {
    return view('site', compact('site_id'));
});

Route::get('/settings', function () {
    return view('settings');
});

Route::get('/pdf/{site_id}/{token}', [SiteController::class, 'pdf']);

// Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
//  \UniSharp\LaravelFilemanager\Lfm::routes();
// });
