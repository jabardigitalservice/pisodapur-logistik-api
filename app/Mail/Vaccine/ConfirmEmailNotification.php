<?php

namespace App\Mail\Vaccine;

use App\Models\Vaccine\VaccineRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $vaccineRequest;
    protected $status;
    public $subject;
    public $texts;
    public $notes;

    public function __construct(VaccineRequest $vaccineRequest)
    {
        $this->vaccineRequest = $vaccineRequest;
        $this->subject = '[Pikobar] Permohonan Logistik Vaksin Diterima';
        $this->texts = [];
        $this->notes = [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->setSubject();
        return $this->getContent();
    }

    public function setSubject()
    {
        $this->texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Vaksin Pikobar.';
        $this->texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik vaksin dengan kode permohonan #' . $this->vaccineRequest->id . ' sudah kami terima.';
        $this->notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan terhadap permohonan tersebut.';
    }

    public function getContent()
    {
        return $this->view('email.vaccine.confirm-email-notification')
                    ->subject($this->subject)
                    ->with([
                        'data' => $this->vaccineRequest,
                        'texts' => $this->texts,
                        'from' => config('mail.from.name_vaccine'),
                        'hotLine' => config('app.hotline_vaccine')
                    ]);
    }
}
