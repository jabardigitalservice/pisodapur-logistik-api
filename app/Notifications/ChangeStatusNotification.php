<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChangeStatusNotification extends Notification
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
    public function __construct($param)
    {
        $this->id = $param['id'];
        $this->phase = $param['phase'];
        $this->link = $param['url'] . '/alat-kesehatan/detail/' . $this->id;
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
            case 'surat':
                $message = '*Info Logistik PIKOBAR*
                *Butuh Verifikasi Administrasi, kode : ' . $this->id .'*
                
                Kepada Yth.  
                PIC Surat
                
                Saat ini pada aplikasi permohonan logistik telah masuk surat permohonan logistik baru dengan kode: ' . $this->id .' Mohon ditindaklanjuti untuk melakukan verifikasi administrasi dokumen permohonan tersebut. Surat permohonan tersebut dapat diakses dengan menggunakan aplikasi permohonan logistik. Berikut ini link permohonan yang perlu diverifikasi: ' . $this->link;
                break;
            case 'rekomendasi':
                $message = '*Info Logistik PIKOBAR*
                *Butuh Rekomendasi Salur, kode : ' . $this->id .'*
                
                Kepada Yth.  
                PIC Rekomendasi Salur
                
                Saat ini pada aplikasi permohonan logistik surat permohonan logistik dengan kode: ' . $this->id . ' telah berada pada tahapan rekomendasi salur. Mohon ditindaklanjuti untuk melakukan rekomendasi salur terhadap permohonan tersebut. Adapun untuk melakukan rekomendasi salur dapat diakses dengan menggunakan aplikasi permohonan logistik. Berikut ini link permohonan yang perlu dilakukan rekomendasi salur: ' . $this->link;
                break;
            case 'realisasi':
                $message = '*Info Logistik PIKOBAR*
                *Butuh Realisasi Salur, kode : ' . $this->id .'*
                
                Kepada Yth.  
                PIC Realisasi Salur
                
                Saat ini pada aplikasi permohonan logistik surat permohonan logistik dengan kode: ' . $this->id . ' telah berada pada tahapan realisasi salur. Mohon ditindaklanjuti untuk melakukan realisasi salur terhadap permohonan tersebut. Adapun untuk melakukan realisasi salur dapat diakses dengan menggunakan aplikasi permohonan logistik. Berikut ini link permohonan yang perlu dilakukan realisasi salur: ' . $this->link;
                break;
            default:
                $message = '*Info Logistik PIKOBAR*
                *Butuh Verifikasi Administrasi, kode : ' . $this->id .'*
                
                Kepada Yth.  
                PIC Surat
                
                Saat ini pada aplikasi permohonan logistik telah masuk surat permohonan logistik baru dengan kode: ' . $this->id .' Mohon ditindaklanjuti untuk melakukan verifikasi administrasi dokumen permohonan tersebut. Surat permohonan tersebut dapat diakses dengan menggunakan aplikasi permohonan logistik. Berikut ini link permohonan yang perlu diverifikasi: ' . $this->link;
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
