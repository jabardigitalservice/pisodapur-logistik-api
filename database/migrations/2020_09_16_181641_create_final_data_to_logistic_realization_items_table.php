<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinalDataToLogisticRealizationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->integer('recommendation_by')->nullable();
            $table->dateTime('recommendation_at')->nullable();
            $table->string('final_product_id', 30)->nullable();
            $table->string('final_product_name')->nullable();
            $table->integer('final_quantity')->nullable();
            $table->string('final_unit', 30)->nullable();
            $table->date('final_date')->nullable();
            $table->string('final_status', 30)->nullable();
            $table->integer('final_unit_id')->nullable();
            $table->integer('final_by')->nullable();
            $table->dateTime('final_at')->nullable();
        });

        DB::table('logistic_realization_items')->whereNotNull('created_by')->update([
            'recommendation_by' => DB::raw('created_by'),
            'recommendation_at' => DB::raw('created_at')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->dropColumn('final_product_id');
            $table->dropColumn('final_product_name');
            $table->dropColumn('final_quantity');
            $table->dropColumn('final_unit');
            $table->dropColumn('final_date');
            $table->dropColumn('final_status');
            $table->dropColumn('final_unit_id');
            $table->dropColumn('final_by');
            $table->dropColumn('final_at');
        });
    }
}
