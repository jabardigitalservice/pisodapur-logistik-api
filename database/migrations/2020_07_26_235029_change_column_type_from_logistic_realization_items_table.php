<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeFromLogisticRealizationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_realization_items', function (Blueprint $table) {
            $table->string('product_id')->change(); 
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
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver == 'mysql') {
                DB::statement( 'ALTER TABLE logistic_realization_items CHANGE product_id product_id int NULL AFTER applicant_id' );
            }
        });
    }
}
