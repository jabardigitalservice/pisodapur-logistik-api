<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['vaccine', 'vaccine_support'])->index();
            $table->json('unit');
            $table->string('api')->default('WMS_JABAR_VACCINE_BASE_URL')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaccine_products');
    }
}
