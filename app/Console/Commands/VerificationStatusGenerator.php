<?php

namespace App\Console\Commands;

use App\Enums\Vaccine\VerificationStatusEnum;
use App\Enums\VaccineRequestStatusEnum;
use App\Models\Vaccine\VaccineRequest;
use Illuminate\Console\Command;

class VerificationStatusGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vaccine-logistik:verification-status-generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        VaccineRequest::where('status', VaccineRequestStatusEnum::not_verified())->update(['verification_status' => VerificationStatusEnum::not_verified()]);
        VaccineRequest::where('status', '!=', VaccineRequestStatusEnum::not_verified())->update(['verification_status' => VerificationStatusEnum::verified()]);
        VaccineRequest::where('status', '!=', VaccineRequestStatusEnum::not_verified())->whereHas('vaccineRequestStatusNotes')->update(['verification_status' => VerificationStatusEnum::verified_with_note()]);

        VaccineRequest::where('status', VaccineRequestStatusEnum::not_verified())->update(['status_rank' => 0]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::verified())->update(['status_rank' => 1]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::approved())->update(['status_rank' => 2]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::finalized())->update(['status_rank' => 3]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::integrated())->update(['status_rank' => 4]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::booked())->update(['status_rank' => 5]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::do())->update(['status_rank' => 6]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::intransit())->update(['status_rank' => 7]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::delivered())->update(['status_rank' => 8]);
        VaccineRequest::where('status', VaccineRequestStatusEnum::rejected())->update(['status_rank' => 0, 'verification_status' => VerificationStatusEnum::not_verified()]);

        return 0;
    }
}
