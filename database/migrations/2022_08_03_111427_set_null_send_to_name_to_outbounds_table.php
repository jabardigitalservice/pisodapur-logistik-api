<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetNullSendToNameToOutboundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outbounds', function (Blueprint $table) {
            $table->date('lo_date')->nullable()->change();
            $table->string('lo_desc')->nullable()->change();
            $table->string('lo_cb')->nullable()->change();
            $table->string('lo_issued_by')->nullable()->change();
            $table->dateTime('lo_ct')->nullable()->change();
            $table->string('send_to_id', 20)->nullable()->change();
            $table->string('send_to_name')->nullable()->change();
            $table->string('city_id', 5)->nullable()->change();
            $table->string('send_to_city')->nullable()->change();
            $table->string('lo_location', 25)->nullable()->change();
            $table->string('whs_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outbounds', function (Blueprint $table) {
            $table->date('lo_date')->change();
            $table->string('lo_desc')->change();
            $table->string('lo_cb')->change();
            $table->string('lo_issued_by')->change();
            $table->dateTime('lo_ct')->change();
            $table->string('send_to_id', 20)->change();
            $table->string('send_to_name')->change();
            $table->string('city_id', 5)->change();
            $table->string('send_to_city')->change();
            $table->string('lo_location', 25)->change();
            $table->string('whs_name')->change();
        });
    }
}
