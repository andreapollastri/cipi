<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;

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

Route::get('/', function () {
    if (filter_var(request()->getHttpHost(), FILTER_VALIDATE_IP) || request()->getHttpHost() == \App\Models\Site::where(['panel' => 1])->pluck('domain')->first()) {
        return view('welcome');
    }
    return 'Domain/Subdomain not configured on this Server!';
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

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
