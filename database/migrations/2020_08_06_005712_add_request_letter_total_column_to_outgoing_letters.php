<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestLetterTotalColumnToOutgoingLetters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->integer('request_letter_total')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outgoing_letters', function (Blueprint $table) {
            $table->dropColumn('request_letter_total');
        });
    }
}
