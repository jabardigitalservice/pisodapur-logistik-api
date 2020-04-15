<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aplicants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agency_id');
            $table->string('aplicant_name');
            $table->string('aplicants_office');
            $table->string('file')->nullable();
            $table->string('email');
            $table->string('primary_phone_number');
            $table->string('secondary_phone_number');
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
        Schema::dropIfExists('aplicant');
    }
}
