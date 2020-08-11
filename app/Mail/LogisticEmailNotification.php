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
        $subject = '[Pikobar] Permohonan Logistik Alkes';
        $text = '';
        $note = '';
        if ($this->status === Applicant::STATUS_REJECTED) {
            $subject = '[Pikobar] Penolakan Permohonan Logistik Alkes';
            $text = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Logistik Alat Kesehatan Pikobar.';
            $text .= '<br/><br/>';
            $text .= 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . ' tidak bisa kami penuhi.';
            $text .= '<br/><br/>';
            $text .= 'Dengan alasan penolakan sebagai berikut:'; 
            $note = $this->agency->applicant->note;
            $note .= '<br/><br/>';
            $note .= 'Mohon maaf atas ketidaknyamanan ini.';
        } else {
            $text = 'Terima kasih anda sudah melakukan permohonan pada aplikasi Logistik Alat Kesehatan Pikobar. Permohonan logistik anda kami TERIMA. Untuk pengecekan permohonan logistik anda, hubungi nomor berikut ini:';
            $note = env('HOTLINE_PIKOBAR');
        }
        return $this->view('email.logisticemailnotification')
                    ->subject($subject)
                    ->with([
                        'applicantName' => $this->agency->applicant->applicant_name,
                        'note' => $note,
                        'agency' => $this->agency->agency_name,
                        'text' => $text,
                        'hotLine' => env('HOTLINE_PIKOBAR')
                    ]);
    }
}
