<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RhController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $tab = $request->string('tab')->toString() ?: 'available';

        $complaints = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent', 'rhAgent'])
            ->where('step', ComplaintStep::RHReview)
            ->when($tab === 'available', fn ($q) => $q->whereNull('rh_user_id'))
            ->when($tab === 'mine', fn ($q) => $q->where('rh_user_id', $userId))
            ->latest('incident_time')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'available' => Complaint::where('step', ComplaintStep::RHReview)->whereNull('rh_user_id')->count(),
            'mine' => Complaint::where('step', ComplaintStep::RHReview)->where('rh_user_id', $userId)->count(),
            'closed' => Complaint::where('rh_user_id', $userId)->where('step', ComplaintStep::Closed)->count(),
        ];

        return view('rh.complaints.index', compact('complaints', 'counts', 'tab'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator', 'comAgent', 'rhAgent']);

        return view('rh.complaints.show', compact('complaint'));
    }

    public function claim(Complaint $complaint, Request $request): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::RHReview || $complaint->rh_user_id !== null) {
            return back()->with('error', 'Ce dossier n\'est plus disponible.');
        }

        $complaint->update(['rh_user_id' => $request->user()->id]);

        return redirect()->route('rh.complaints.show', $complaint)
            ->with('success', 'Dossier pris en charge.');
    }

    public function close(Complaint $complaint, Request $request): RedirectResponse
    {
        if ($complaint->rh_user_id !== $request->user()->id) {
            abort(403);
        }

        $complaint->update([
            'step' => ComplaintStep::Closed,
            'status' => ComplaintStatus::Abouti,
        ]);

        return redirect()->route('rh.complaints.show', $complaint)
            ->with('success', 'Dossier clôturé — plainte aboutie.');
    }
}
