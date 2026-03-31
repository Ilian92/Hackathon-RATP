<?php

namespace App\Http\Controllers;

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

        return view('agent.profile', compact('user', 'satisfactionStats'));
    }
}
