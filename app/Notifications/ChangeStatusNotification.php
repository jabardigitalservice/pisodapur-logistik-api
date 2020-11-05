<?php

namespace App\Notifications;

use App\Channels\WhatsappChannel;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeStatusNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhatsappChannel::class, SmsChannel::class];
    }

    public function toWhatsapp($notifiable)
    {
        $message = 'Permohonan dengan kode: xxxx sudah diterima mohon ditindaklanjuti dengan melakukan verifikasi. Berikut link permohonan yang perlu diverifikasi [link]';
        return $message;
    }

    public function toSms($notifiable)
    {
        $message = 'Permohonan dengan kode: xxxx sudah diterima mohon ditindaklanjuti dengan melakukan verifikasi. Berikut link permohonan yang perlu diverifikasi [link]';
        return $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
