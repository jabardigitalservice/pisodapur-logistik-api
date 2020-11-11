<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhaseToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phase')->nullable();
        });

        DB::table('users')->where('username', '=', 'superadmin')->update(['phase' => 'superadmin']);
        DB::table('users')->where('username', '=', 'admin')->update(['phase' => 'superadmin']);

        DB::table('users')->where('username', '=', 'jabarbergerak')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'gtlog')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'indag')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'gtlogdinkes')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'pimpinan')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'GTLog_dwiagus')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'GTLog_agi')->update(['phase' => 'pimpinan']);
        DB::table('users')->where('username', '=', 'GTLog_disperindag')->update(['phase' => 'pimpinan']);
        
        DB::table('users')->where('username', '=', 'gtloglabkesda')->update(['phase' => 'rekomendasi']);
        DB::table('users')->where('username', '=', 'GTLog_farhan')->update(['phase' => 'rekomendasi']);
        DB::table('users')->where('username', '=', 'GTlog_yatna')->update(['phase' => 'rekomendasi']);
        DB::table('users')->where('username', '=', 'GTLog_marion')->update(['phase' => 'rekomendasi']);
        DB::table('users')->where('username', '=', 'GTLog_elisha')->update(['phase' => 'rekomendasi']);
        
        DB::table('users')->where('username', '=', 'gtlogsalur')->update(['phase' => 'realisasi']);
        DB::table('users')->where('username', '=', 'GTLog_sigit')->update(['phase' => 'realisasi']);
        DB::table('users')->where('username', '=', 'GTLog_heru')->update(['phase' => 'realisasi']);
        DB::table('users')->where('username', '=', 'GTLog_anti')->update(['phase' => 'realisasi']);
        DB::table('users')->where('username', '=', 'GTLog_iyandra')->update(['phase' => 'realisasi']);

        DB::table('users')->where('username', '=', 'gtlogsurat')->update(['phase' => 'surat']);
        DB::table('users')->where('username', '=', 'GTLog_edy')->update(['phase' => 'surat']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
