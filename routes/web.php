<?php

use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DatabaseController;

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

Route::redirect('/', '/dashboard');

Route::get('/login', function () {
    return view('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});


Route::get('/database', function () {
    return view('database');
});

//phpmyadmin route
Route::get('/pma', function () {
    return redirect()->to('mysecureadmin/index.php');
});

//phpmyadmin route with autologin
Route::get('/autopma/{site_id}', [SiteController::class, 'autoLoginPMA'])->name('autopma');
//database
Route::get('/data', [DatabaseController::class, 'viewdatabase'])->name('data');
Route::post('/createdatab', [DatabaseController::class,'createdatabase'])->name('createdatab');
Route::post('/createuser', [DatabaseController::class,'createuser'])->name('createuser');
Route::post('/linkdatabuser', [DatabaseController::class,'linkdatabaseuser'])->name('linkdatabuser');



Route::get('/servers', function () {
    return view('servers');
});

Route::get('/servers/{server_id}', function ($server_id) {
    return view('server', compact('server_id'));
});

Route::get('/sites', function () {
    return view('sites');
});

Route::get('/sites/{site_id}', function ($site_id) {
    return view('site', compact('site_id'));
});

Route::get('/settings', function () {
    return view('settings');
});

Route::get('/pdf/{site_id}/{token}', [SiteController::class, 'pdf']);

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

// File::put();

// Route::get('/test-server', function () {
//     // Get an array of files in the directory
//     $files = Storage::allDirectories('cipi');

//     dd(File::directories('C:\Users\patrick.udoh\Downloads\Grind'));

//     dd($files);

//     // Loop through the files and do something with each one
//     foreach ($files as $file) {

//         // Do something with the file, such as get its name or size

//         if ($file->isDir()) {
//             echo "there is a directory";
//             // Do something with the directory, such as get its name
//             $dirname = $file->getRelativePathname();

//             echo $dirname;
//         } else {
//             $filename = $file->getFilename();
//             $filesize = $file->getSize();

//             // echo $filename . " - " . $filesize;
//         }
//     }
// });
