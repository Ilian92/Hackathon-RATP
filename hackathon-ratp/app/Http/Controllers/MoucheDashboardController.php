<?php

namespace App\Http\Controllers;

use App\Enums\MissionMoucheStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MoucheDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $mouche = $request->user();

        $pendingMissions = $mouche->missionsAsMouche()
            ->whereIn('status', [MissionMoucheStatus::EnCours->value, MissionMoucheStatus::Completee->value])
            ->wherePivotNull('submitted_at')
            ->with('driver')
            ->latest('mission_mouches.created_at')
            ->get();

        $submittedMissions = $mouche->missionsAsMouche()
            ->wherePivotNotNull('submitted_at')
            ->with(['driver', 'rapports' => fn ($q) => $q->where('mouche_user_id', $mouche->id)])
            ->latest('mission_mouches.created_at')
            ->get();

        $totalSubmitted = $submittedMissions->count();
        $totalPending = $pendingMissions->count();

        return view('mouche.dashboard', compact(
            'pendingMissions', 'submittedMissions', 'totalSubmitted', 'totalPending'
        ));
    }
}
