<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DailyEntryController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

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

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (guest middleware ensures authenticated users are redirected away)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout route (requires authentication)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Items Management
    Route::resource('items', ItemController::class);
    
    // Daily Entries
    Route::resource('entries', DailyEntryController::class)->except(['update']);
    Route::post('/entries/{entry}/items', [DailyEntryController::class, 'addItem'])->name('entries.items.store');
    Route::delete('/entry-items/{entryItem}', [DailyEntryController::class, 'removeItem'])->name('entry-items.destroy');
    
    // Records Viewer
    Route::get('/records', [RecordController::class, 'index'])->name('records.index');
    
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});