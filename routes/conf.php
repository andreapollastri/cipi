<?php

use App\Http\Controllers\ConfController;
use Illuminate\Support\Facades\Route;

Route::get('/cron/{server_id}', [ConfController::class, 'cron']);
Route::get('/panel', [ConfController::class, 'panel']);
Route::get('/nginx', [ConfController::class, 'nginx']);
Route::get('/host/{site_id}', [ConfController::class, 'host']);
Route::get('/alias/{alias_id}', [ConfController::class, 'alias']);
Route::get('/php/{site_id}', [ConfController::class, 'php']);
Route::get('/supervisor', [ConfController::class, 'supervisor']);
