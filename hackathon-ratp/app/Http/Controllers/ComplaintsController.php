<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComplaintsController extends Controller
{
    public function index(Request $request): View
    {
        return match ($request->user()->role) {
            UserRole::Com => app(ComController::class)->index($request),
            UserRole::Manager => app(ManagerController::class)->index($request),
            UserRole::RH => app(RhController::class)->index($request),
            default => abort(403),
        };
    }

    public function show(Request $request, Complaint $complaint): View
    {
        return match ($request->user()->role) {
            UserRole::Com => app(ComController::class)->show($complaint),
            UserRole::Manager => app(ManagerController::class)->show($complaint, $request),
            UserRole::RH => app(RhController::class)->show($complaint),
            default => abort(403),
        };
    }

    public function claim(Request $request, Complaint $complaint): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Com => app(ComController::class)->claim($complaint, $request),
            UserRole::RH => app(RhController::class)->claim($complaint, $request),
            default => abort(403),
        };
    }

    public function close(Request $request, Complaint $complaint): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Manager => app(ManagerController::class)->close($complaint, $request),
            UserRole::RH => app(RhController::class)->close($complaint, $request),
            default => abort(403),
        };
    }

    public function identifyDriver(Request $request, Complaint $complaint): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Manager => app(ManagerController::class)->identifyDriver($complaint, $request),
            UserRole::RH => app(RhController::class)->identifyDriver($complaint, $request),
            default => abort(403),
        };
    }

    public function sanction(Request $request, Complaint $complaint): RedirectResponse
    {
        return match ($request->user()->role) {
            UserRole::Manager => app(ManagerController::class)->sanction($complaint, $request),
            UserRole::RH => app(RhController::class)->sanction($complaint, $request),
            default => abort(403),
        };
    }
}
