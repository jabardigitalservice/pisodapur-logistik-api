<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAllocationMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocation_materials', function (Blueprint $table) {
            $table->string('soh_location');
            $table->string('soh_location_name');
            $table->integer('stock_ok');
            $table->integer('stock_nok');
            $table->integer('booked_stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allocation_materials', function (Blueprint $table) {
            $table->dropColumn('soh_location');
            $table->dropColumn('soh_location_name');
            $table->dropColumn('stock_ok');
            $table->dropColumn('stock_nok');
            $table->dropColumn('booked_stock');
        });
    }
}
