<?php

namespace App\Http\Controllers;

use App\Models\Ligne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        $nature = $request->get('nature', 'all');
        $severity = $request->get('severity', 'all');

        $startDate = match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => null,
        };

        $centreBusIds = $request->user()->centreBuses()->pluck('centre_buses.id');

        $lines = Ligne::with('arrets')
            ->whereIn('centre_bus_id', $centreBusIds)
            ->get()
            ->map(function (Ligne $ligne) use ($startDate, $nature, $severity) {
                $query = DB::table('complaints')
                    ->join('plannings', function ($join) use ($ligne) {
                        $join->on('plannings.bus_id', '=', 'complaints.bus_id')
                            ->whereRaw('plannings.date = complaints.incident_time::date')
                            ->where('plannings.ligne_id', $ligne->id);
                    });

                if ($startDate !== null) {
                    $query->where('complaints.incident_time', '>=', $startDate);
                }

                if ($nature === 'negative') {
                    $query->where('complaints.negative', true);
                } elseif ($nature === 'positive') {
                    $query->where('complaints.negative', false);
                }

                if ($severity !== 'all') {
                    $query->join('severities', 'severities.complaint_id', '=', 'complaints.id')
                        ->where('severities.level', (int) $severity);
                }

                $complaintCount = $query->distinct('complaints.id')->count('complaints.id');

                return [
                    'id' => $ligne->id,
                    'nom' => $ligne->nom,
                    'complaint_count' => $complaintCount,
                    'stops' => $ligne->arrets->map(fn (object $a) => [
                        'nom' => $a->nom,
                        'lat' => (float) $a->latitude,
                        'lng' => (float) $a->longitude,
                    ])->values(),
                ];
            });

        return response()->json([
            'lines' => $lines->values(),
            'max_complaints' => $lines->max('complaint_count') ?: 1,
        ]);
    }
}
