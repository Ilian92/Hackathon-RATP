<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\StoreSatisfactionRequest;
use App\Models\Bus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Planning;
use App\Models\QrcodeSatisfactionSubmission;
use App\Models\QrcodeScanLimit;
use App\Models\Satisfaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    /**
     * Point d'entrée du QR code (URL fixe collée dans le bus).
     * Génère un token valable 24h et redirige.
     */
    public function show(Request $request): RedirectResponse
    {
        $bus = Bus::where('code', $request->string('bus'))->firstOrFail();

        $token = Str::random(64);

        Cache::put("qr:{$token}", [
            'bus_code' => $bus->code,
            'scanned_at' => now()->toDateTimeString(),
        ], now()->addHours(24));

        return redirect()->route('qrcode.landing', $token);
    }

    /**
     * Page d'accueil après scan (token résolu).
     */
    public function landing(string $token): View
    {
        ['bus_code' => $busCode, 'scanned_at' => $scannedAt] = $this->resolveToken($token);

        $bus = Bus::where('code', $busCode)->firstOrFail();

        return view('qrcode.show', compact('token', 'bus', 'scannedAt'));
    }

    public const SATISFACTION_LIMIT = 3;

    private const SATISFACTION_WINDOW_H = 24;

    public function satisfactionCreate(Request $request, string $token): View
    {
        ['bus_code' => $busCode, 'scanned_at' => $scannedAt] = $this->resolveToken($token);

        $ip = $request->ip();
        $isThrottled = $this->isScanLimitReached($ip) || $this->hasAlreadySubmittedForToken($ip, $token);

        return view('qrcode.satisfaction', compact('token', 'busCode', 'scannedAt', 'isThrottled'));
    }

    public function satisfactionStore(StoreSatisfactionRequest $request, string $token): RedirectResponse
    {
        ['bus_code' => $busCode] = $this->resolveToken($token);

        $ip = $request->ip();

        if ($this->isScanLimitReached($ip) || $this->hasAlreadySubmittedForToken($ip, $token)) {
            return redirect()->route('satisfaction.create', $token)
                ->with('throttled', true);
        }

        $validated = $request->validated();
        $client = Client::firstOrCreate(['email' => $validated['email']]);

        Satisfaction::create([
            'note' => $validated['note'],
            'description' => $validated['description'] ?? null,
            'client_id' => $client->id,
        ]);

        QrcodeSatisfactionSubmission::create(['ip_address' => $ip, 'token' => $token]);
        $this->incrementScanCount($ip);

        return redirect()->route('qrcode.landing', $token)
            ->with('success', 'Merci pour votre retour !');
    }

    private function hasAlreadySubmittedForToken(string $ip, string $token): bool
    {
        return QrcodeSatisfactionSubmission::where('ip_address', $ip)
            ->where('token', $token)
            ->exists();
    }

    private function isScanLimitReached(string $ip): bool
    {
        $record = QrcodeScanLimit::where('ip_address', $ip)->first();

        if (! $record) {
            return false;
        }

        // Fenêtre expirée : la limite ne s'applique plus
        if ($record->window_start->addHours(self::SATISFACTION_WINDOW_H)->isPast()) {
            return false;
        }

        return $record->count >= self::SATISFACTION_LIMIT;
    }

    private function incrementScanCount(string $ip): void
    {
        $record = QrcodeScanLimit::where('ip_address', $ip)->first();

        if (! $record || $record->window_start->addHours(self::SATISFACTION_WINDOW_H)->isPast()) {
            QrcodeScanLimit::updateOrCreate(
                ['ip_address' => $ip],
                ['count' => 1, 'window_start' => now()]
            );

            return;
        }

        $record->increment('count');
    }

    public function complaintCreate(string $token): View
    {
        ['bus_code' => $busCode, 'scanned_at' => $scannedAt] = $this->resolveToken($token);

        $complaintTypes = ComplaintType::all();

        return view('qrcode.complaint', compact('token', 'busCode', 'scannedAt', 'complaintTypes'));
    }

    public function complaintStore(StoreComplaintRequest $request, string $token): RedirectResponse
    {
        ['bus_code' => $busCode, 'scanned_at' => $scannedAt] = $this->resolveToken($token);

        $validated = $request->validated();
        $bus = Bus::where('code', $busCode)->firstOrFail();
        $client = Client::firstOrCreate(['email' => $validated['email']]);

        $scannedAt = Carbon::parse($scannedAt);
        $scanTime = $scannedAt->format('H:i:s');

        $planning = Planning::where('bus_id', $bus->id)
            ->whereDate('date', $scannedAt->toDateString())
            ->where(function ($q) use ($scanTime) {
                $q->whereNull('heure_debut')
                    ->orWhere(function ($q2) use ($scanTime) {
                        $q2->whereTime('heure_debut', '<=', $scanTime)
                            ->whereTime('heure_fin', '>=', $scanTime);
                    });
            })
            ->first();

        Complaint::create([
            'description' => $validated['description'],
            'severity' => null,
            'incident_time' => $scannedAt,
            'bus_id' => $bus->id,
            'complaint_type_id' => $validated['complaint_type_id'],
            'user_id' => $planning?->user_id,
            'client_id' => $client->id,
        ]);

        return redirect()->route('qrcode.landing', $token)
            ->with('success', 'Votre plainte a bien été enregistrée.');
    }

    /**
     * @return array{bus_code: string, scanned_at: string}
     */
    public function expired(): View
    {
        return view('qrcode.expired');
    }

    /**
     * @return array{bus_code: string, scanned_at: string}
     */
    private function resolveToken(string $token): array
    {
        return Cache::get("qr:{$token}") ?? abort(redirect()->route('qrcode.expired'));
    }
}
