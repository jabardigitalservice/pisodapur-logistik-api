<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaccineMaterialRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaccine_material_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('vaccine_request_id');
            $table->string('product_id', 30);
            $table->integer('quantity');
            $table->integer('unit_id');
            $table->text('description');
            $table->text('usage');
            $table->string('priority', 20)->default('Menengah');
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
        Schema::dropIfExists('vaccine_material_requests');
    }
}
