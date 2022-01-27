<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgressColumnToVaccineProductRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_product_requests', function (Blueprint $table) {
            $table->string('recommendation_product_id')->nullable()->index();
            $table->string('recommendation_product_name')->nullable();
            $table->integer('recommendation_quantity')->nullable();
            $table->string('recommendation_UoM')->nullable();
            $table->dateTime('recommendation_date')->nullable();
            $table->string('recommendation_status')->nullable();
            $table->integer('recommendation_by')->nullable();
            $table->string('finalized_product_id')->nullable()->index();
            $table->string('finalized_product_name')->nullable();
            $table->integer('finalized_quantity')->nullable();
            $table->string('finalized_UoM')->nullable();
            $table->dateTime('finalized_date')->nullable();
            $table->string('finalized_status')->nullable();
            $table->integer('finalized_by')->nullable();
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
            $table->dropColumn('recommendation_product_id');
            $table->dropColumn('recommendation_product_name');
            $table->dropColumn('recommendation_quantity');
            $table->dropColumn('recommendation_UoM');
            $table->dropColumn('recommendation_date');
            $table->dropColumn('recommendation_status');
            $table->dropColumn('recommendation_by');
            $table->dropColumn('finalized_product_id');
            $table->dropColumn('finalized_product_name');
            $table->dropColumn('finalized_quantity');
            $table->dropColumn('finalized_UoM');
            $table->dropColumn('finalized_date');
            $table->dropColumn('finalized_status');
            $table->dropColumn('finalized_by');
        });
    }
}
