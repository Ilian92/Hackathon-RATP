<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ComController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RhController;
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
    Route::get('/profile', [AgentController::class, 'profile'])->name('profile');

    Route::prefix('com')->name('com.')->group(function () {
        Route::get('/complaints', [ComController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [ComController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/claim', [ComController::class, 'claim'])->name('complaints.claim');
        Route::post('/complaints/{complaint}/severity', [ComController::class, 'assignSeverity'])->name('complaints.severity');
        Route::post('/complaints/{complaint}/forward-manager', [ComController::class, 'forwardToManager'])->name('complaints.forward-manager');
    });

    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/complaints', [ManagerController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [ManagerController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/forward-rh', [ManagerController::class, 'forwardToRh'])->name('complaints.forward-rh');
        Route::post('/complaints/{complaint}/close', [ManagerController::class, 'close'])->name('complaints.close');
        Route::get('/drivers/{user}', [ManagerController::class, 'showDriver'])->name('drivers.show');
    });

    Route::prefix('rh')->name('rh.')->group(function () {
        Route::get('/complaints', [RhController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [RhController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/claim', [RhController::class, 'claim'])->name('complaints.claim');
        Route::post('/complaints/{complaint}/close', [RhController::class, 'close'])->name('complaints.close');
    });

    Route::get('/manage-profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/manage-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/manage-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
