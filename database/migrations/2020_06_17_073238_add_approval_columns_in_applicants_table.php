<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalColumnsInApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('approval_status')->after('verification_status')->nullable();
            $table->longText('approval_note')->after('approval_status')->nullable();
            $table->string('stock_checking_status')->after('approval_note')->nullable();
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
            $table->dropColumn('approval_status');
            $table->dropColumn('approval_note');
            $table->dropColumn('stock_checking_status');
        });
    }
}
