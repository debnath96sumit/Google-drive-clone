<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::controller(\App\Http\Controllers\FileController::class)
     ->middleware(['auth', 'verified'])->group(function() {
         Route::get('/my-files/{folder?}', 'myFiles')
              ->where('folder', '(.*)')
              ->name('myFiles');
         Route::get('trash', 'trash')->name('trash');
         Route::post('/folder/create', 'createFolder')->name('folder.create');
         Route::post('/file', 'fileStore')->name('file.store');
         Route::delete('/file/delete', 'destroy')->name('file.delete');
         Route::get('/file/download', 'download')->name('file.download');
         Route::post('/file/restore', 'restore')->name('file.restore');
         Route::delete('/file/delete-forever', 'deleteForever')->name('file.deleteForever');
         Route::post('/file/add-to-favourites', 'addToFavourites')->name('file.addToFavourites');
         Route::post('/file/favourites/{file_id?}', 'starredFiles')->name('file.favourites');
         Route::post('/media/share', 'shareMedia')->name('media.share');
         Route::get('/shared-with-me', 'sharedWithMe')->name('file.sharedWithMe');
         Route::get('/file/download-shared-with-me', 'downloadSharedWithMeFiles')->name('file.downloadSharedWithMeFiles');
         Route::get('/file/download-shared-by-me', 'downloadSharedByMeFiles')->name('file.downloadSharedByMeFiles');
         Route::get('/shared-by-me', 'sharedByMe')->name('file.sharedByMe');

     });
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
