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
            $table->integer('agency_id')->index();
            $table->integer('applicant_id')->index();
            $table->string('lo_id', 20)->nullable()->index();
            $table->date('lo_date')->nullable();
            $table->string('lo_desc')->nullable();
            $table->string('lo_cb')->nullable();
            $table->string('lo_issued_by')->nullable();
            $table->dateTime('lo_ct')->nullable();
            $table->string('send_to_id', 20)->nullable();
            $table->string('send_to_name')->nullable();
            $table->text('send_to_address')->nullable();
            $table->string('city_id', 5)->nullable();
            $table->string('send_to_city')->nullable();
            $table->string('lo_location', 25)->nullable();
            $table->string('whs_name')->nullable();
            $table->string('lo_proses_stt')->nullable();
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

        DB::table('outbounds')->insert([
            'agency_id' => 1541,
            'applicant_id' => 1541,
        ]);
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
