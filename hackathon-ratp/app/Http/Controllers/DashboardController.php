<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use App\Enums\MissionMoucheStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\Gratification;
use App\Models\MissionMouche;
use App\Models\Sanction;
use App\Models\Satisfaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        return match ($user->role) {
            UserRole::Manager => $this->managerDashboard($user),
            UserRole::Com => $this->comDashboard($user),
            UserRole::RH => $this->rhDashboard($user),
            UserRole::Mouche, UserRole::Chauffeur => redirect()->route('profile'),
            default => view('dashboard'),
        };
    }

    private function managerDashboard(User $user): View
    {
        $driverIds = $user->chauffeurs()->pluck('users.id');

        $visibilityScope = fn ($q) => $q->where('complaints.manager_user_id', $user->id)
            ->orWhereIn('complaints.user_id', $driverIds);

        $pendingCount = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::ManagerReview)
            ->count();

        $rhCount = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::RHReview)
            ->count();

        $closedThisMonth = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::Closed)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $teamCount = $driverIds->count();

        $stepBreakdown = Complaint::where($visibilityScope)
            ->selectRaw('
                COUNT(CASE WHEN step = ? THEN 1 END) as manager_review,
                COUNT(CASE WHEN step = ? THEN 1 END) as rh_review,
                COUNT(CASE WHEN step = ? THEN 1 END) as closed
            ', [ComplaintStep::ManagerReview->value, ComplaintStep::RHReview->value, ComplaintStep::Closed->value])
            ->first();

        $natureBreakdown = Complaint::where($visibilityScope)
            ->selectRaw('
                COUNT(CASE WHEN negative = true THEN 1 END) as negative_count,
                COUNT(CASE WHEN negative = false THEN 1 END) as positive_count,
                COUNT(CASE WHEN negative IS NULL THEN 1 END) as unclassified_count
            ')
            ->first();

        $severityDistribution = Complaint::where($visibilityScope)
            ->join('severities', 'severities.complaint_id', '=', 'complaints.id')
            ->selectRaw('severities.level, COUNT(*) as count')
            ->groupBy('severities.level')
            ->orderBy('severities.level')
            ->pluck('count', 'level');

        $teamStats = $user->chauffeurs()
            ->withCount([
                'complaints as total_complaints',
                'complaints as negative_complaints' => fn ($q) => $q->where('negative', true),
                'complaints as positive_complaints' => fn ($q) => $q->where('negative', false),
                'sanctions',
                'gratifications',
            ])
            ->orderBy('users.last_name')
            ->get(['users.id', 'users.first_name', 'users.last_name']);

        // Délai moyen de résolution (incident → clôture) pour les dossiers de l'équipe
        $avgDaysToClose = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::Closed)
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (updated_at - incident_time)) / 86400)::numeric, 1) as avg_days')
            ->value('avg_days');

        // Note de satisfaction moyenne des chauffeurs de l'équipe
        $teamSatisfaction = Satisfaction::whereIn('user_id', $driverIds)
            ->selectRaw('ROUND(AVG(note)::numeric, 1) as avg, COUNT(*) as total')
            ->first();

        // Ancienneté des dossiers en attente de décision manager
        $agingPending = Complaint::where('manager_user_id', $user->id)
            ->where('step', ComplaintStep::ManagerReview)
            ->selectRaw("
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL '3 days' THEN 1 END) as age_0_3,
                COUNT(CASE WHEN created_at < NOW() - INTERVAL '3 days' AND created_at >= NOW() - INTERVAL '7 days' THEN 1 END) as age_4_7,
                COUNT(CASE WHEN created_at < NOW() - INTERVAL '7 days' AND created_at >= NOW() - INTERVAL '14 days' THEN 1 END) as age_8_14,
                COUNT(CASE WHEN created_at < NOW() - INTERVAL '14 days' THEN 1 END) as age_over_14
            ")
            ->first();

        // Volume mensuel des 6 derniers mois (plaintes de mon périmètre)
        $monthlyVolume = collect(range(5, 0))->map(function ($monthsAgo) use ($user) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => ucfirst($date->translatedFormat('M')),
                'count' => Complaint::where('manager_user_id', $user->id)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        })->values();

        $pendingMissionDecisionCount = MissionMouche::where('manager_user_id', $user->id)
            ->where('status', MissionMoucheStatus::Completee)
            ->count();

        return view('manager.dashboard', compact(
            'pendingCount', 'rhCount', 'closedThisMonth', 'teamCount',
            'stepBreakdown', 'natureBreakdown', 'severityDistribution', 'teamStats',
            'avgDaysToClose', 'teamSatisfaction', 'agingPending', 'monthlyVolume',
            'pendingMissionDecisionCount'
        ));
    }

    private function comDashboard(User $user): View
    {
        $availableCount = Complaint::where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->count();

        $myInProgressCount = Complaint::where('step', ComplaintStep::ComReview)
            ->where('com_user_id', $user->id)
            ->count();

        $treatedThisMonth = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $treatedLastMonth = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->count();

        $totalTreated = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->count();

        $severityDistribution = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->join('severities', 'severities.complaint_id', '=', 'complaints.id')
            ->selectRaw('severities.level, COUNT(*) as count')
            ->groupBy('severities.level')
            ->orderBy('severities.level')
            ->pluck('count', 'level');

        $positiveCount = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->where('negative', false)
            ->count();

        $negativeCount = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->where('negative', true)
            ->count();

        $typeBreakdown = Complaint::where('com_user_id', $user->id)
            ->where('step', '!=', ComplaintStep::ComReview)
            ->join('complaint_types', 'complaint_types.id', '=', 'complaints.complaint_type_id')
            ->selectRaw('complaint_types.name, COUNT(*) as count')
            ->groupBy('complaint_types.name')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'name');

        // Délai moyen d'attente en file non réclamée (jours depuis création)
        $avgWaitInQueue = Complaint::where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (NOW() - created_at)) / 86400)::numeric, 1) as avg_wait')
            ->value('avg_wait') ?? 0;

        // Dossiers bloqués dans la file depuis plus de 3 jours (sans prise en charge)
        $stalledCount = Complaint::where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        // Note de satisfaction globale de tous les chauffeurs
        $globalSatisfaction = Satisfaction::selectRaw('ROUND(AVG(note)::numeric, 1) as avg, COUNT(*) as total')
            ->first();

        // Volume de plaintes reçues par mois (6 derniers mois)
        $monthlyVolume = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => ucfirst($date->translatedFormat('M')),
                'count' => Complaint::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        })->values();

        return view('com.dashboard', compact(
            'availableCount', 'myInProgressCount', 'treatedThisMonth', 'treatedLastMonth',
            'totalTreated', 'severityDistribution', 'positiveCount', 'negativeCount', 'typeBreakdown',
            'avgWaitInQueue', 'stalledCount', 'globalSatisfaction', 'monthlyVolume'
        ));
    }

    private function rhDashboard(User $user): View
    {
        $availableCount = Complaint::where('step', ComplaintStep::RHReview)
            ->whereNull('rh_user_id')
            ->count();

        $myInProgressCount = Complaint::where('step', ComplaintStep::RHReview)
            ->where('rh_user_id', $user->id)
            ->count();

        $closedThisMonth = Complaint::where('rh_user_id', $user->id)
            ->where('step', ComplaintStep::Closed)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $sanctionsThisMonth = Sanction::whereMonth('sanctioned_at', now()->month)
            ->whereYear('sanctioned_at', now()->year)
            ->count();

        $gratificationsThisMonth = Gratification::whereMonth('awarded_at', now()->month)
            ->whereYear('awarded_at', now()->year)
            ->count();

        $allClosedPositive = Complaint::where('step', ComplaintStep::Closed)
            ->where('negative', false)
            ->count();

        $allClosedNegative = Complaint::where('step', ComplaintStep::Closed)
            ->where('negative', true)
            ->count();

        $severityDistribution = Complaint::where('rh_user_id', $user->id)
            ->where('step', ComplaintStep::Closed)
            ->join('severities', 'severities.complaint_id', '=', 'complaints.id')
            ->selectRaw('severities.level, COUNT(*) as count')
            ->groupBy('severities.level')
            ->orderBy('severities.level')
            ->pluck('count', 'level');

        $sanctionTypeBreakdown = Sanction::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->pluck('count', 'type');

        $recentSanctions = Sanction::with('user')
            ->latest('sanctioned_at')
            ->limit(5)
            ->get();

        $recentGratifications = Gratification::with('user')
            ->latest('awarded_at')
            ->limit(5)
            ->get();

        // Délai moyen de résolution (incident → clôture) pour mes dossiers RH
        $avgDaysToClose = Complaint::where('rh_user_id', $user->id)
            ->where('step', ComplaintStep::Closed)
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (updated_at - incident_time)) / 86400)::numeric, 1) as avg_days')
            ->value('avg_days');

        // Note de satisfaction globale de tous les chauffeurs
        $globalSatisfaction = Satisfaction::selectRaw('ROUND(AVG(note)::numeric, 1) as avg, COUNT(*) as total')
            ->first();

        // Taux de dossiers clôturés avec action (abouti) vs sans suite
        $allClosedCount = Complaint::where('step', ComplaintStep::Closed)->count();
        $aboutiCount = Complaint::where('step', ComplaintStep::Closed)
            ->where('status', ComplaintStatus::Abouti)
            ->count();
        $aboutiRate = $allClosedCount > 0 ? round($aboutiCount / $allClosedCount * 100) : null;

        // Volume mensuel de plaintes reçues (6 derniers mois, global)
        $monthlyVolume = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => ucfirst($date->translatedFormat('M')),
                'received' => Complaint::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'closed' => Complaint::where('step', ComplaintStep::Closed)
                    ->whereMonth('updated_at', $date->month)
                    ->whereYear('updated_at', $date->year)
                    ->count(),
            ];
        })->values();

        return view('rh.dashboard', compact(
            'availableCount', 'myInProgressCount', 'closedThisMonth', 'sanctionsThisMonth',
            'gratificationsThisMonth', 'allClosedPositive', 'allClosedNegative',
            'severityDistribution', 'sanctionTypeBreakdown', 'recentSanctions', 'recentGratifications',
            'avgDaysToClose', 'globalSatisfaction', 'aboutiRate', 'monthlyVolume', 'allClosedCount', 'aboutiCount'
        ));
    }
}
