<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplaintStep;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\Severity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function pending(): AnonymousResourceCollection
    {
        $complaints = Complaint::with(['complaintType'])
            ->where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->whereDoesntHave('severity')
            ->orderBy('created_at')
            ->get();

        return ComplaintResource::collection($complaints);
    }

    public function storeSeverity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'idPlainte' => ['required', 'integer', 'exists:complaints,id'],
            'note' => ['required', 'integer', 'between:0,4'],
            'justification' => ['required', 'string', 'max:2000'],
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

        return response()->json(['message' => 'Sévérité enregistrée.'], 201);
    }
}
