<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfController;

Route::get('/cron/{server_id}', [ConfController::class, 'cron']);
Route::get('/panel', [ConfController::class, 'panel']);
Route::get('/nginx', [ConfController::class, 'nginx']);
Route::get('/host/{site_id}', [ConfController::class, 'host']);
Route::get('/host_nodejs/{site_id}', [ConfController::class, 'host_nodejs']);
Route::get('/alias/{alias_id}', [ConfController::class, 'alias']);
Route::get('/php/{site_id}', [ConfController::class, 'php']);
Route::get('/supervisor', [ConfController::class, 'supervisor']);
