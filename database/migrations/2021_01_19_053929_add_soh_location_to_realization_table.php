<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSohLocationToRealizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->string('recommendation_soh_location')->nullable();
            $table->string('recommendation_soh_location_name')->nullable();
            $table->string('final_soh_location')->nullable();
            $table->string('final_soh_location_name')->nullable();
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
            $table->dropColumn('recommendation_soh_location');
            $table->dropColumn('recommendation_soh_location_name');
            $table->dropColumn('final_soh_location');
            $table->dropColumn('final_soh_location_name');
        });
    }
}
