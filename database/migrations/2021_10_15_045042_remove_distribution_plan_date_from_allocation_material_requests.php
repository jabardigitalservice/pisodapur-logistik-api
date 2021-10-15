<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDistributionPlanDateFromAllocationMaterialRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocation_material_requests', function (Blueprint $table) {
            $table->dropColumn('distribution_plan_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allocation_material_requests', function (Blueprint $table) {
            $table->date('distribution_plan_date')->nullable();
        });
    }
}
