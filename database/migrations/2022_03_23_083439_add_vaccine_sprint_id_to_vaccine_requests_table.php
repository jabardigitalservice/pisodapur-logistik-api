<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVaccineSprintIdToVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->integer('vaccine_sprint_id')->nullable()->index();
            $table->date('delivery_plan_date')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->dropColumn('vaccine_sprint_id');
            $table->dropColumn('delivery_plan_date');
        });
    }
}
