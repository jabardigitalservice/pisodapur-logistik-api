<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNameFromAllocationMaterialRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocation_material_requests', function (Blueprint $table) {
            $table->renameColumn('allocation_distribution_id', 'allocation_distribution_request_id')->change();
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
            $table->renameColumn('allocation_distribution_request_id', 'allocation_distribution_id')->change();
        });
    }
}
