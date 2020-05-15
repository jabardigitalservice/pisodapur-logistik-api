<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsImportedToMasterUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            !Schema::hasColumn('master_unit', 'is_imported')
        ) {
            Schema::table('master_unit', function (Blueprint $table) {
                $table->boolean('is_imported')->after('unit')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_unit', function (Blueprint $table) {
            $table->dropColumn('is_imported');
        });
    }
}
