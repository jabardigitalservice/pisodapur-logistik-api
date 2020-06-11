<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNonPublicMasterFaskesType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('master_faskes_types', 'non_public')) {
            Schema::table('master_faskes_types', function (Blueprint $table) {
                $table->boolean('non_public')->after('is_imported')->default(0);
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
            $table->dropColumn('non_public');
        });
    }
}
