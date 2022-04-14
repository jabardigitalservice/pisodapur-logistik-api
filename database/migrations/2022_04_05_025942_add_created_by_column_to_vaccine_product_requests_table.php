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
            $table->text('description')->nullable()->change();
            $table->text('recommendation_reason')->nullable()->after('recommendation_status');
            $table->string('recommendation_file_url')->nullable()->after('recommendation_status');
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
            $table->dropColumn('recommendation_reason');
            $table->dropColumn('recommendation_file_url');
        });
    }
}
