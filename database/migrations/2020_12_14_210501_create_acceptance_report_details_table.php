<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcceptanceReportDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acceptance_report_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('acceptance_report_id');
            $table->integer('agency_id');
            $table->integer('logistic_realization_item_id');
            $table->string('product_id', 25);
            $table->string('product_name');
            $table->integer('qty');
            $table->integer('qty_ok')->nullable();
            $table->integer('qty_nok')->nullable();
            $table->string('unit', 25);
            $table->string('status', 25);
            $table->string('quality', 40);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acceptance_report_details');
    }
}
