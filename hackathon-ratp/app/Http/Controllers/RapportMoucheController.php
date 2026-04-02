<?php

namespace App\Http\Controllers;

use App\Enums\MissionMoucheStatus;
use App\Models\Ligne;
use App\Models\MissionMouche;
use App\Models\RapportMouche;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RapportMoucheController extends Controller
{
    public function create(Request $request, MissionMouche $mission): View
    {
        $mouche = $request->user();

        $this->authorizeMoucheAccess($mission, $mouche->id);

        abort_if($mission->status === MissionMoucheStatus::Decidee, 403, 'Cette mission est déjà clôturée.');

        $alreadySubmitted = $mission->mouches()
            ->wherePivot('user_id', $mouche->id)
            ->wherePivotNotNull('submitted_at')
            ->exists();

        abort_if($alreadySubmitted, 403, 'Vous avez déjà soumis votre rapport pour cette mission.');

        $lignes = Ligne::orderBy('nom')->get(['id', 'nom']);
        $mission->load('driver');

        return view('mouche.rapport.create', compact('mission', 'lignes'));
    }

    public function store(Request $request, MissionMouche $mission): RedirectResponse
    {
        $mouche = $request->user();

        $this->authorizeMoucheAccess($mission, $mouche->id);

        $alreadySubmitted = $mission->mouches()
            ->wherePivot('user_id', $mouche->id)
            ->wherePivotNotNull('submitted_at')
            ->exists();

        abort_if($alreadySubmitted, 403);

        $validated = $request->validate([
            'ligne_id' => ['nullable', 'exists:lignes,id'],
            'date_observation' => ['required', 'date', 'before_or_equal:today'],
            'ponctualite' => ['required', 'integer', 'min:1', 'max:5'],
            'conduite' => ['required', 'integer', 'min:1', 'max:5'],
            'politesse' => ['required', 'integer', 'min:1', 'max:5'],
            'tenue' => ['required', 'integer', 'min:1', 'max:5'],
            'securite' => ['required', 'integer', 'min:1', 'max:5'],
            'gestion_conflit' => ['nullable', 'integer', 'min:1', 'max:5'],
            'observation' => ['nullable', 'string', 'max:3000'],
        ]);

        RapportMouche::create([
            ...$validated,
            'mission_mouche_id' => $mission->id,
            'mouche_user_id' => $mouche->id,
        ]);

        // Marquer la mouche comme ayant soumis
        $mission->mouches()->updateExistingPivot($mouche->id, ['submitted_at' => now()]);

        // Si toutes les mouches ont soumis → mission complétée
        if ($mission->fresh()->isComplete()) {
            $mission->update(['status' => MissionMoucheStatus::Completee]);
        }

        return redirect()->route('mouche.dashboard')
            ->with('success', 'Votre rapport a été soumis avec succès.');
    }

    private function authorizeMoucheAccess(MissionMouche $mission, int $moucheId): void
    {
        $assigned = $mission->mouches()->where('users.id', $moucheId)->exists();
        abort_unless($assigned, 403);
    }
}
