<?php

use App\Http\Controllers\HistoryPelaporanP2HController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard'); // Redirect '/' to '/login'

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth'])->name('dashboard');

// Route::get('/test', function () {
//     return Inertia::render('Test');
// })->middleware(['auth'])->name('test');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [HistoryPelaporanP2HController::class, 'index'])->name('dashboard');
    Route::get('/fetch-data-table-p2h', [HistoryPelaporanP2HController::class, 'fetchDataTableP2H'])->name('fetch-data-table-p2h');
    Route::get('/get-info-kerusakan/{id}', [HistoryPelaporanP2HController::class, 'getListKerusakan'])->name('get-info-kerusakan');

    Route::get('/get-detail-laporan-p2h/{id}', [HistoryPelaporanP2HController::class, 'getDetailLaporanP2h'])->name('get-detail-laporan-p2h');
    Route::post('/change-status-fu-kerusakan', [HistoryPelaporanP2HController::class, 'changeStatusFuKerusakan'])->name('change-status-fu-kerusakan');
});

require __DIR__ . '/auth.php';
