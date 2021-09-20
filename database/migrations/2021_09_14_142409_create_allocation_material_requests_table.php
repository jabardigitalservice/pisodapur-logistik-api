<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllocationMaterialRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocation_material_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('allocation_request_id');
            $table->bigInteger('allocation_distribution_id');
            $table->string('matg_id');
            $table->string('material_id');
            $table->string('material_name');
            $table->string('soh_location')->nullable();
            $table->string('soh_location_name')->nullable();
            $table->integer('qty');
            $table->string('UoM');
            $table->date('distribution_plan_date');
            $table->text('additional_information')->nullable();
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
        Schema::dropIfExists('allocation_material_requests');
    }
}
