<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUnitInProductUnit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_unit', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->integer('unit_id')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_unit', function (Blueprint $table) {
            $table->string('unit');
            $table->dropColumn('unit_id');
        });
    }
}
