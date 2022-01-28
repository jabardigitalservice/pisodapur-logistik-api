<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutboundVaccinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbound_vaccines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('req_id')->index();
            $table->string('lo_id', 20)->index();
            $table->date('lo_date');
            $table->string('lo_desc');
            $table->string('lo_cb');
            $table->string('lo_issued_by');
            $table->dateTime('lo_ct');
            $table->string('send_to_id', 20);
            $table->string('send_to_extid', 11)->nullable();
            $table->string('send_to_name');
            $table->text('send_to_address')->nullable();
            $table->string('city_id', 5);
            $table->string('send_to_city');
            $table->string('lo_location', 25);
            $table->string('whs_name');
            $table->string('lo_proses_stt')->default('NEW')->index();
            $table->dateTime('lo_approved_time')->nullable();
            $table->string('lo_app_cb')->nullable();
            $table->string('lo_approved_by')->nullable();
            $table->string('delivery_id', 50)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_transporter', 50)->nullable();
            $table->string('delivery_driver')->nullable();
            $table->string('delivery_fleet', 50)->nullable();
            $table->dateTime('delivery_ct')->nullable();
            $table->string('delivery_cb', 50)->nullable();
            $table->string('delivery_issued_by', 50)->nullable();
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
        Schema::dropIfExists('outbound_vaccines');
    }
}
