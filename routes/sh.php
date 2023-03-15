<?php

use App\Http\Controllers\ShellController;
use Illuminate\Support\Facades\Route;

Route::get('/setup/{server_id}', [ShellController::class, 'setup']);
Route::get('/deploy/{site_id}', [ShellController::class, 'deploy']);
Route::get('/servers/rootreset', [ShellController::class, 'serversrootreset']);
Route::get('/newsite', [ShellController::class, 'newsite']);
Route::get('/delsite', [ShellController::class, 'delsite']);
Route::get('/sitepass', [ShellController::class, 'sitepass']);

// Client Patch
Route::get('/client-patch/202112091', [ShellController::class, 'patch202112091']);
Route::get('/client-patch/202112101', [ShellController::class, 'patch202112101']);
Route::get('/client-patch/202112181', [ShellController::class, 'patch202112181']);
