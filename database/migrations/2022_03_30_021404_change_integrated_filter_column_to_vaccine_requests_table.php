<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIntegratedFilterColumnToVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->dropColumn('is_integrated');
            $table->dropColumn('is_completed');
            $table->dateTime('integrated_at')->nullable();
            $table->integer('integrated_by')->nullable()->index();
            $table->dateTime('delivered_at')->nullable();
            $table->integer('delivered_by')->nullable()->index();
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
            $table->tinyInteger('is_integrated')->default(0);
            $table->tinyInteger('is_completed')->default(0);
            $table->dropColumn('integrated_at');
            $table->dropColumn('integrated_by');
            $table->dropColumn('delivered_at');
            $table->dropColumn('delivered_by');
        });
    }
}
