<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToApplicants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('status')->nullable()->index();
            $table->bigInteger('integrated_by')->nullable()->index();
            $table->timestamp('integrated_at')->nullable();
            $table->bigInteger('booked_by')->nullable()->index();
            $table->timestamp('booked_at')->nullable();
            $table->bigInteger('do_by')->nullable()->index();
            $table->timestamp('do_at')->nullable();
            $table->bigInteger('intransit_by')->nullable()->index();
            $table->timestamp('intransit_at')->nullable();
            $table->bigInteger('delivered_by')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('integrated_by');
            $table->dropColumn('integrated_at');
            $table->dropColumn('booked_by');
            $table->dropColumn('booked_at');
            $table->dropColumn('do_by');
            $table->dropColumn('do_at');
            $table->dropColumn('intransit_by');
            $table->dropColumn('intransit_at');
            $table->dropColumn('delivered_by');
            $table->dropColumn('delivered_at');
        });
    }
}
