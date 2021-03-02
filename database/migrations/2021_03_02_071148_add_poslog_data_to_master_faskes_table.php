<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPoslogDataToMasterFaskesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->string('poslog_id')->after('nama_faskes')->nullable();
            $table->string('poslog_name')->after('poslog_id')->nullable();
        });

        //Add Mapping Table Phase 1 & 2
        $filepath = base_path('database/seeds/data/master_faskes_map.sql');
        DB::unprepared(file_get_contents($filepath));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->dropColumn('poslog_id');
            $table->dropColumn('poslog_name');
        });
    }
}
