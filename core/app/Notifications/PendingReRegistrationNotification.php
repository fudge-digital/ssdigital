<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PendingReRegistrationNotification extends Notification
{
    use Queueable;

    protected $pembayaran;

    public function __construct($pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'pending_reregistration',
            'pembayaran_id' => $this->pembayaran->id,
            'parent_id' => $this->pembayaran->user_id,
            'title' => 'Registrasi Ulang Baru',
            'message' => 'Terdapat permintaan registrasi ulang dari orang tua.',
            'route' => route('admin.pembayaran.index'),
            'created_at' => now(),
        ];
    }
}
