<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IuranRequestNotification extends Notification
{
    use Queueable;

    public $type;
    public $requestData;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $requestData)
    {
        //
        $this->type = $type;
        $this->requestData = $requestData;
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
        switch ($this->type) {
            case 'request_created':
                return [
                    'title' => 'Request Tagihan Baru',
                    'message' => "Parent {$this->requestData->parent->name} mengajukan request tagihan {$this->requestData->months} bulan.",
                    'url' => route('admin.iuran.requests'),
                ];

            case 'request_approved':
                return [
                    'title' => 'Request Tagihan Disetujui',
                    'message' => "Request tagihan {$this->requestData->months} bulan telah disetujui admin.",
                    'url' => route('parent.iuran'),
                ];

            case 'request_rejected':
                return [
                    'title' => 'Request Tagihan Ditolak',
                    'message' => "Request tagihan {$this->requestData->months} bulan ditolak admin.",
                    'url' => route('parent.iuran'),
                ];
        }
    }
}
