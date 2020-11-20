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
            $texts = $this->textNotVerified();
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan terhadap permohonan tersebut.';
        } elseif ($this->status === Applicant::STATUS_REJECTED) {
            $subject = '[Pikobar] Penolakan Permohonan Logistik';
            $texts = $this->textRejected();
            $notes[] = $this->agency->applicant->note;
            $notes[] = 'Mohon maaf atas ketidaknyamanan ini.';
        } elseif ($this->status === Applicant::STATUS_VERIFIED) {
            $subject = '[Pikobar] Permohonan Logistik Terverifikasi';
            $texts = $this->textVerified();
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan dan konfirmasi terhadap permohonan tersebut.';
        } else {
            $subject = '[Pikobar] Permohonan Logistik Sudah Realisasi Salur';
            $texts = $this->textOther();
            $notes[] = 'Silahkan anda dapat menghubungi nomor kontak hotline atau email untuk melakukan pengecekan dan konfirmasi terhadap permohonan tersebut.';
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

    public function textNotVerified()
    {
        $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.'; 
        $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->id . ' sudah kami terima.';
        return $texts;
    }
    
    public function textRejected()
    {
        $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.'; 
        $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->id . ' tidak bisa kami penuhi.';
        $texts[] = 'Dengan alasan penolakan sebagai berikut:'; 
        return $texts;
    }

    public function textVerified()
    {
        $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.';
        $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->id . '  sudah dalam status terverifikasi. Selanjutnya kami akan melakukan pengecekan ketersediaan barang pada gudang logistik.';
        return $texts;
    }

    public function textOther()
    {
        $texts[] = 'Terima kasih Anda sudah melakukan permohonan pada Aplikasi Permohonan Logistik Pikobar.';
        $texts[] = 'Melalui surat elektronik ini, kami bermaksud untuk menyampaikan bahwa permohonan logistik dengan kode permohonan #' . $this->agency->id . ' sudah disetujui untuk realisasi salur logistik dan barang sudah siap untuk diambil/ dikirimkan.';
        $texts[] = '';
        $texts[] = 'Jika barang sudah diterima oleh pemohon, silahkan untuk melaporkan penggunaan logistik dengan ketentuan berikut:';
        $texts[] = '';
        $texts[] = '1. Pemohon tanpa adanya mutasi barang dapat langsung menjalankan alur pelaporan sebagai berikut:';
        $texts[] = 'a. Pemohon dapat mengisi formulir penerimaan barang dari Pemdaprov Jabar melalui laman http://bit.ly/LaporPenerimaanLogistik. Pengisian form dilakukan sebanyak 1 kali sejak penerimaan barang dilakukan, dengan batas maksimum pelaporan yaitu 2x24 jam setelah barang diterima.';
        $texts[] = 'b. Pemohon mengisi formulir penggunaan barang dari setiap pengguna melalui laman http://bit.ly/LaporPenggunaanLogistik secara berkala setiap kali ada penggunaan barang. ';
        $texts[] = '';
        $texts[] = '2. Pemohon yang melakukan mutasi barang dapat menjalankan alur pelaporan seperti berikut:';
        $texts[] = 'a. Dinas Kesehatan Kab/Kota dapat mengisi formulir penerimaan barang dari Pemdaprov Jabar melalui laman http://bit.ly/LaporPenerimaanLogistik. Pengisian form dilakukan sebanyak 1 kali sejak penerimaan barang dilakukan, dengan batas maksimum pelaporan yaitu 2x24 jam setelah barang diterima. Dinas Kesehatan Kab/Kota dapat mendistribusikan barang logistik yang telah diterima dari Pemdaprov Jabar ke setiap fasyankes sesuai dengan rencana alokasi masing-masing. dengan batas waktu distribusi selama 4x24 jam.';
        $texts[] = 'b. Fasyankes yang telah menerima barang dari Pemdaprov Jabar melalui Dinas Kesehatan Kab/Kota dapat melaporkan penerimaan barang tersebut melalui laman http://bit.ly/LaporPenerimaanLogistik dengan batas waktu maksimal 2x24 jam sejak barang diterima. Fasyankes diharapkan dapat koordinasi dengan Dinkes Kab/Kota mengenai kode permohonan dan nomor surat pemohon.';
        $texts[] = 'c. Fasyankes yang menerima bantuan logistik dipersilakan untuk mengisi formulir penggunaan barang melalui laman http://bit.ly/LaporPenggunaanLogistik secara berkala setiap kali ada penggunaan barang.';
        $texts[] = '';
        $texts[] = '3. Jika barang yang diterima sudah habis terpakai, maka pelaporan penggunaan logistik dapat dihentikan.';
        $texts[] = '';
        $texts[] = 'Untuk panduan lebih lengkap, dapat dilihat pada link berikut :';
        $texts[] = 'https://bit.ly/PanduanPelaporanLogistik';
        return $texts;
    }
}
