<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestResult extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhatsappChannel::class, SmsChannel::class];
    }

    public function toSms($notifiable)
    {
        $message  = 'Sampurasun, hasil Tes COVID Anda sudah keluar. ';
        return $message;
    }

    public function toWhatsapp($notifiable)
    {
        $message = 'Sampurasun, hasil Tes COVID-19 Anda sudah keluar. ';
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
