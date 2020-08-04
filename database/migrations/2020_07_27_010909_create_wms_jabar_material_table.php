<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsJabarMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_jabar_material', function (Blueprint $table) {
            $table->increments('id');
            $table->string('material_id');
            $table->string('uom');
            $table->string('material_name');
            $table->string('matg_id');
            $table->string('matgsub_id');
            $table->string('material_desc');
            $table->integer('donatur_id');
            $table->string('donatur_name');
            $table->timeStamps();
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
        Schema::table('wms_jabar_material', function (Blueprint $table) {
            $table->dropIfExists('wms_jabar_material');
        });
    }
}
