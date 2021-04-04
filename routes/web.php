<?php

use App\Http\Controllers\ListingController;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Broadcast;
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

Route::middleware(['auth'])->group(function() {
	Route::get('/dashboard', function () {
		return Inertia::render('Dashboard');
	})->name('dashboard');
});

Route::middleware(['auth.lists:yes'])->group(function() {
	Route::get('/lists/{listId}', [ ListingController::class, 'show' ]);
});

Route::middleware(['auth.lists'])->group(function() {
	Route::post('/lists/{listId}', [ ListingController::class, 'store' ]);

	Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])
		->name('broadcast.auth')
		->withoutMiddleware([ VerifyCsrfToken::class]);
});
