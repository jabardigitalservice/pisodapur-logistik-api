<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllocationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number')->index();
            $table->date('letter_date');
            $table->string('type')->default('alkes');
            $table->string('applicant_name');
            $table->string('applicant_position');
            $table->bigInteger('applicant_agency_id')->index();
            $table->string('applicant_agency_name');
            $table->string('letter_url');
            $table->string('status')->default('draft')->index();
            $table->integer('is_integrated')->default(0);
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
        Schema::dropIfExists('allocation_requests');
    }
}
