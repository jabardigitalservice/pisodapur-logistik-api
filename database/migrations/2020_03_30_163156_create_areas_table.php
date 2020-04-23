<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kemendagri_provinsi_kode')->nullable();
            $table->string('kemendagri_provinsi_nama')->nullable();
        });

        Schema::create('districtcities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kemendagri_kabupaten_kode')->nullable();
            $table->string('kemendagri_provinsi_nama')->nullable();
            $table->string('kemendagri_provinsi_kode')->nullable();
            $table->string('dinkes_kota_kode')->nullable();
            $table->string('kemendagri_kabupaten_nama')->nullable();
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kemendagri_desa_nama')->nullable();
            $table->string('kemendagri_kabupaten_kode')->nullable();
            $table->string('kemendagri_provinsi_nama')->nullable();
            $table->string('kemendagri_desa_kode')->nullable();
            $table->string('kemendagri_provinsi_kode')->nullable();
            $table->string('kemendagri_kabupaten_nama')->nullable();
            $table->string('kemendagri_kecamatan_kode')->nullable();
            $table->string('kemendagri_kecamatan_nama')->nullable();
            $table->integer('is_desa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('districtcities');
        Schema::dropIfExists('villages');
    }
}
