<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogisticRealizationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logistic_realization_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('need_id')->nullable();
            $table->integer('agency_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('unit_id')->nullable();
            $table->dateTime('realization_date')->nullable();
            $table->String('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('logistic_realization_items');
    }
}
