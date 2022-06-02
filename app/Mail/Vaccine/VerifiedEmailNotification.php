<?php

namespace App\Mail\Vaccine;

use App\Enums\VaccineProductRequestStatusEnum;
use App\Enums\VaccineRequestStatusEnum;
use App\Models\Vaccine\VaccineRequest;
use App\Models\Vaccine\VaccineRequestStatusNote;
use App\VaccineProductRequest;
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
    public $table;
    public $notes;

    public function __construct(VaccineRequest $vaccineRequest, $status)
    {
        $this->vaccineRequest = $vaccineRequest;
        $this->status = $status;
        $this->texts = [];
        $this->notes = [];
        $this->table = [];
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
        switch ($this->status) {
            case VaccineRequestStatusEnum::verified():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Telah Diverifikasi';
                $this->setMessageVerified();
                break;

            case VaccineRequestStatusEnum::verified_with_note():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Diterima Dengan Catatan';
                $this->setMessageVerifiedWithNote();
                break;

            case VaccineRequestStatusEnum::finalized():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Telah Realisasi';
                $this->setMessageFinalized();
                break;

            default:
                # code...
                break;
        }
    }

    public function getContent()
    {
        return $this->markdown('email.vaccine.verifiedEmailNotification')
                    ->subject($this->subject)
                    ->with([
                        'data' => $this->vaccineRequest,
                        'texts' => $this->texts,
                        'notes' => $this->notes,
                        'tables' => $this->table,
                        'from' => config('mail.from.name_vaccine'),
                        'hotLine' => config('app.hotline_vaccine'),
                        'email' => config('mail.from.name_vaccine'),
                    ]);
    }

    public function setMessageVerified()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') <b>telah diverifikasi.</b>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahap Selanjutnya</b>';
        $this->texts[] = 'Permohonan Anda saat ini sedang dalam tahap rekomendasi dan akan masuk ke tahap realisasi salur. Progres tindak lanjut permohonan akan diinfokan melalui email secara berkala.';
        $this->texts[] = '';
        $this->texts[] = '<b>Lacak Permohonan</b>';
        $this->texts[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->texts[] = '';
    }

    public function setMessageVerifiedWithNote()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') telah melalui tahap verifikasi dan <b><i>diterima dengan catatan</i></b>. Anda diharapkan memperbaiki hal-hal berikut.';

        $verifiedNotes = VaccineRequestStatusNote::where('vaccine_request_id', $this->vaccineRequest->id)->get();
        foreach($verifiedNotes as $key => $note) {
            $this->texts[] = ($key + 1) . '. ' . $note->vaccine_status_note_name;
        }

        $this->texts[] = '';
        $this->texts[] = '<b>Proses Perbaikan</b>';
        $this->texts[] = 'Untuk memperbaiki catatan dari kami silahkan berkoordinasi melalui nomor Whatsapp Admin Logistik Vaksin Pikobar. <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahapan Saat Ini</b>';
        $this->texts[] = 'Selagi menunggu perbaikan surat dari Anda, kami tetap memproses permohonan ke tahap rekomendasi. Selanjutnya akan dilakukan proses realisasi salur dan penentuan tanggal rencana kirim. Progres permohonan akan diinfokan melalui email secara berkala.';
        $this->texts[] = '';
        $this->texts[] = '<b>Lacak Permohonan</b>';
        $this->texts[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
    }

    public function setMessageFinalized()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') <b><i>telah direalisasikan.</i></b>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahap Selanjutnya</b>';
        $this->texts[] = 'Permohonan Logistik Vaksin Anda sedang dikemas. Selanjutnya, akan dilakukan pengiriman barang sesuai tanggal rencana kirim.';
        $this->texts[] = '';
        $this->texts[] = '<b>Tanggal Rencana Kirim</b>';
        $this->texts[] = 'Barang akan dikirim pada tanggal <b><i>' . date('d-m-Y', strtotime($this->vaccineRequest->delivery_plan_date)) . '</i></b>. Mohon bersiap pada tanggal tersebut.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Barang</b>';

        $this->table = VaccineProductRequest::with('vaccineProduct')
            ->where('vaccine_request_id', $this->vaccineRequest->id)
            ->get();

        $this->texts[] = '';

        $this->notes[] = '<b>Lacak Permohonan</b>';
        $this->notes[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->notes[] = '';
    }
}
