<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\Api\FileManagerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Servers
Route::get('/servers', [ServerController::class, 'index']);
Route::post('/servers', [ServerController::class, 'create']);
Route::get('/servers/panel', [ServerController::class, 'panel']);
Route::patch('/servers/panel/domain', [ServerController::class, 'paneldomain']);
Route::post('/servers/panel/ssl', [ServerController::class, 'panelssl']);
Route::delete('/servers/{server_id}', [ServerController::class, 'destroy']);
Route::get('/servers/{server_id}', [ServerController::class, 'show']);
Route::patch('/servers/{server_id}', [ServerController::class, 'edit']);
Route::get('/servers/{server_id}/ping', [ServerController::class, 'ping']);
Route::get('/servers/{server_id}/healthy', [ServerController::class, 'healthy']);
Route::post('/servers/{server_id}/rootreset', [ServerController::class, 'rootreset']);
Route::post('/servers/{server_id}/servicerestart/{service}', [ServerController::class, 'servicerestart']);
Route::get('/servers/{server_id}/sites', [ServerController::class, 'sites']);
Route::get('/servers/{server_id}/domains', [ServerController::class, 'domains']);

//Sites
Route::get('/sites', [SiteController::class, 'index']);
Route::post('/sites', [SiteController::class, 'create']);
Route::patch('/sites/{site_id}', [SiteController::class, 'edit']);
Route::delete('/sites/{site_id}', [SiteController::class, 'destroy']);
Route::get('/sites/{site_id}', [SiteController::class, 'show']);
Route::post('/sites/{site_id}/ssl', [SiteController::class, 'ssl']);
Route::post('/sites/{site_id}/reset/ssh', [SiteController::class, 'resetssh']);
Route::post('/sites/{site_id}/reset/db', [SiteController::class, 'resetdb']);
Route::get('/sites/{site_id}/aliases', [SiteController::class, 'aliases']);
Route::post('/sites/{site_id}/aliases', [SiteController::class, 'createalias']);
Route::delete('/sites/{site_id}/aliases/{alias_id}', [SiteController::class, 'destroyalias']);




Route::get('files/{folder_name?}', [FileManagerController::class,'index'])->where('folder_name', '(.*)')->name('files.index');
Route::post('files/view', [FileManagerController::class, 'show'])->name('files.show');
Route::post('files/edit', [FileManagerController::class, 'edit'])->name('files.edit');
Route::post('files/store', [FileManagerController::class, 'store'])->name('files.store');
Route::post('files/download', [FileManagerController::class, 'download'])->name('files.download');
Route::post('files/create-directory', [FileManagerController::class, 'createDirectory'])->name('files.create.directory');
Route::post('files/create-file', [FileManagerController::class, 'createFile'])->name('files.create.file');
Route::post('files/rename-file', [FileManagerController::class, 'renameFile'])->name('files.rename.file');
Route::post('files/copy-file', [FileManagerController::class, 'copy'])->name('files.copy');
Route::post('files/move-file', [FileManagerController::class, 'move'])->name('files.move');
Route::post('files/delete', [FileManagerController::class, 'destroy'])->name('files.delete');

Route::get('download_file_object/{id}', [FileManagerController::class, 'downloadObject']);
Route::get('show-media-file/{id}', [FileManagerController::class, 'showMediaFile']);
