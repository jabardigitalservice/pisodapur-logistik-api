<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerificationStatusToMasterFaskes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            !Schema::hasColumn('master_faskes', 'verification_status')
        ) {
            Schema::table('master_faskes', function (Blueprint $table) {
                $table->string('verification_status')->after('id_tipe_faskes')->nullable();
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
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->dropColumn('verification_status');
        });
    }
}
