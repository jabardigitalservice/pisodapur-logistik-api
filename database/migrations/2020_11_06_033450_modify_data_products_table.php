<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDataProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('poslog_products')->truncate();
        DB::table('products')->where('api', '=', 'DASHBOARD_PIKOBAR_API_BASE_URL')->update([
            'api' => 'WMS_JABAR_BASE_URL',
            'material_group' => 'SWAB KIT'
        ]);
        DB::table('soh_locations')->updateOrInsert(
            ['location_id' => 'WHS_D_LABKESDA'],
            ['location_name' => 'GUDANG D - LABKES']
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
