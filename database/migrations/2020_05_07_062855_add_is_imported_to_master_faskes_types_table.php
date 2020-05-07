<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsImportedToMasterFaskesTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            !Schema::hasColumn('master_faskes_types', 'is_imported')
        ) {
            Schema::table('master_faskes_types', function (Blueprint $table) {
                $table->boolean('is_imported')->after('name')->default(0);
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
        Schema::table('master_faskes_types', function (Blueprint $table) {
            $table->dropColumn('is_imported');
        });
    }
}
