<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineProductRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_product_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('vaccine_request_id');
            $table->string('product_id', 30);
            $table->integer('quantity');
            $table->integer('unit_id');
            $table->text('description');
            $table->text('usage');
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
        Schema::dropIfExists('vaccine_product_requests');
    }
}
