<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubdistrictTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subdistricts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kemendagri_kabupaten_kode')->nullable();
            $table->string('kemendagri_kabupaten_nama')->nullable();
            $table->string('kemendagri_provinsi_kode')->nullable();
            $table->string('kemendagri_provinsi_nama')->nullable();
            $table->string('kemendagri_kecamatan_kode')->nullable();
            $table->string('kemendagri_kecamatan_nama')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subdistrict');
    }
}
