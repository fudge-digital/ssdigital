<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IuranApprovedNotification extends Notification
{
    use Queueable;

    protected $iuran;

    /**
     * Create a new notification instance.
     */
    public function __construct($iuran)
    {
        //
        $this->iuran = $iuran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pembayaran Disetujui',
            'message' => 'Pembayaran iuran untuk ' . ($this->iuran->siswa->siswaProfile->nama_lengkap ?? 'siswa') . ' telah disetujui.',
            'iuran_id' => $this->iuran->id,
        ];
    }
}
