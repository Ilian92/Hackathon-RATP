<?php

namespace App\Notifications;

use App\Models\Gratification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GratificationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Gratification $gratification) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'star',
            'color' => 'green',
            'message' => "Félicitations ! Une gratification de {$this->gratification->amount} € a été enregistrée à votre dossier.",
            'complaint_id' => $this->gratification->complaint_id,
        ];
    }
}
