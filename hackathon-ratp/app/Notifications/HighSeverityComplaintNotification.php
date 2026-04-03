<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HighSeverityComplaintNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Complaint $complaint, private readonly int $level) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'warning',
            'color' => 'red',
            'message' => "Nouveau dossier de niveau {$this->level} disponible (bus {$this->complaint->bus?->code}) — prise en charge urgente recommandée.",
            'complaint_id' => $this->complaint->id,
        ];
    }
}
