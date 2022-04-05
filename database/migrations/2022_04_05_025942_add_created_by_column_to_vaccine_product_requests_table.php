<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByColumnToVaccineProductRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_product_requests', function (Blueprint $table) {
            $table->integer('product_id')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->string('unit')->nullable()->change();
            $table->integer('created_by')->nullable()->index()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_product_requests', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
}
