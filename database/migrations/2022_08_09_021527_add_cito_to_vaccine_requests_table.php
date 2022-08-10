<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCitoToVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->boolean('is_cito')->default(0)->index();
            $table->dateTime('cito_at')->nullable();
            $table->bigInteger('cito_by')->nullable()->index();
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
            $table->dropColumn('is_cito');
            $table->dropColumn('cito_at');
            $table->dropColumn('cito_by');
        });
    }
}
