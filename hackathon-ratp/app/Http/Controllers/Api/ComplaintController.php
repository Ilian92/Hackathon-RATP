<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplaintStep;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Severity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function pending(): JsonResponse
    {
        $complaint = Complaint::with('complaintType')
            ->where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->whereDoesntHave('severity')
            ->oldest()
            ->first();

        if (! $complaint) {
            return response()->json(['message' => 'No pending complaint.'], 404);
        }

        return response()->json([
            'id' => $complaint->id,
            'complaint_type' => $complaint->complaintType?->name,
            'description' => $complaint->description,
        ]);
    }

    public function storeSeverity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idPlainte' => ['required', 'integer', 'exists:complaints,id'],
            'note' => ['required', 'integer', 'between:0,4'],
            'justification' => ['required', 'string', 'max:2000'],
            'negative' => ['nullable', 'boolean'],
        ]);

        $complaint = Complaint::where('id', $validated['idPlainte'])
            ->where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->whereDoesntHave('severity')
            ->firstOrFail();

        Severity::create([
            'complaint_id' => $complaint->id,
            'user_id' => null,
            'level' => $validated['note'],
            'justification' => $validated['justification'],
        ]);

        if (array_key_exists('negative', $validated)) {
            $complaint->update(['negative' => $validated['negative']]);
        }

        return response()->json(['message' => 'Sévérité enregistrée.'], 201);
    }
}
