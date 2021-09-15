<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllocationMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocation_materials', function (Blueprint $table) {
            $table->id();
            $table->string('matg_id');
            $table->string('material_id');
            $table->string('material_name');
            $table->string('soh_location');
            $table->string('soh_location_name');
            $table->string('UoM');
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
        Schema::dropIfExists('allocation_materials');
    }
}
