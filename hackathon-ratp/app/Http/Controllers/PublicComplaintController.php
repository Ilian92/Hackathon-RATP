<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicComplaintRequest;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Ligne;
use App\Models\Planning;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PublicComplaintController extends Controller
{
    public function index(): View
    {
        $lignes = Ligne::orderBy('nom')->get(['id', 'nom']);
        $complaintTypes = ComplaintType::all();

        return view('welcome', compact('lignes', 'complaintTypes'));
    }

    public function store(StorePublicComplaintRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $date = $validated['date'];
        $time = $validated['heure'].':00';
        $incidentTime = $date.' '.$time;
        $previousDate = Carbon::parse($date)->subDay()->toDateString();

        $planning = Planning::where('ligne_id', $validated['ligne_id'])
            ->where(function ($q) use ($date, $previousDate, $time) {
                // Trajet normal : heure_fin > heure_debut, même journée
                $q->where(function ($q2) use ($date, $time) {
                    $q2->whereDate('date', $date)
                        ->where(function ($q3) use ($time) {
                            $q3->whereNull('heure_debut')
                                ->orWhere(function ($q4) use ($time) {
                                    $q4->whereTime('heure_debut', '<=', $time)
                                        ->whereRaw('heure_fin >= heure_debut')
                                        ->whereTime('heure_fin', '>=', $time);
                                });
                        });
                })
                // Trajet nuit : débuté la veille, heure_fin < heure_debut, on est encore dans le trajet
                    ->orWhere(function ($q2) use ($previousDate, $time) {
                        $q2->whereDate('date', $previousDate)
                            ->whereRaw('heure_fin < heure_debut')
                            ->whereTime('heure_fin', '>=', $time);
                    })
                // Trajet nuit : débuté ce jour, heure_fin < heure_debut, l'incident est après heure_debut
                    ->orWhere(function ($q2) use ($date, $time) {
                        $q2->whereDate('date', $date)
                            ->whereRaw('heure_fin < heure_debut')
                            ->whereTime('heure_debut', '<=', $time);
                    });
            })
            ->first();

        $client = Client::firstOrCreate(['email' => $validated['email']]);

        Complaint::create([
            'description' => $validated['description'],
            'severity' => null,
            'incident_time' => $incidentTime,
            'bus_id' => $planning?->bus_id,
            'complaint_type_id' => $validated['complaint_type_id'],
            'user_id' => $planning?->user_id,
            'client_id' => $client->id,
        ]);

        return redirect()->route('home')
            ->with('success', 'Votre plainte a bien été enregistrée. Merci pour votre retour.');
    }
}
