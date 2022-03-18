<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineRequestStatusNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_request_status_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('vaccine_request_id')->index();
            $table->string('status', 30)->index();
            $table->integer('vaccine_status_note_id')->index();
            $table->string('vaccine_status_note_name')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('vaccine_request_status_notes');
    }
}
