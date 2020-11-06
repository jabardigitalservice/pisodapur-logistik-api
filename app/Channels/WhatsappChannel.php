<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class WhatsappChannel extends SmsChannel
{
    protected $awsQueueName = 'wablast-queue';

    protected $loggingName = 'NOTIFICATION_WA_SENT';

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return void
     * @throws \Exception
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsapp($notifiable);

        $this->process($notifiable, $message);
    }
}
