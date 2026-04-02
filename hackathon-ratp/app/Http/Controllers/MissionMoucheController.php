<?php

namespace App\Http\Controllers;

use App\Enums\MissionMoucheDecision;
use App\Enums\MissionMoucheStatus;
use App\Enums\UserRole;
use App\Models\MissionMouche;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MissionMoucheController extends Controller
{
    public function index(Request $request): View
    {
        $manager = $request->user();

        $missions = MissionMouche::where('manager_user_id', $manager->id)
            ->with(['driver', 'mouches'])
            ->latest()
            ->paginate(15);

        return view('manager.missions.index', compact('missions'));
    }

    public function create(Request $request): View
    {
        $manager = $request->user();

        $drivers = $manager->chauffeurs()
            ->orderBy('users.last_name')
            ->get(['users.id', 'users.first_name', 'users.last_name']);

        $mouches = User::where('role', UserRole::Mouche)
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('manager.missions.create', compact('drivers', 'mouches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver_user_id' => ['required', 'exists:users,id'],
            'mouche_ids' => ['required', 'array', 'min:1', 'max:3'],
            'mouche_ids.*' => ['exists:users,id'],
        ]);

        $mission = MissionMouche::create([
            'driver_user_id' => $validated['driver_user_id'],
            'manager_user_id' => $request->user()->id,
            'status' => MissionMoucheStatus::EnCours,
        ]);

        $mission->mouches()->attach($validated['mouche_ids']);

        return redirect()->route('missions.show', $mission)
            ->with('success', 'Mission créée. Les mouches ont été assignées.');
    }

    public function show(MissionMouche $mission): View
    {
        $this->authorizeManagerAccess($mission);

        $mission->load(['driver', 'manager', 'mouches', 'rapports.mouche', 'rapports.ligne']);

        return view('manager.missions.show', compact('mission'));
    }

    public function decide(Request $request, MissionMouche $mission): RedirectResponse
    {
        $this->authorizeManagerAccess($mission);

        abort_unless($mission->status === MissionMoucheStatus::Completee, 403);

        $validated = $request->validate([
            'decision' => ['required', 'in:Cloture,Sanctionne'],
            'manager_notes' => ['nullable', 'string', 'max:2000'],
            'sanction_type' => ['required_if:decision,Sanctionne', 'nullable', 'string', 'max:255'],
            'sanction_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $mission->update([
            'status' => MissionMoucheStatus::Decidee,
            'decision' => MissionMoucheDecision::from($validated['decision']),
            'manager_notes' => $validated['manager_notes'],
            'decided_at' => now(),
        ]);

        if ($validated['decision'] === MissionMoucheDecision::Sanctionne->value) {
            Sanction::create([
                'user_id' => $mission->driver_user_id,
                'mission_mouche_id' => $mission->id,
                'type' => $validated['sanction_type'],
                'description' => $validated['sanction_description'] ?? '',
                'sanctioned_at' => now()->toDateString(),
            ]);
        }

        return redirect()->route('missions.show', $mission)
            ->with('success', 'Décision enregistrée.');
    }

    private function authorizeManagerAccess(MissionMouche $mission): void
    {
        abort_unless($mission->manager_user_id === auth()->id(), 403);
    }
}
