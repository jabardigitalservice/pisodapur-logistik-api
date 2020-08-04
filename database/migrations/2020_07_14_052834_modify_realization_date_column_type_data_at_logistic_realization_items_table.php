<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRealizationDateColumnTypeDataAtLogisticRealizationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->date('realization_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->dateTime('realization_date')->nullable()->change();
        });
    }
}
