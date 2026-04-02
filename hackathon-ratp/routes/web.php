<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ComController;
use App\Http\Controllers\ComplaintsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\MissionMoucheController;
use App\Http\Controllers\MoucheDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicComplaintController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RapportMoucheController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicComplaintController::class, 'index'])->name('home');
Route::post('/', [PublicComplaintController::class, 'store'])->name('home.store');

Route::get('/qrcode', [QrCodeController::class, 'show'])->name('qrcode.show');
Route::get('/qrcode-expired', [QrCodeController::class, 'expired'])->name('qrcode.expired');
Route::get('/qrcode/{token}', [QrCodeController::class, 'landing'])->name('qrcode.landing');
Route::get('/qrcode/{token}/satisfaction', [QrCodeController::class, 'satisfactionCreate'])->name('satisfaction.create');
Route::post('/qrcode/{token}/satisfaction', [QrCodeController::class, 'satisfactionStore'])->name('satisfaction.store');
Route::get('/qrcode/{token}/complaint', [QrCodeController::class, 'complaintCreate'])->name('complaint.create');
Route::post('/qrcode/{token}/complaint', [QrCodeController::class, 'complaintStore'])->name('complaint.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AgentController::class, 'profile'])->name('profile');

    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/', [ComplaintsController::class, 'index'])->name('index');
        Route::get('/{complaint}', [ComplaintsController::class, 'show'])->name('show');
        Route::post('/{complaint}/claim', [ComplaintsController::class, 'claim'])->name('claim');
        Route::post('/{complaint}/close', [ComplaintsController::class, 'close'])->name('close');
        Route::post('/{complaint}/identify-driver', [ComplaintsController::class, 'identifyDriver'])->name('identify-driver');
        Route::post('/{complaint}/sanction', [ComplaintsController::class, 'sanction'])->name('sanction');
        Route::post('/{complaint}/gratify', [ComplaintsController::class, 'gratify'])->name('gratify');
        Route::post('/{complaint}/severity', [ComController::class, 'assignSeverity'])->name('severity');
        Route::post('/{complaint}/forward-rh', [ManagerController::class, 'forwardToRh'])->name('forward-rh');
    });

    Route::get('/drivers/{user}', [ManagerController::class, 'showDriver'])->name('drivers.show');

    // Missions mouche (manager)
    Route::get('/missions', [MissionMoucheController::class, 'index'])->name('missions.index');
    Route::get('/missions/create', [MissionMoucheController::class, 'create'])->name('missions.create');
    Route::post('/missions', [MissionMoucheController::class, 'store'])->name('missions.store');
    Route::get('/missions/{mission}', [MissionMoucheController::class, 'show'])->name('missions.show');
    Route::patch('/missions/{mission}/decide', [MissionMoucheController::class, 'decide'])->name('missions.decide');

    // Mouche dashboard & rapports
    Route::get('/mouche/dashboard', [MoucheDashboardController::class, 'index'])->name('mouche.dashboard');
    Route::get('/mouche/missions/{mission}/rapport', [RapportMoucheController::class, 'create'])->name('rapport.create');
    Route::post('/mouche/missions/{mission}/rapport', [RapportMoucheController::class, 'store'])->name('rapport.store');

    Route::get('/manage-profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/manage-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/manage-profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
