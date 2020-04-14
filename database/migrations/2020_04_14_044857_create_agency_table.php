<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agency_type')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location_district_code')->nullable();
            $table->string('location_subdistrict_code')->nullable();
            $table->string('location_village_code')->nullable();
            $table->string('location_address')->nullable();
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
        Schema::dropIfExists('agency');
    }
}
