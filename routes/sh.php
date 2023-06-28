<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShellController;

Route::get('/setup/{server_id}', [ShellController::class, 'setup']);
Route::get('/deploy/{site_id}', [ShellController::class, 'deploy']);
Route::get('/servers/rootreset', [ShellController::class, 'serversrootreset']);
Route::get('/newsite', [ShellController::class, 'newsite']);
Route::get('/newsite_nodejs', [ShellController::class, 'newsite_nodejs']);
Route::get('/start_nodejs', [ShellController::class, 'start_nodejs']);
Route::get('/stop_nodejs', [ShellController::class, 'stop_nodejs']);
Route::get('/delsite', [ShellController::class, 'delsite']);
Route::get('/sitepass', [ShellController::class, 'sitepass']);
