<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingRegistrationNotification extends Notification
{
    use Queueable;

    public $pembayaran;

    /**
     * Create a new notification instance.
     */
    public function __construct(PembayaranSiswa $pembayaran)
    {
        //
        $this->pembayaran = $pembayaran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['databse', 'mail'];
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

    public function toDatabase($notifiable)
    {
        $type = $this->pembayaran->jenis; // Daftar_Ulang / Pendaftaran_Baru

        return [
            'title' => $type === 'Daftar_Ulang'
                ? 'Pending Daftar Ulang'
                : 'Pending Pendaftaran Baru',
            'message' => $type === 'Daftar_Ulang'
                ? 'Ada pengajuan Registrasi Ulang siswa yang menunggu verifikasi.'
                : 'Ada pengajuan Pendaftaran Baru yang menunggu verifikasi.',
            'pembayaran_id' => $this->pembayaran->id,
            'type' => $type,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
