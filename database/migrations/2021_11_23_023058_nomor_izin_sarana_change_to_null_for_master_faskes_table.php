<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NomorIzinSaranaChangeToNullForMasterFaskesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->string('nomor_izin_sarana')->nullable()->change();
            $table->string('nama_atasan')->nullable()->change();
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
            $table->string('nomor_izin_sarana')->change();
            $table->string('nama_atasan')->change();
        });
    }
}