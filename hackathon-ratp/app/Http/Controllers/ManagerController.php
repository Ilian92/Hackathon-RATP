<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index(Request $request): View
    {
        $manager = $request->user();
        $managerId = $manager->id;
        $driverIds = $manager->chauffeurs()->pluck('users.id');
        $tab = $request->string('tab')->toString() ?: 'pending';

        $allowedSorts = ['incident_time', 'severity', 'type', 'driver', 'bus'];
        $sort = in_array($request->string('sort')->toString(), $allowedSorts) ? $request->string('sort')->toString() : 'incident_time';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $typeId = $request->integer('type') ?: null;
        $severityFilter = $request->filled('severity') && in_array($request->integer('severity'), [0, 1, 2, 3, 4]) ? $request->integer('severity') : null;
        $driverFilter = $request->integer('driver_id') ?: null;

        // Visible si assignée à ce manager OU si le chauffeur fait partie de son équipe
        $visibilityScope = fn ($q) => $q->where('complaints.manager_user_id', $managerId)
            ->orWhereIn('complaints.user_id', $driverIds);

        $query = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent', 'managerAgent'])
            ->select('complaints.*')
            ->where($visibilityScope)
            ->when($tab === 'pending', fn ($q) => $q->where('complaints.step', ComplaintStep::ManagerReview))
            ->when($tab === 'rh', fn ($q) => $q->where('complaints.step', ComplaintStep::RHReview))
            ->when($tab === 'closed', fn ($q) => $q->where('complaints.step', ComplaintStep::Closed))
            ->when($typeId, fn ($q) => $q->where('complaints.complaint_type_id', $typeId))
            ->when($driverFilter, fn ($q) => $q->where('complaints.user_id', $driverFilter))
            ->when($severityFilter !== null, fn ($q) => $q->whereHas('severity', fn ($sq) => $sq->where('level', $severityFilter)));

        match ($sort) {
            'severity' => $query->leftJoin('severities', 'severities.complaint_id', '=', 'complaints.id')
                ->orderByRaw("severities.level {$direction} NULLS LAST"),
            'type' => $query->join('complaint_types', 'complaint_types.id', '=', 'complaints.complaint_type_id')
                ->orderBy('complaint_types.name', $direction),
            'driver' => $query->leftJoin('users as driver_users', 'driver_users.id', '=', 'complaints.user_id')
                ->orderBy('driver_users.last_name', $direction),
            'bus' => $query->join('buses', 'buses.id', '=', 'complaints.bus_id')
                ->orderBy('buses.code', $direction),
            default => $query->orderBy('complaints.incident_time', $direction),
        };

        $complaints = $query->paginate(20)->withQueryString();
        $complaintTypes = ComplaintType::orderBy('name')->get();
        $drivers = $manager->chauffeurs()->orderBy('last_name')->orderBy('first_name')->get(['users.id', 'first_name', 'last_name']);

        $counts = [
            'pending' => Complaint::where($visibilityScope)->where('step', ComplaintStep::ManagerReview)->count(),
            'rh' => Complaint::where($visibilityScope)->where('step', ComplaintStep::RHReview)->count(),
            'closed' => Complaint::where($visibilityScope)->where('step', ComplaintStep::Closed)->count(),
        ];

        return view('manager.complaints.index', compact('complaints', 'complaintTypes', 'drivers', 'counts', 'tab', 'typeId', 'sort', 'direction', 'severityFilter', 'driverFilter'));
    }

    public function show(Complaint $complaint, Request $request): View
    {
        $manager = $request->user();
        $isAssignedManager = $complaint->manager_user_id === $manager->id;
        $isResponsibleManager = $complaint->user_id && $manager->chauffeurs()->where('users.id', $complaint->user_id)->exists();

        abort_unless($isAssignedManager || $isResponsibleManager, 403);

        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator', 'comAgent', 'rhAgent', 'managerAgent']);

        return view('manager.complaints.show', compact('complaint', 'isAssignedManager'));
    }

    public function forwardToRh(Complaint $complaint, Request $request): RedirectResponse
    {
        abort_unless($complaint->manager_user_id === $request->user()->id, 403);

        if ($complaint->step !== ComplaintStep::ManagerReview) {
            return back()->with('error', 'Action non disponible pour ce dossier.');
        }

        $complaint->update(['step' => ComplaintStep::RHReview]);

        return redirect()->route('manager.complaints.show', $complaint)
            ->with('success', 'Dossier transmis au service RH.');
    }

    public function showDriver(User $user, Request $request): View
    {
        abort_unless($request->user()->chauffeurs()->where('users.id', $user->id)->exists(), 403);

        $user->load(['complaints.complaintType', 'complaints.bus', 'gratifications', 'sanctions', 'managers']);

        $satisfactionStats = $user->satisfactions()->selectRaw('AVG(note) as average, COUNT(*) as total')->first();
        $avgSur5 = ($satisfactionStats?->average ?? 0) / 2;
        $totalAvis = $satisfactionStats?->total ?? 0;
        $aboutiesCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
        $enCoursCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::EnCours)->count();
        $closCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Clos)->count();
        $scoreInterne = round($avgSur5 * 0.7 + (5 - min($aboutiesCount, 5)) * 0.3, 1);

        return view('manager.drivers.show', compact(
            'user', 'avgSur5', 'totalAvis', 'aboutiesCount', 'enCoursCount', 'closCount', 'scoreInterne'
        ));
    }

    public function close(Complaint $complaint, Request $request): RedirectResponse
    {
        abort_unless($complaint->manager_user_id === $request->user()->id, 403);

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
