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
