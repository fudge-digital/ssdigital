<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PembayaranSiswa;

class ParentVerificationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PembayaranSiswa $pembayaran;

    /**
     * Create a new notification instance.
     */
    public function __construct(PembayaranSiswa $pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Pendaftaran Berhasil')
            ->view('emails.parent-verification-approved', [
                'parent' => $notifiable,
                'pembayaran' => $this->pembayaran,
                'students' => $this->pembayaran->details()->with('siswa.siswaProfile')->get()
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Pendaftaran Anda telah diverifikasi oleh admin.',
            'jenis'   => $this->pembayaran->jenis,
            'pembayaran_id' => $this->pembayaran->id,
            'status'  => 'approve',
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
