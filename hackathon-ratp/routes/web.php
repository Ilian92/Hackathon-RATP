<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/qrcode', [QrCodeController::class, 'show'])->name('qrcode.show');
Route::get('/qrcode-expired', [QrCodeController::class, 'expired'])->name('qrcode.expired');
Route::get('/qrcode/{token}', [QrCodeController::class, 'landing'])->name('qrcode.landing');
Route::get('/qrcode/{token}/satisfaction', [QrCodeController::class, 'satisfactionCreate'])->name('satisfaction.create');
Route::post('/qrcode/{token}/satisfaction', [QrCodeController::class, 'satisfactionStore'])->name('satisfaction.store');
Route::get('/qrcode/{token}/complaint', [QrCodeController::class, 'complaintCreate'])->name('complaint.create');
Route::post('/qrcode/{token}/complaint', [QrCodeController::class, 'complaintStore'])->name('complaint.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/agent/profile', [AgentController::class, 'profile'])->name('agent.profile');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
