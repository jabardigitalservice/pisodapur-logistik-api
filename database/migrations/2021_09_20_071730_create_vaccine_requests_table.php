<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('agency_id')->index();
            $table->tinyInteger('agency_type_id')->index();
            $table->string('agency_name');
            $table->string('agency_phone_number', 30)->nullable();
            $table->string('agency_location_address')->nullable();
            $table->string('agency_location_village_code', 15)->index();
            $table->string('agency_location_subdistrict_code', 10)->index();
            $table->string('agency_location_district_code', 6)->index();
            $table->string('applicant_fullname');
            $table->string('applicant_position', 50);
            $table->string('applicant_email', 50)->index();
            $table->string('applicant_primary_phone_number', 30)->index();
            $table->string('applicant_secondary_phone_number', 30)->index();
            $table->string('letter_number', 50);
            $table->string('letter_file_url');
            $table->string('applicant_file_url');
            $table->string('status', 20)->default('not_verified');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccine_requests');
    }
}
