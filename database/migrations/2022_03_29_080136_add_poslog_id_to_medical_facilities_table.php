<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPoslogIdToMedicalFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medical_facilities', function (Blueprint $table) {
            $table->string('poslog_id')->nullable()->index();
            $table->string('poslog_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medical_facilities', function (Blueprint $table) {
            $table->dropColumn('poslog_id');
            $table->dropColumn('poslog_name');
        });
    }
}
