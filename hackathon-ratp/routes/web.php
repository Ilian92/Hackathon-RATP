<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/qrcode', [QrCodeController::class, 'show'])->name('qrcode.show');
Route::get('/satisfaction', [QrCodeController::class, 'satisfactionCreate'])->name('satisfaction.create');
Route::post('/satisfaction', [QrCodeController::class, 'satisfactionStore'])->name('satisfaction.store');
Route::get('/complaint', [QrCodeController::class, 'complaintCreate'])->name('complaint.create');
Route::post('/complaint', [QrCodeController::class, 'complaintStore'])->name('complaint.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
