<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->integer('medical_facility_type_id')->index();
            $table->string('city_id')->index();
            $table->string('district_id')->index();
            $table->string('village_id')->index();
            $table->text('address');
            $table->string('phone');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_facilities');
    }
}
