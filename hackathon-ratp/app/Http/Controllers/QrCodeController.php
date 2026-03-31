<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\StoreSatisfactionRequest;
use App\Models\Bus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Planning;
use App\Models\Satisfaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function show(Request $request): View
    {
        $bus = Bus::where('code', $request->string('bus'))->firstOrFail();
        $scannedAt = now()->toDateTimeString();

        return view('qrcode.show', compact('bus', 'scannedAt'));
    }

    public function satisfactionCreate(Request $request): View
    {
        $busCode = $request->string('bus');
        $scannedAt = $request->string('scanned_at');

        return view('qrcode.satisfaction', compact('busCode', 'scannedAt'));
    }

    public function satisfactionStore(StoreSatisfactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $client = Client::firstOrCreate(['email' => $validated['email']]);

        Satisfaction::create([
            'note' => $validated['note'],
            'description' => $validated['description'] ?? null,
            'client_id' => $client->id,
        ]);

        return redirect()->route('qrcode.show', ['bus' => $validated['bus_code']])
            ->with('success', 'Merci pour votre retour !');
    }

    public function complaintCreate(Request $request): View
    {
        $busCode = $request->string('bus');
        $scannedAt = $request->string('scanned_at');
        $complaintTypes = ComplaintType::all();

        return view('qrcode.complaint', compact('busCode', 'scannedAt', 'complaintTypes'));
    }

    public function complaintStore(StoreComplaintRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $bus = Bus::where('code', $validated['bus_code'])->firstOrFail();
        $client = Client::firstOrCreate(['email' => $validated['email']]);

        $planning = Planning::where('bus_id', $bus->id)
            ->whereDate('date', Carbon::parse($validated['scanned_at'])->toDateString())
            ->first();

        Complaint::create([
            'description' => $validated['description'],
            'severity' => null,
            'incident_time' => $validated['scanned_at'],
            'bus_id' => $bus->id,
            'complaint_type_id' => $validated['complaint_type_id'],
            'user_id' => $planning?->user_id,
            'client_id' => $client->id,
        ]);

        return redirect()->route('qrcode.show', ['bus' => $validated['bus_code']])
            ->with('success', 'Votre plainte a bien été enregistrée.');
    }
}
