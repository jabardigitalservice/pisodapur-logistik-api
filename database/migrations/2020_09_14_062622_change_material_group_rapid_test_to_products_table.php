<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMaterialGroupRapidTestToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $update = DB::table('products')->where('material_group', 'RAPID TEST')->update(['material_group' => 'RDT KIT']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $update = DB::table('products')->where('material_group', 'RDT KIT')->update(['material_group' => 'RAPID TEST']);
        });
    }
}
