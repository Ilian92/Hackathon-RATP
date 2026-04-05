<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\AssignSeverityRequest;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\Severity;
use App\Models\User;
use App\Notifications\ComplaintAssignedToManagerNotification;
use App\Notifications\ComplaintSentDirectlyToRHNotification;
use App\Notifications\ComplaintSentToRHNotification;
use App\Notifications\HighSeverityComplaintNotification;
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

        $allowedSorts = ['incident_time', 'severity', 'type', 'driver', 'bus'];
        $sort = in_array($request->string('sort')->toString(), $allowedSorts) ? $request->string('sort')->toString() : 'incident_time';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';
        $severityFilter = $request->filled('severity') && in_array($request->integer('severity'), [0, 1, 2, 3, 4]) ? $request->integer('severity') : null;
        $driverFilter = $request->integer('driver_id') ?: null;
        $importantFilter = $request->boolean('important');

        $query = Complaint::with(['complaintType', 'bus', 'driver', 'severity', 'comAgent'])
            ->select('complaints.*')
            ->when($tab === 'available', fn ($q) => $q->where('complaints.step', ComplaintStep::ComReview)->whereNull('complaints.com_user_id'))
            ->when($tab === 'mine', fn ($q) => $q->where('complaints.step', ComplaintStep::ComReview)->where('complaints.com_user_id', $userId))
            ->when($tab === 'done', fn ($q) => $q->where('complaints.com_user_id', $userId)->where('complaints.step', '!=', ComplaintStep::ComReview))
            ->when($typeId, fn ($q) => $q->where('complaints.complaint_type_id', $typeId))
            ->when($driverFilter, fn ($q) => $q->where('complaints.user_id', $driverFilter))
            ->when($severityFilter !== null, fn ($q) => $q->whereHas('severity', fn ($sq) => $sq->where('level', $severityFilter)))
            ->when($importantFilter, fn ($q) => $q->whereHas('severity', fn ($sq) => $sq->whereIn('level', [3, 4])));

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
        $drivers = collect();

        $importantCount = Complaint::whereHas('severity', fn ($q) => $q->whereIn('level', [3, 4]))
            ->where(function ($q) use ($userId) {
                $q->where(fn ($q2) => $q2->where('step', ComplaintStep::ComReview)->whereNull('com_user_id'))
                    ->orWhere(fn ($q2) => $q2->where('step', ComplaintStep::ComReview)->where('com_user_id', $userId))
                    ->orWhere(fn ($q2) => $q2->where('com_user_id', $userId)->where('step', '!=', ComplaintStep::ComReview));
            })
            ->count();

        $counts = [
            'available' => Complaint::where('step', ComplaintStep::ComReview)->whereNull('com_user_id')->count(),
            'mine' => Complaint::where('step', ComplaintStep::ComReview)->where('com_user_id', $userId)->count(),
            'done' => Complaint::where('com_user_id', $userId)->where('step', '!=', ComplaintStep::ComReview)->count(),
        ];

        return view('com.complaints.index', compact('complaints', 'complaintTypes', 'drivers', 'counts', 'importantCount', 'tab', 'typeId', 'sort', 'direction', 'severityFilter', 'driverFilter', 'importantFilter'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complaintType', 'bus', 'driver.managers.centreBuses', 'client', 'severity.evaluator', 'comAgent', 'gratification']);

        $substituteManagers = collect();

        if (
            $complaint->com_user_id === auth()->id()
            && $complaint->step === ComplaintStep::ComReview
            && $complaint->severity
            && $complaint->severity->level >= 1
            && $complaint->severity->level <= 2
            && $complaint->negative !== false
            && $complaint->driver
        ) {
            $activeManager = $complaint->driver->managers->firstWhere('status', UserStatus::Actif);

            if (! $activeManager) {
                $centreBusIds = $complaint->driver->managers->flatMap->centreBuses->pluck('id')->unique();

                if ($centreBusIds->isNotEmpty()) {
                    $substituteManagers = User::where('role', UserRole::Manager)
                        ->where('status', UserStatus::Actif)
                        ->whereHas('centreBuses', fn ($q) => $q->whereIn('centre_bus_id', $centreBusIds))
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get(['id', 'first_name', 'last_name']);
                }
            }
        }

        return view('com.complaints.show', compact('complaint', 'substituteManagers'));
    }

    private function notifyRhUsers(Complaint $complaint): void
    {
        User::where('role', UserRole::RH)->get()
            ->each(fn (User $rh) => $rh->notify(new ComplaintSentToRHNotification($complaint->load('bus'))));
    }

    private function notifyManagerDirectRH(Complaint $complaint): void
    {
        $complaint->loadMissing('driver.managers');
        $manager = $complaint->driver?->managers->firstWhere('status', UserStatus::Actif);
        if ($manager) {
            $manager->notify(new ComplaintSentDirectlyToRHNotification($complaint->load(['bus', 'severity'])));
        }
    }

    public function claim(Complaint $complaint, Request $request): RedirectResponse
    {
        if ($complaint->step !== ComplaintStep::ComReview || $complaint->com_user_id !== null) {
            return back()->with('error', 'Ce dossier n\'est plus disponible.');
        }

        $complaint->update(['com_user_id' => $request->user()->id]);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Dossier pris en charge.');
    }

    public function assignSeverity(AssignSeverityRequest $request, Complaint $complaint): RedirectResponse
    {
        if ($complaint->com_user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();

        $isNegative = isset($validated['negative']) ? (bool) $validated['negative'] : null;

        Severity::updateOrCreate(
            ['complaint_id' => $complaint->id],
            [
                'user_id' => $request->user()->id,
                'level' => $validated['level'],
                'justification' => $validated['justification'],
            ]
        );

        if ($isNegative !== null) {
            $complaint->update(['negative' => $isNegative]);
        }

        $level = (int) $validated['level'];

        if ($level === 0) {
            $complaint->update([
                'step' => ComplaintStep::Closed,
                'status' => ComplaintStatus::Clos,
            ]);

            return redirect()->route('complaints.show', $complaint)
                ->with('success', 'Évaluation enregistrée — dossier annulé.');
        }

        if ($level >= 3) {
            User::where('role', UserRole::Com)
                ->where('id', '!=', $request->user()->id)
                ->get()
                ->each(fn (User $com) => $com->notify(new HighSeverityComplaintNotification($complaint->load('bus'), $level)));
        }

        if ($isNegative === false) {
            $complaint->update(['step' => ComplaintStep::RHReview]);

            $this->notifyRhUsers($complaint);
            $this->notifyManagerDirectRH($complaint);

            return redirect()->route('complaints.show', $complaint)
                ->with('success', 'Signalement positif enregistré — dossier transmis au service RH.');
        }

        if ($level >= 3) {
            $complaint->update(['step' => ComplaintStep::RHReview]);

            $this->notifyRhUsers($complaint);
            $this->notifyManagerDirectRH($complaint);

            return redirect()->route('complaints.show', $complaint)
                ->with('success', 'Évaluation enregistrée — dossier transmis au service RH.');
        }

        $complaint->load('driver.managers.centreBuses');
        $activeManager = $complaint->driver?->managers->firstWhere('status', UserStatus::Actif);

        if ($activeManager) {
            $complaint->update([
                'step' => ComplaintStep::ManagerReview,
                'manager_user_id' => $activeManager->id,
            ]);

            $activeManager->notify(new ComplaintAssignedToManagerNotification($complaint->load('bus')));

            return redirect()->route('complaints.show', $complaint)
                ->with('success', 'Évaluation enregistrée — dossier transmis au manager.');
        }

        if ($complaint->driver) {
            $centreBusIds = $complaint->driver->managers->flatMap->centreBuses->pluck('id')->unique();

            $substituteManager = User::where('id', $request->integer('manager_id'))
                ->where('role', UserRole::Manager)
                ->where('status', UserStatus::Actif)
                ->whereHas('centreBuses', fn ($q) => $q->whereIn('centre_bus_id', $centreBusIds))
                ->first();

            if (! $substituteManager) {
                return back()->withErrors(['manager_id' => 'Veuillez sélectionner un manager de remplacement valide.'])->withInput();
            }

            $complaint->update([
                'step' => ComplaintStep::ManagerReview,
                'manager_user_id' => $substituteManager->id,
            ]);

            $substituteManager->notify(new ComplaintAssignedToManagerNotification($complaint->load('bus')));

            return redirect()->route('complaints.show', $complaint)
                ->with('success', 'Évaluation enregistrée — dossier transmis au manager de remplacement.');
        }

        $complaint->update(['step' => ComplaintStep::ManagerReview]);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Évaluation enregistrée — dossier transmis.');
    }
}
