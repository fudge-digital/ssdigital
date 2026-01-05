<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ParentPaymentApproved extends Notification
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
            ->subject('Pembayaran Anda Telah Diverifikasi')
            ->greeting('Halo ' . ($notifiable->userProfile->nama_lengkap ?? $notifiable->name))
            ->line('Pembayaran Anda telah diverifikasi oleh admin.')
            ->line('Jumlah: Rp ' . number_format($this->pembayaran->jumlah_total, 0, ',', '.'))
            ->action('Cek Riwayat Pembayaran', route('parent.iuran.index'))
            ->line('Terima kasih!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_approved',
            'pembayaran_id' => $this->pembayaran->id,
            'amount' => $this->pembayaran->jumlah_total,
            'title' => 'Pembayaran Diverifikasi',
            'message' => 'Pembayaran Anda telah diverifikasi oleh admin.',
            'route' => route('parent.iuran.index'),
        ];
    }
}
