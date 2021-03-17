<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfController;

Route::get('/cron/{server_id}', [ConfController::class, 'cron']);
Route::get('/panel', [ConfController::class, 'panel']);
Route::get('/nginx', [ConfController::class, 'nginx']);
Route::get('/host/{site_id}', [ConfController::class, 'host']);
Route::get('/alias/{alias_id}', [ConfController::class, 'alias']);
Route::get('/php/{site_id}', [ConfController::class, 'php']);
