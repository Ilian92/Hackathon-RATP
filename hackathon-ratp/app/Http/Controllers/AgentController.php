<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function profile(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->load([
            'complaints.complaintType',
            'complaints.bus',
            'gratifications',
            'sanctions',
            'managers',
        ]);

        $satisfactionStats = $user->satisfactions()->selectRaw('AVG(note) as average, COUNT(*) as total')->first();

        $avgSur5 = ($satisfactionStats?->average ?? 0) / 2;
        $totalAvis = $satisfactionStats?->total ?? 0;

        $aboutiesCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Abouti)->count();
        $enCoursCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::EnCours)->count();
        $closCount = $user->complaints->filter(fn ($c) => $c->status === ComplaintStatus::Clos)->count();

        $scoreInterne = round($avgSur5 * 0.7 + (5 - min($aboutiesCount, 5)) * 0.3, 1);

        return view('agent.profile', compact(
            'user',
            'avgSur5',
            'totalAvis',
            'aboutiesCount',
            'enCoursCount',
            'closCount',
            'scoreInterne',
        ));
    }
}
