<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Http\Request;

class TestResult extends Notification
{
    use Queueable;

    public $link;
    public $id;
    public $phase;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->id = $request->id;
        $this->phase = $request->phase;
        $this->link = $request->url . '/alat-kesehatan/detail/' . $this->id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WhatsappChannel::class];
    }

    public function toSms($notifiable)
    {
        $message = $this->setMessage();
        return $message;
    }

    public function toWhatsapp($notifiable)
    {
        $message = $this->setMessage();
        return $message;
    }

    public function setMessage()
    {
        $message = '';
        switch ($this->phase) {
            case 'rekomendasi':
                $message = 'Permohonan dengan kode: ' . $this->id . ' sudah dilakukan verifikasi administrasi dan perlu dilakukan rekomendasi salur. Berikut link permohonan yang perlu dilakukan rekomendasi salur ' . $this->link;
                break;
            case 'realisasi':
                $message = 'Permohonan dengan kode: ' . $this->id . ' sudah dilakukan rekomendasi salur dan perlu dilakukan realisasi salur. Berikut link permohonan yang perlu dilakukan realisasi salur ' . $this->link;
                break;
            default:
                $message = 'Permohonan dengan kode: ' . $this->id . ' sudah diterima mohon ditindaklanjuti dengan melakukan verifikasi. Berikut link permohonan yang perlu diverifikasi ' . $this->link;
                break;
        }
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
