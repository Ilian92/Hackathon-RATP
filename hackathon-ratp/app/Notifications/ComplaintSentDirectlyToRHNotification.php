<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintSentDirectlyToRHNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Complaint $complaint) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $level = $this->complaint->severity?->level;
        $reason = $this->complaint->negative === false
            ? 'signalement positif'
            : "niveau {$level}";

        return [
            'icon' => 'warning',
            'color' => 'amber',
            'message' => "Un dossier concernant votre équipe a été transmis directement au RH ({$reason}, bus {$this->complaint->bus?->code}).",
            'complaint_id' => $this->complaint->id,
        ];
    }
}
