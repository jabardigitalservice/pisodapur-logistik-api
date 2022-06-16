<?php

namespace App\Console\Commands;

use App\Enums\Vaccine\VerificationStatusEnum;
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
        $vaccineRequest = VaccineRequest::where('status', 'not_verified')->update(['verification_status' => VerificationStatusEnum::not_verified()]);
        $vaccineRequest = VaccineRequest::where('status', '!=', 'not_verified')->update(['verification_status' => VerificationStatusEnum::verified()]);
        $vaccineRequest = VaccineRequest::where('status', '!=', 'not_verified')->whereHas('vaccineRequestStatusNotes')->update(['verification_status' => VerificationStatusEnum::verified_with_note()]);
        return 0;
    }
}
