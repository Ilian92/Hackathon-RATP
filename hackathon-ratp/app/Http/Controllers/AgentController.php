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
        $user = $request->user()->load(['managers.centreBuses', 'centreBuses']);

        $data = match ($user->role) {
            UserRole::Chauffeur => $this->chauffeurData($user),
            UserRole::Manager => $this->managerData($user),
            UserRole::Com => $this->comData($user),
            UserRole::RH => $this->rhData($user),
            UserRole::Avocat, UserRole::Mouche => [],
        };

        return view('profile', array_merge(['user' => $user], $data));
    }

    /** @return array<string, mixed> */
    private function chauffeurData(User $user): array
    {
        $user->load(['complaints.complaintType', 'complaints.bus', 'gratifications', 'sanctions']);

        $satisfactionStats = $user->satisfactions()->selectRaw('AVG(note) as average, COUNT(*) as total')->first();

        $avgSur5 = ($satisfactionStats?->average ?? 0) / 2;
        $totalAvis = $satisfactionStats?->total ?? 0;

        // Score interne calculé sur toutes les plaintes (métrique interne)
        $allAboutiesCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
        $scoreInterne = round($avgSur5 * 0.7 + (5 - min($allAboutiesCount, 5)) * 0.3, 1);

        // Seules les plaintes traitées par le manager sont visibles par le chauffeur
        $visibleComplaints = $user->complaints->filter(
            fn ($c) => in_array($c->step, [ComplaintStep::RHReview, ComplaintStep::Closed])
        );

        $negativeComplaints = $visibleComplaints->filter(fn ($c) => $c->negative !== false);
        $positiveComplaints = $visibleComplaints->filter(fn ($c) => $c->negative === false);

        $aboutiesCount = $negativeComplaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
        $enCoursCount = $negativeComplaints->filter(fn ($c) => $c->status === ComplaintStatus::EnCours)->count();
        $closCount = $negativeComplaints->filter(fn ($c) => $c->status === ComplaintStatus::Clos)->count();

        return compact('avgSur5', 'totalAvis', 'aboutiesCount', 'enCoursCount', 'closCount', 'scoreInterne', 'negativeComplaints', 'positiveComplaints');
    }

    /** @return array<string, mixed> */
    private function managerData(User $user): array
    {
        $user->load(['chauffeurs.complaints', 'chauffeurs.sanctions']);

        $pendingComplaints = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::ManagerReview)
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        $rhComplaints = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::RHReview)
            ->with(['complaintType', 'bus', 'driver', 'severity'])
            ->latest('incident_time')
            ->get();

        return compact('pendingComplaints', 'rhComplaints');
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
