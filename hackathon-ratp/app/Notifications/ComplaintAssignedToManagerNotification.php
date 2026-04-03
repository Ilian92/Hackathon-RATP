<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintAssignedToManagerNotification extends Notification
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
        return [
            'icon' => 'inbox',
            'color' => 'blue',
            'message' => "Un nouveau dossier vous a été assigné (bus {$this->complaint->bus?->code}).",
            'complaint_id' => $this->complaint->id,
        ];
    }
}
