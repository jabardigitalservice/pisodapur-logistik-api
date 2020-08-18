<?php

namespace App\Mail;

use App\Agency;
use App\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApplicationRequestEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $agency;
    protected $status;
     
    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = '[Pikobar] Permohonan Logistik Diterima';
        $texts = [];
        $notes = [];
        
        $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.'; 
        $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . ' sudah kami terima.';
        $texts[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan terhadap permohonan tersebut.';   
        return $this->view('email.applicationrequestemailnotification')
                    ->subject($subject)
                    ->with([
                        'applicantName' => $this->agency->applicant->applicant_name, 
                        'agency' => $this->agency->agency_name,
                        'texts' => $texts,
                        'from' => env('MAIL_FROM_NAME'),
                        'hotLine' => env('HOTLINE_PIKOBAR')
                    ]);
    }
}
