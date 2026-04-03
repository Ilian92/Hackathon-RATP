<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DriverComplaintClosedNotification extends Notification
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
            'icon' => 'info',
            'color' => 'blue',
            'message' => 'Un signalement vous concernant a été traité et clôturé par votre manager.',
            'complaint_id' => $this->complaint->id,
        ];
    }
}
