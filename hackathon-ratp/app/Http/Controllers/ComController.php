<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Http\Requests\AssignSeverityRequest;
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
        $tab = $request->string('tab')->toString() ?: 'available';
        $typeId = $request->integer('type') ?: null;

        $userId = $request->user()->id;

        $complaints = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent'])
            ->when($tab === 'available', fn ($q) => $q->where('step', ComplaintStep::ComReview)->whereNull('com_user_id'))
            ->when($tab === 'mine', fn ($q) => $q->where('step', ComplaintStep::ComReview)->where('com_user_id', $userId))
            ->when($tab === 'done', fn ($q) => $q->where('com_user_id', $userId)->where('step', '!=', ComplaintStep::ComReview))
            ->when($typeId, fn ($q) => $q->where('complaint_type_id', $typeId))
            ->latest('incident_time')
            ->paginate(20)
            ->withQueryString();

        $complaintTypes = ComplaintType::orderBy('name')->get();

        $counts = [
            'available' => Complaint::where('step', ComplaintStep::ComReview)->whereNull('com_user_id')->count(),
            'mine' => Complaint::where('step', ComplaintStep::ComReview)->where('com_user_id', $userId)->count(),
            'done' => Complaint::where('com_user_id', $userId)->where('step', '!=', ComplaintStep::ComReview)->count(),
        ];

        return view('com.complaints.index', compact('complaints', 'complaintTypes', 'counts', 'tab', 'typeId'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator', 'comAgent']);

        return view('com.complaints.show', compact('complaint'));
    }

    public function claim(Complaint $complaint, Request $request): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::ComReview || $complaint->com_user_id !== null) {
            return back()->with('error', 'Ce dossier n\'est plus disponible.');
        }

        $complaint->update(['com_user_id' => $request->user()->id]);

        return redirect()->route('com.complaints.show', $complaint)
            ->with('success', 'Dossier pris en charge.');
    }

    public function assignSeverity(AssignSeverityRequest $request, Complaint $complaint): RedirectResponse
    {
        if ($complaint->com_user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();

        Severity::updateOrCreate(
            ['complaint_id' => $complaint->id],
            [
                'user_id' => $request->user()->id,
                'level' => $validated['level'],
                'justification' => $validated['justification'],
            ]
        );

        $level = (int) $validated['level'];

        if ($level === 0) {
            $complaint->update([
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
            ]);
        } elseif ($level <= 2) {
            $complaint->update(['step' => ComplaintStep::ManagerReview]);
        } else {
            $complaint->update(['step' => ComplaintStep::RHReview]);
        }

        return redirect()->route('com.complaints.show', $complaint)
            ->with('success', 'Évaluation enregistrée — dossier transmis.');
    }
}
