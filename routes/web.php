<?php

use Illuminate\Support\Facades\Route;

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
    return redirect('/home');
});

Route::get('/home', function () {
    return view('dashboard');
});

Route::get('/cloud', function () {
    return view('cloud');
});

Route::get('/cloud/api/list', function () {
    $test = [
        ['name' => 'server1', 'code' => '123', 'ip' => '123.123.123.123', 'provider' => 'AWS', 'location' => 'AMS3', 'apps' => "11"],
        ['name' => 'production', 'code' => '54345', 'ip' => '324.123.434.241', 'provider' => 'Google Cloud', 'location' => 'MIL', 'apps' => "14"]
    ];
    return response()->json($test);
});
