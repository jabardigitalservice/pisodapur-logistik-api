<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumntoAgency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->integer('total_covid_patients')->default(0);
            $table->integer('total_isolation_room')->default(0);
            $table->integer('total_bedroom')->default(0);
            $table->integer('total_health_worker')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn('total_covid_patients');
            $table->dropColumn('total_isolation_room');
            $table->dropColumn('total_bedroom');
            $table->dropColumn('total_health_worker');
        });
    }
}
