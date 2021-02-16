<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Agency;

class AddCompletenessToAgencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->tinyInteger('completeness')->after('location_address')->default(1);
            $table->tinyInteger('is_reference')->after('completeness')->default(0);
        });

        $updateCompleteness = DB::table('agency')->where('agency_name', '')
            ->orWhereNull('agency_name')
            ->orWhere('location_address', '')
            ->orWhereNull('location_address')
            ->update(['completeness' => 0]);

        $applicants = DB::table('applicants')
            ->select('agency_id')
            ->where('applicant_name', '')
            ->orWhereNull('applicant_name')
            ->orWhere('primary_phone_number', '')
            ->orWhereNull('primary_phone_number')
            ->orWhere('file', '')
            ->orWhereNull('file')->get();

        foreach ($applicants as $applicant) {
            $updateCompleteness = DB::table('agency')
            ->where('id', $applicant->agency_id)
            ->update(['completeness' => 0]);
        }

        $agencyNoLetter = Agency::doesnthave('letter')
            ->update(['completeness' => 0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn('completeness');
            $table->dropColumn('is_reference');
        });
    }
}
