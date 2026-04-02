<?php

namespace App\Http\Controllers\Api;

use App\Enums\ComplaintStep;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ComplaintController extends Controller
{
    public function pending(): AnonymousResourceCollection
    {
        $complaints = Complaint::with(['complaintType'])
            ->where('step', ComplaintStep::ComReview)
            ->whereNull('com_user_id')
            ->whereDoesntHave('severity')
            ->orderBy('created_at')
            ->get();

        return ComplaintResource::collection($complaints);
    }
}
