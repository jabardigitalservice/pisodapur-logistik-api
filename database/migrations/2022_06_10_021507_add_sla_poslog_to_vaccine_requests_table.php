<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlaPoslogToVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->bigInteger('booked_by')->nullable()->index()->before('delivered_at');
            $table->dateTime('booked_at')->nullable()->before('delivered_at');
            $table->bigInteger('do_by')->nullable()->index()->before('delivered_at');
            $table->dateTime('do_at')->nullable()->before('delivered_at');
            $table->bigInteger('intransit_by')->nullable()->index()->before('delivered_at');
            $table->dateTime('intransit_at')->nullable()->before('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->bigInteger('booked_by')->dropColumn();
            $table->dateTime('booked_at')->dropColumn();
            $table->bigInteger('do_by')->dropColumn();
            $table->dateTime('do_at')->dropColumn();
            $table->bigInteger('intransit_by')->dropColumn();
            $table->dateTime('intransit_at')->dropColumn();
        });
    }
}
