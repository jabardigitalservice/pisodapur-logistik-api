<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApiToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('api')->default('WMS_JABAR_BASE_URL');
        });

        DB::table('products')->where('material_group', '=', 'REAGENT')->update([
            'material_group' => 'Reagent',
            'api' => 'DASHBOARD_PIKOBAR_API_BASE_URL'
        ]);

        DB::table('products')->where('name', 'LIKE', '%VTM%')->update([
            'api' => 'DASHBOARD_PIKOBAR_API_BASE_URL'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('api');
        });
    }
}
