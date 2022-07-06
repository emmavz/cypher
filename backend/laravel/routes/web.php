<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Front\HomeController;

use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

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

// Auth
require __DIR__ . '/auth.php';

Route::namespace('Front')->group(function () {
	// Page
	Route::get('/', [PageController::class, 'index'])->name('index');

	Route::middleware('auth:web')->group(function () {

		// Dashboard
		Route::prefix('dashboard')->group(function () {

			Route::get('/', [HomeController::class, 'home'])->name('dashboard');
		});
	});
});


/********************** Admin Routes *********************/
Route::prefix('admin')->name('admin.')->namespace('Admin')->group(function () {

	Route::middleware('auth:admin')->group(function () {

		// Dashboard
		Route::get('/', [AdminHomeController::class, 'home'])->name('dashboard');

		// Profile
		Route::get('profile/edit', [AdminProfileController::class, 'edit'])->name('profile.edit');
		Route::put('profile/update', [AdminProfileController::class, 'update'])->name('profile.update');

		// Truncate
		Route::delete('truncate', [AdminHomeController::class, 'truncate'])->name('truncate');
		Route::delete('delete', [AdminHomeController::class, 'destroy'])->name('delete');
		Route::delete('delete-selected', [AdminHomeController::class, 'destroySelected'])->name('delete-selected');

		// Update Rows
		// Route::put('update-rows', [AdminHomeController::class, 'updateRows'])->name('update-rows');
	});
});
