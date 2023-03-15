<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/', [AuthController::class, 'login']);
Route::get('/', [AuthController::class, 'refresh']);
Route::patch('/', [AuthController::class, 'update']);
Route::delete('/', [AuthController::class, 'logout']);
