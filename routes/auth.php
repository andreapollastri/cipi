<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/', [AuthController::class, 'login']);
Route::get('/', [AuthController::class, 'refresh']);
Route::patch('/', [AuthController::class, 'update']);
Route::delete('/', [AuthController::class, 'logout']);
