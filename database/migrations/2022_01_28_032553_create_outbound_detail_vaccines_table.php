<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutboundDetailVaccinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbound_detail_vaccines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('lo_id');
            $table->integer('req_id')->index();
            $table->string('material_id', 35);
            $table->string('material_name');
            $table->string('UoM', 30);
            $table->string('matg_id', 50);
            $table->string('matgsub_id', 50);
            $table->string('donatur_id', 25);
            $table->string('donatur_name');
            $table->string('lo_qty')->nullable();
            $table->integer('lo_plan_qty');
            $table->string('lo_proses_stt')->default('NEW');
            $table->string('lo_approved_time')->nullable();
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
        Schema::dropIfExists('outbound_detail_vaccines');
    }
}
