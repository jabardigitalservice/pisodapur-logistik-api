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

class PoslogEmailNotification extends Mailable
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
            case VaccineRequestStatusEnum::booked():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Telah Dipacking';
                $this->setMessageBooked();
                break;

            case VaccineRequestStatusEnum::do():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Telah Dikemas';
                $this->setMessageDo();
                break;

            case VaccineRequestStatusEnum::intransit():
                $this->subject = '[INFO] Permohonan Logistik Vaksin - Paket Dalam Perjalanan';
                $this->setMessageIntransit();
                break;
        }
    }

    public function getContent()
    {
        return $this->markdown('email.vaccine.verified-email-notification')
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

    public function setMessageBooked()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') <b><i>telah dipacking.</i></b>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahapan Selanjutnya</b>';
        $this->texts[] = 'Permohonan Logistik Vaksin Anda sedang dikemas untuk dimasukkan ke dalam kendaraan. Selanjutnya, akan dilakukan pengiriman barang sesuai tanggal rencana kirim.';
        $this->texts[] = '';
        $this->texts[] = '<b>Tanggal Rencana Kirim</b>';
        $this->texts[] = 'Barang akan dikirim pada tanggal <b><i>' . date('d-m-Y', strtotime($this->vaccineRequest->delivery_plan_date)) . '</i></b>. Mohon bersiap pada tanggal tersebut.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Barang</b>';

        $this->table = VaccineProductRequest::with('vaccineProduct')
            ->where('vaccine_request_id', $this->vaccineRequest->id)
            ->get();

        foreach ($this->table as $key => $val) {
            $this->table[$key]->finalized_status = $this->getStatusValue($val->finalized_status);
        }

        $this->texts[] = '';

        $this->notes[] = '<b>Lacak Permohonan</b>';
        $this->notes[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->notes[] = '';
    }

    public function setMessageDo()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') <b><i>telah dikemas.</i></b>';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahapan Selanjutnya</b>';
        $this->texts[] = 'Permohonan Logistik Vaksin Anda sedang dimasukkan ke dalam kendaraan. Selanjutnya, akan dilakukan pengiriman barang sesuai tanggal rencana kirim.';
        $this->texts[] = '';
        $this->texts[] = '<b>Tanggal Rencana Kirim</b>';
        $this->texts[] = 'Barang akan dikirim pada tanggal <b><i>' . date('d-m-Y', strtotime($this->vaccineRequest->delivery_plan_date)) . '</i></b>. Mohon bersiap pada tanggal tersebut.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Barang</b>';

        $this->table = VaccineProductRequest::with('vaccineProduct')
            ->where('vaccine_request_id', $this->vaccineRequest->id)
            ->get();

        foreach ($this->table as $key => $val) {
            $this->table[$key]->finalized_status = $this->getStatusValue($val->finalized_status);
        }

        $this->texts[] = '';

        $this->notes[] = '<b>Lacak Permohonan</b>';
        $this->notes[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->notes[] = '';
    }

    public function setMessageIntransit()
    {
        $this->texts[] = '';
        $this->texts[] = 'Terimakasih telah melakukan permohonan pada Aplikasi Vaksin Pikobar. Berikut merupakan ringkasan informasi permohonan Anda.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Permohonan</b>';
        $this->texts[] = 'Melalui email ini, kami mengabarkan bahwa permohonan Logistik vaksin Anda dengan ID permohonan (' . $this->vaccineRequest->id . ') sedang <b><i>dalam perjalanan.</i></b> menuju alamat tujuan.';
        $this->texts[] = '';
        $this->texts[] = '<b>Tahapan Selanjutnya</b>';
        $this->texts[] = 'Selanjutnya, akan dilakukan proses serah terima barang. Mohon bersiap di lokasi kirim.';
        $this->texts[] = '';
        $this->texts[] = '<b>Tanggal Kirim</b>';
        $this->texts[] = 'Barang dikirim pada tanggal <b><i>' . date('d-m-Y', strtotime($this->vaccineRequest->delivery_plan_date)) . '</i></b>. Mohon bersiap pada tanggal tersebut.';
        $this->texts[] = '';
        $this->texts[] = '<b>Status Barang</b>';

        $this->table = VaccineProductRequest::with('vaccineProduct')
            ->where('vaccine_request_id', $this->vaccineRequest->id)
            ->get();

        foreach ($this->table as $key => $val) {
            $this->table[$key]->finalized_status = $this->getStatusValue($val->finalized_status);
        }

        $this->texts[] = '';

        $this->notes[] = '<b>Lacak Permohonan</b>';
        $this->notes[] = 'Lacak permohonan Anda melalui nomor Whatsapp Admin Logistik Vaksin Pikobar <a href="bit.ly/AdmLogVaksin">bit.ly/AdmLogVaksin</a>';
        $this->notes[] = '';
    }

    public function getStatusValue($status = '')
    {
        $result = '';
        switch ($status) {

            case VaccineProductRequestStatusEnum::approved():
                $result = 'Barang Disetujui';
                break;

            case VaccineProductRequestStatusEnum::not_available():
                $result = 'Barang Tidak Tersedia';
                break;

            case VaccineProductRequestStatusEnum::replaced():
                $result = 'Barang Diganti';
                break;

            case VaccineProductRequestStatusEnum::not_yet_fulfilled():
                $result = 'Barang Belum Bisa Dipenuhi';
                break;

            case VaccineProductRequestStatusEnum::urgent():
                $result = 'Barang Penting';
                break;

            case VaccineProductRequestStatusEnum::other():
                $result = 'Barang Lainnya';
                break;
        }
        return $result;
    }
}
