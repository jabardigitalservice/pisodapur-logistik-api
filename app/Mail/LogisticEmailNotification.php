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
        $subject = '[Pikobar] Persetujuan Permohonan Logistik';
        $texts = [];
        $notes = [];
        if ($this->status === Applicant::STATUS_NOT_VERIFIED) {
            $subject = '[Pikobar] Permohonan Logistik Diterima';
            $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.'; 
            $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . ' sudah kami terima.';
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan terhadap permohonan tersebut.';
        } elseif ($this->status === Applicant::STATUS_REJECTED) {
            $subject = '[Pikobar] Penolakan Permohonan Logistik';
            $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.'; 
            $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . ' tidak bisa kami penuhi.';
            $texts[] = 'Dengan alasan penolakan sebagai berikut:'; 
            $notes[] = $this->agency->applicant->note;
            $notes[] = 'Mohon maaf atas ketidaknyamanan ini.';
        } elseif ($this->status === Applicant::STATUS_VERIFIED) {
            $subject = '[Pikobar] Permohonan Logistik Terverifikasi';
            $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.';
            $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . '  sudah dalam status terverifikasi. Selanjutnya kami akan melakukan pengecekan ketersediaan barang pada gudang logistik.';
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan dan konfirmasi terhadap permohonan tersebut.';
        } else {
            $subject = '[Pikobar] Persetujuan Permohonan Logistik';
            $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.';
            $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->applicant->id . ' sudah kami setujui.';
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan konfirmasi dan pengecekan terhadap permohonan tersebut.';
        }
        return $this->view('email.logisticemailnotification')
                    ->subject($subject)
                    ->with([
                        'applicantName' => $this->agency->applicant->applicant_name,
                        'notes' => $notes,
                        'agency' => $this->agency->agency_name,
                        'texts' => $texts,
                        'from' => env('MAIL_FROM_NAME'),
                        'hotLine' => env('HOTLINE_PIKOBAR')
                    ]);
    }
}
