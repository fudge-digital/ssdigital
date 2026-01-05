<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ParentReRegistrationApprovedNotification extends Notification
{
    use Queueable;

    protected $pembayaran;

    public function __construct($pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Registrasi Ulang Telah Diverifikasi')
            ->view('emails.parent-reregistration-approved', [
                'parent' => $notifiable,
                'pembayaran' => $this->pembayaran,
                'details' => $this->pembayaran->details()->with('siswa.siswaProfile')->get(),
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reregistration_approved',
            'pembayaran_id' => $this->pembayaran->id,
            'title' => 'Registrasi Ulang Diverifikasi',
            'message' => 'Registrasi ulang Anda telah disetujui oleh admin.',
            'route' => route('parent.dashboard'),
        ];
    }
}
