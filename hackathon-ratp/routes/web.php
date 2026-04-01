<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ComController;
use App\Http\Controllers\ComplaintsController;
use App\Http\Controllers\ManagerController;
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
    Route::get('/profile', [AgentController::class, 'profile'])->name('profile');

    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ComplaintsController::class, 'index'])->name('index');
        Route::get('/{complaint}', [ComplaintsController::class, 'show'])->name('show');
        Route::post('/{complaint}/claim', [ComplaintsController::class, 'claim'])->name('claim');
        Route::post('/{complaint}/close', [ComplaintsController::class, 'close'])->name('close');
        Route::post('/{complaint}/severity', [ComController::class, 'assignSeverity'])->name('severity');
        Route::post('/{complaint}/forward-rh', [ManagerController::class, 'forwardToRh'])->name('forward-rh');
    });

    Route::get('/drivers/{user}', [ManagerController::class, 'showDriver'])->name('drivers.show');

    Route::get('/manage-profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/manage-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/manage-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
