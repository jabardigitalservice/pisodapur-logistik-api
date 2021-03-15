<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutboundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbounds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('req_id')->index();
            $table->integer('agency_id')->index();
            $table->integer('applicant_id')->index();
            $table->string('lo_id', 20)->index();
            $table->date('lo_date');
            $table->string('lo_desc');
            $table->string('lo_cb');
            $table->string('lo_issued_by');
            $table->dateTime('lo_ct');
            $table->string('send_to_id', 20);
            $table->string('send_to_name');
            $table->text('send_to_address');
            $table->string('city_id', 5);
            $table->string('send_to_city');
            $table->string('lo_location', 25);
            $table->string('whs_name');
            $table->string('lo_proses_stt')->default('NEW');;
            $table->string('lo_approved_time')->nullable();
            $table->string('lo_app_cb')->nullable();
            $table->string('lo_approved_by')->nullable();
            $table->string('delivery_id')->nullable();
            $table->string('delivery_date')->nullable();
            $table->string('delivery_transporter')->nullable();
            $table->string('delivery_driver')->nullable();
            $table->string('delivery_fleet')->nullable();
            $table->string('delivery_ct')->nullable();
            $table->string('delivery_cb')->nullable();
            $table->string('delivery_issued_by')->nullable();
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
        Schema::dropIfExists('outbounds');
    }
}
