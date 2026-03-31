<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Http\Requests\AssignSeverityRequest;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Severity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ComController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;
        $typeId = $request->integer('type') ?: null;

        $complaints = Complaint::with(['complaintType', 'bus', 'driver', 'severity'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($typeId, fn ($q) => $q->where('complaint_type_id', $typeId))
            ->latest('incident_time')
            ->paginate(20)
            ->withQueryString();

        $complaintTypes = ComplaintType::orderBy('name')->get();

        $counts = [
            'all' => Complaint::count(),
            'EnCours' => Complaint::where('status', ComplaintStatus::EnCours)->count(),
            'Clos' => Complaint::where('status', ComplaintStatus::Clos)->count(),
            'Abouti' => Complaint::where('status', ComplaintStatus::Abouti)->count(),
        ];

        return view('com.complaints.index', compact('complaints', 'complaintTypes', 'counts', 'status', 'typeId'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator']);

        return view('com.complaints.show', compact('complaint'));
    }

    public function assignSeverity(AssignSeverityRequest $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validated();

        Severity::updateOrCreate(
            ['complaint_id' => $complaint->id],
            [
                'user_id' => $request->user()->id,
                'level' => $validated['level'],
                'justification' => $validated['justification'],
            ]
        );

        return redirect()->route('com.complaints.show', $complaint)
            ->with('success', 'Niveau de gravité enregistré.');
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update(['status' => $request->validated('status')]);

        return redirect()->route('com.complaints.show', $complaint)
            ->with('success', 'Statut mis à jour.');
    }
}
