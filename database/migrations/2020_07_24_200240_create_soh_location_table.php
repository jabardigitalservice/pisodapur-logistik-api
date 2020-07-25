<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSohLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soh_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('location_id');
            $table->string('location_name');
            $table->timestamps();
            $table->softDeletes();
        });

        $data[] = [
            'location_id' => 'WHS_PAKUAN_A',
            'location_name' => 'GUDANG PAKUAN A'
        ];
        $data[] = [
            'location_id' => 'WHS_PAKUAN_B',
            'location_name' => 'GUDANG PAKUAN B'
        ];
        $data[] = [
            'location_id' => 'WHS_DINKES',
            'location_name' => 'GUDANG DINKES C'
        ];
        DB::table('soh_locations')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soh_locations');
    }
}
