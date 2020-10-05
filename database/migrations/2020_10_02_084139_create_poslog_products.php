<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoslogProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poslog_products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('material_id', 30);
            $table->string('material_name');
            $table->string('soh_location', 30);
            $table->string('soh_location_name', 30);
            $table->string('uom', 30)->nullable();
            $table->string('matg_id', 30);
            $table->bigInteger('stock_ok')->nullable();
            $table->bigInteger('stock_nok')->nullable();
            $table->string('source_data', 30);
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
        Schema::dropIfExists('poslog_products');
    }
}
