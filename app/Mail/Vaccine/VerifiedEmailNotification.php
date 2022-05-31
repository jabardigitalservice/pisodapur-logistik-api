<?php

namespace App\Mail\Vaccine;

use App\Enums\VaccineRequestStatusEnum;
use App\Models\Vaccine\VaccineRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifiedEmailNotification extends Mailable
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

    public function __construct(VaccineRequest $vaccineRequest, $status)
    {
        $this->vaccineRequest = $vaccineRequest;
        $this->subject = '[INFO] Permohonan Logistik Vaksin - Telah Diverifikasi';
        if ($status == VaccineRequestStatusEnum::verified_with_note()) {
            $this->subject = '[INFO] Permohonan Logistik Vaksin - Diterima Dengan Catatan';
        }
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
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') <b>telah diverifikasi.</b>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahap Selanjutnya</b>';
        $this->texts[] = 'Permohonan Anda saat ini sedang dalam tahap rekomendasi dan akan masuk ke tahap realisasi salur. Progres tindak lanjut permohonan akan diinfokan melalui email secara berkala.';
        $this->texts[] = '';
        $this->texts[] = '<b>Lacak Permohonan</b>';
        $this->texts[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar bit.ly/AdmLogVaksin';
    }

    public function getContent()
    {
        return $this->view('email.vaccine.verifiedEmailNotification')
                    ->subject($this->subject)
                    ->with([
                        'data' => $this->vaccineRequest,
                        'texts' => $this->texts,
                        'from' => config('mail.from.name_vaccine'),
                        'hotLine' => config('app.hotline_vaccine'),
                        'email' => config('mail.from.name_vaccine'),
                    ]);
    }
}
