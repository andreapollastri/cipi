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

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/cloud', function () {
    return view('cloud');
});

Route::get('/cloud/api', function () {
    $test = [
        ['id' => 1, 'name' => 'Production 1', 'code' => '123', 'ip' => '127.0.0.1', 'provider' => 'aws', 'location' => 'FRA', 'apps' => "11"],
        ['id' => 2, 'name' => 'Production 2', 'code' => '54345', 'ip' => '127.0.0.1', 'provider' => 'vultr', 'location' => 'NYC', 'apps' => "14"],
        ['id' => 3, 'name' => 'Staging', 'code' => '435345345123', 'ip' => '127.0.0.1', 'provider' => 'My home', 'location' => 'MIL', 'apps' => "0"],
        ['id' => 4, 'name' => 'Database', 'code' => '43534523', 'ip' => '127.0.0.1', 'provider' => 'google', 'location' => 'AMS', 'apps' => "322"],
        ['id' => 5, 'name' => 'Database backup', 'code' => '45453523', 'ip' => '127.0.0.1', 'provider' => 'google', 'location' => 'AMS', 'apps' => "322"],
        ['id' => 6, 'name' => 'Storage', 'code' => '45435', 'ip' => '127.0.0.1', 'provider' => 'do', 'location' => 'SFO', 'apps' => "322"]
    ];
    return response()->json($test);
});
