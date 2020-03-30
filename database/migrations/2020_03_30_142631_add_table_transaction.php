<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_tipe')->nullable();
            $table->integer('id_user')->nullable();
            $table->integer('id_category')->nullable();
            $table->string('name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location_address')->nullable();
            $table->string('location_subdistrict_code')->nullable();
            $table->string('location_district_code')->nullable();
            $table->string('location_province_code')->nullable();
            $table->integer('quantity')->default(0);
            $table->dateTime('time')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
           Schema::drop('transactions');
    }
}
