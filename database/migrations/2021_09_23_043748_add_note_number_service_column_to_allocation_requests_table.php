<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteNumberServiceColumnToAllocationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocation_requests', function (Blueprint $table) {
            $table->string('note_number_service')->nullable()->after('letter_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allocation_requests', function (Blueprint $table) {
            $table->dropColumn('note_number_service');
        });
    }
}
