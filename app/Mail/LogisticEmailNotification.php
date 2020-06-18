<?php

namespace App\Mail;

use App\Agency;
use App\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogisticEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $agency;
    protected $status;
     
    public function __construct(Agency $agency, $status)
    {
        $this->agency = $agency;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $hotLine = env('HOTLINE_PIKOBAR');
        if ($this->status === Applicant::STATUS_REJECTED) {
            // dd('test');
            $text = 'Terima kasih anda sudah melakukan permohonan pada aplikasi Logistik Alat Kesehatan Pikobar. Akan tetapi, mohon maaf permohonan logistik anda kami TOLAK. Dengan alasan penolakan sebagai berikut:';
            $note = $this->agency->applicant->note;
        } else {
            $text = 'Terima kasih anda sudah melakukan permohonan pada aplikasi Logistik Alat Kesehatan Pikobar. Permohonan logistik anda kami TERIMA. Untuk pengecekan permohonan logistik anda, hubungi nomor berikut ini:';
            $note = $hotLine;
        }
        return $this->view('email.logisticemailnotification')
                    ->subject('Logistik Alat Kesehatan Pikobar')
                    ->with([
                        'applicantName' => $this->agency->applicant->applicant_name,
                        'note' => $note,
                        'agency' => $this->agency->agency_name,
                        'text' => $text,
                        'hotLine' => $hotLine
                    ]);
    }
}
