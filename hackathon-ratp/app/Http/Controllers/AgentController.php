<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function profile(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load('managers');

        $data = match ($user->role) {
            UserRole::Chauffeur => $this->chauffeurData($user),
            UserRole::Manager   => $this->managerData($user),
            UserRole::Com       => $this->comData($user),
            UserRole::RH        => $this->rhData($user),
            UserRole::Avocat    => [],
        };

        return view('profile', array_merge(['user' => $user], $data));
    }

    /** @return array<string, mixed> */
    private function chauffeurData(User $user): array
    {
        $user->load(['complaints.complaintType', 'complaints.bus', 'gratifications', 'sanctions']);

        $satisfactionStats = $user->satisfactions()->selectRaw('AVG(note) as average, COUNT(*) as total')->first();

        $avgSur5       = ($satisfactionStats?->average ?? 0) / 2;
        $totalAvis     = $satisfactionStats?->total ?? 0;
        $aboutiesCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
        $enCoursCount  = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::EnCours)->count();
        $closCount     = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Clos)->count();
        $scoreInterne  = round($avgSur5 * 0.7 + (5 - min($aboutiesCount, 5)) * 0.3, 1);

        return compact('avgSur5', 'totalAvis', 'aboutiesCount', 'enCoursCount', 'closCount', 'scoreInterne');
    }

    /** @return array<string, mixed> */
    private function managerData(User $user): array
    {
        $user->load(['chauffeurs.complaints', 'chauffeurs.sanctions']);

        $pendingComplaints = Complaint::whereIn('user_id', $user->chauffeurs->pluck('id'))
            ->where('step', ComplaintStep::ManagerReview)
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        return compact('pendingComplaints');
    }

    /** @return array<string, mixed> */
    private function comData(User $user): array
    {
        $availableComplaints = Complaint::where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->with(['complaintType', 'bus', 'driver'])
            ->latest('incident_time')
            ->get();

        $myComplaints = Complaint::where('com_user_id', $user->id)
            ->where('step', ComplaintStep::ComReview)
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        return compact('availableComplaints', 'myComplaints');
    }

    /** @return array<string, mixed> */
    private function rhData(User $user): array
    {
        $availableComplaints = Complaint::where('step', ComplaintStep::RHReview)
            ->whereNull('rh_user_id')
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        $myComplaints = Complaint::where('rh_user_id', $user->id)
            ->where('step', ComplaintStep::RHReview)
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        return compact('availableComplaints', 'myComplaints');
    }
}
