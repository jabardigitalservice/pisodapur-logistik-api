<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TokenEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $subject;
    public $texts;
    public $notes;
    public $token;
     
    public function __construct($token)
    {
        $this->subject = '[Pikobar] Kode Verifikasi Pelaporan Penerimaan Logistik';
        $this->texts[] = 'Kode verifikasi digunakan untuk melakukan pelaporan penerimaan logistik Pemdaprov Jawa Barat';
        $this->texts[] = 'Silahkan memasukkan kode verifikasi ini untuk melakukan pengisian form pelaporan penerimaan logistik. Kode verifikasi anda:';
        $this->token = $token;
        $this->notes[] = 'Catatan: Kode verifikasi hanya berlaku selama 24 jam. Jika sudah lebih dari 24 jam verifikasi akan dikirimkan kembali melalui email.';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.tokenemailnotification')
                    ->subject($this->subject)
                    ->with([
                        'texts' => $this->texts,
                        'token' => $this->token,
                        'notes' => $this->notes,
                        'from' => env('MAIL_FROM_NAME'),
                        'hotLine' => env('HOTLINE_PIKOBAR')
                    ]);
    }
}
