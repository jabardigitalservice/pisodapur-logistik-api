<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgencyNameColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('agency_name')->nullable()->after('roles');
        });

        DB::table('users')->where('username', 'like', '%gtlog%')->update(['agency_name' => 'Divisi Logistik']);
        DB::table('users')->where('name', 'like', '%log%')->update(['agency_name' => 'Divisi Logistik']);
        DB::table('users')->where('username', 'like', '%dinkes%')->update(['agency_name' => 'Dinas Kesehatan']);
        DB::table('users')->where('name', 'like', '%Jabar Bergerak%')->update(['agency_name' => 'Jabar Bergerak']);
        DB::table('users')->where('username', 'like', '%indag%')->update(['agency_name' => 'Dinas Perindustrian dan Perdagangan']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('agency_name');
        });
    }
}
