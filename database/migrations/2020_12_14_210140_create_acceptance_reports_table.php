<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceptanceReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceptance_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('agency_id');
            $table->string('fullname');
            $table->string('position');
            $table->string('phone', 25);
            $table->date('date');
            $table->string('officer_fullname');
            $table->string('note')->nullable();
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
        Schema::dropIfExists('acceptance_reports');
    }
}
