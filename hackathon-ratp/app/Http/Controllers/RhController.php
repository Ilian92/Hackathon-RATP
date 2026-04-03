<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Gratification;
use App\Models\Sanction;
use App\Models\User;
use App\Notifications\ComplaintClosedNotification;
use App\Notifications\DriverComplaintClosedNotification;
use App\Notifications\GratificationNotification;
use App\Notifications\SanctionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RhController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $tab = $request->string('tab')->toString() ?: 'available';

        $allowedSorts = ['incident_time', 'severity', 'type', 'driver', 'bus'];
        $sort = in_array($request->string('sort')->toString(), $allowedSorts) ? $request->string('sort')->toString() : 'incident_time';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $typeId = $request->integer('type') ?: null;
        $severityFilter = $request->filled('severity') && in_array($request->integer('severity'), [0, 1, 2, 3, 4]) ? $request->integer('severity') : null;
        $driverFilter = $request->integer('driver_id') ?: null;

        $query = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent', 'rhAgent'])
            ->select('complaints.*')
            ->when($tab === 'available', fn ($q) => $q->where('complaints.step', ComplaintStep::RHReview)->whereNull('complaints.rh_user_id'))
            ->when($tab === 'mine', fn ($q) => $q->where('complaints.step', ComplaintStep::RHReview)->where('complaints.rh_user_id', $userId))
            ->when($tab === 'closed', fn ($q) => $q->where('complaints.rh_user_id', $userId)->where('complaints.step', ComplaintStep::Closed))
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
        $drivers = User::where('role', UserRole::Chauffeur)->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        $counts = [
            'available' => Complaint::where('step', ComplaintStep::RHReview)->whereNull('rh_user_id')->count(),
            'mine' => Complaint::where('step', ComplaintStep::RHReview)->where('rh_user_id', $userId)->count(),
            'closed' => Complaint::where('rh_user_id', $userId)->where('step', ComplaintStep::Closed)->count(),
        ];

        return view('rh.complaints.index', compact('complaints', 'complaintTypes', 'drivers', 'counts', 'tab', 'typeId', 'sort', 'direction', 'severityFilter', 'driverFilter'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver', 'client', 'severity.evaluator', 'comAgent', 'rhAgent', 'sanction', 'gratification']);

        $drivers = $complaint->user_id === null
            ? User::where('role', UserRole::Chauffeur)->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
            : collect();

        return view('rh.complaints.show', compact('complaint', 'drivers'));
    }

    public function identifyDriver(Complaint $complaint, Request $request): RedirectResponse
    {
        abort_unless($complaint->rh_user_id === $request->user()->id, 403);
        abort_if($complaint->user_id !== null, 422);

        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $driver = User::where('id', $validated['driver_id'])
            ->where('role', UserRole::Chauffeur)
            ->firstOrFail();

        $complaint->update(['user_id' => $driver->id]);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Chauffeur identifié et associé au dossier.');
    }

    public function sanction(Complaint $complaint, Request $request): RedirectResponse
    {
        abort_unless($complaint->rh_user_id === $request->user()->id, 403);

        if ($complaint->step !== ComplaintStep::RHReview || $complaint->sanction !== null) {
            return back()->with('error', 'Action non disponible pour ce dossier.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $sanction = Sanction::create([
            'user_id' => $complaint->user_id,
            'complaint_id' => $complaint->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'sanctioned_at' => now()->toDateString(),
        ]);

        $complaint->update([
            'step' => ComplaintStep::Closed,
            'status' => ComplaintStatus::Abouti,
        ]);

        if ($complaint->user_id) {
            $complaint->driver?->notify(new SanctionNotification($sanction));
        }
        if ($complaint->com_user_id) {
            $complaint->comAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }
        if ($complaint->manager_user_id) {
            $complaint->managerAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Sanction enregistrée — dossier clôturé.');
    }

    public function claim(Complaint $complaint, Request $request): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::RHReview || $complaint->rh_user_id !== null) {
            return back()->with('error', 'Ce dossier n\'est plus disponible.');
        }

        $complaint->update(['rh_user_id' => $request->user()->id]);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Dossier pris en charge.');
    }

    public function gratify(Complaint $complaint, Request $request): RedirectResponse
    {
        abort_unless($complaint->rh_user_id === $request->user()->id, 403);

        if ($complaint->step !== ComplaintStep::RHReview || $complaint->negative !== false || $complaint->gratification !== null) {
            return back()->with('error', 'Action non disponible pour ce dossier.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'amount' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $gratification = Gratification::create([
            'user_id' => $complaint->user_id,
            'complaint_id' => $complaint->id,
            'reason' => $validated['reason'],
            'amount' => $validated['amount'] ?? 0,
            'awarded_at' => now()->toDateString(),
        ]);

        $complaint->update([
            'step' => ComplaintStep::Closed,
            'status' => ComplaintStatus::Abouti,
        ]);

        if ($complaint->user_id) {
            $complaint->driver?->notify(new GratificationNotification($gratification));
        }
        if ($complaint->com_user_id) {
            $complaint->comAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }
        if ($complaint->manager_user_id) {
            $complaint->managerAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Gratification enregistrée — dossier clôturé.');
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

        if ($complaint->user_id) {
            $complaint->driver?->notify(new DriverComplaintClosedNotification($complaint->load('bus')));
        }
        if ($complaint->com_user_id) {
            $complaint->comAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }
        if ($complaint->manager_user_id) {
            $complaint->managerAgent?->notify(new ComplaintClosedNotification($complaint->load('bus')));
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Dossier clôturé — plainte aboutie.');
    }
}
