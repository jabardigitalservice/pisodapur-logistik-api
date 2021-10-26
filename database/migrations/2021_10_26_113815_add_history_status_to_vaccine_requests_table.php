<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHistoryStatusToVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->index('agency_name');
            $table->index('applicant_fullname');
            $table->index('letter_number');
            $table->index('is_completed');
            $table->index('is_urgency');
            $table->datetime('verified_at')->nullable();
            $table->integer('verified_by')->index()->nullable();
            $table->datetime('approved_at')->nullable();
            $table->integer('approved_by')->index()->nullable();
            $table->datetime('finalized_at')->nullable();
            $table->integer('finalized_by')->index()->nullable();
            $table->text('rejected_note')->nullable();
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
            $table->dropIndex(['agency_name']);
            $table->dropIndex(['applicant_fullname']);
            $table->dropIndex(['letter_number']);
            $table->dropIndex(['is_completed']);
            $table->dropIndex(['is_urgency']);
            $table->dropColumn('verified_at');
            $table->dropColumn('verified_by');
            $table->dropColumn('approved_at');
            $table->dropColumn('approved_by');
            $table->dropColumn('finalized_at');
            $table->dropColumn('finalized_by');
            $table->dropColumn('rejected_note');
        });
    }
}
