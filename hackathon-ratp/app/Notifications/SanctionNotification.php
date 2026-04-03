<?php

namespace App\Notifications;

use App\Models\Sanction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SanctionNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Sanction $sanction) {}

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
            'message' => "Une sanction ({$this->sanction->type}) a été enregistrée à votre dossier.",
            'complaint_id' => $this->sanction->complaint_id,
        ];
    }
}
