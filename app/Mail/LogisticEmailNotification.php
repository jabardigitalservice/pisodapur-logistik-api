<?php

namespace App\Mail;

use App\Agency;
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
        // dd($this->agency->agency_name);
        return $this->view('email.logisticemailnotification')
                    ->subject('Logistik Alat Kesehatan Pikobar')
                    ->with([
                        'applicantName' => $this->agency->applicant->applicant_name,
                        'note' => $this->agency->applicant->note,
                        'agency' => $this->agency->agency_name
                    ]);
    }
}
