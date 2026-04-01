<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index(Request $request): View
    {
        $driverIds = $request->user()->chauffeurs()->pluck('users.id');
        $tab = $request->string('tab')->toString() ?: 'pending';

        $complaints = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent'])
            ->whereIn('user_id', $driverIds)
            ->when($tab === 'pending', fn ($q) => $q->where('step', ComplaintStep::ManagerReview))
            ->when($tab === 'rh', fn ($q) => $q->where('step', ComplaintStep::RHReview))
            ->when($tab === 'closed', fn ($q) => $q->where('step', ComplaintStep::Closed))
            ->latest('incident_time')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => Complaint::whereIn('user_id', $driverIds)->where('step', ComplaintStep::ManagerReview)->count(),
            'rh' => Complaint::whereIn('user_id', $driverIds)->where('step', ComplaintStep::RHReview)->count(),
            'closed' => Complaint::whereIn('user_id', $driverIds)->where('step', ComplaintStep::Closed)->count(),
        ];

        return view('manager.complaints.index', compact('complaints', 'counts', 'tab'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator', 'comAgent', 'rhAgent']);

        return view('manager.complaints.show', compact('complaint'));
    }

    public function forwardToRh(Complaint $complaint): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::ManagerReview) {
            return back()->with('error', 'Action non disponible pour ce dossier.');
        }

        $complaint->update(['step' => ComplaintStep::RHReview]);

        return redirect()->route('manager.complaints.show', $complaint)
            ->with('success', 'Dossier transmis au service RH.');
    }

    public function close(Complaint $complaint): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::ManagerReview) {
            return back()->with('error', 'Action non disponible pour ce dossier.');
        }

        $complaint->update([
            'step' => ComplaintStep::Closed,
            'status' => ComplaintStatus::Clos,
        ]);

        return redirect()->route('manager.complaints.show', $complaint)
            ->with('success', 'Dossier clôturé.');
    }
}
