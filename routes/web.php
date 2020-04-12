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
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/cloud', function () {
    return view('cloud');
});

Route::get('/cloud/api', function () {
    $test = [
        ['id' => 1, 'name' => 'Production 1', 'code' => '123', 'ip' => '123.123.123.123', 'provider' => 'aws', 'location' => 'FRA', 'apps' => "11"],
        ['id' => 2, 'name' => 'Production 2', 'code' => '54345', 'ip' => '324.123.434.241', 'provider' => 'vultr', 'location' => 'NYC', 'apps' => "14"],
        ['id' => 3, 'name' => 'Staging', 'code' => '23123123', 'ip' => '231.232.231.323', 'provider' => 'My home', 'location' => 'MIL', 'apps' => "0"]
    ];
    return response()->json($test);
});
