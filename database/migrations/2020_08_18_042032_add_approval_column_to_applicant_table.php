<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalColumnToApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dateTime('verified_at')->nullable()->after('verified_by');
            $table->integer('approved_by')->nullable()->after('verified_at');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
        });

        $userId = 70;
        $user = DB::table('users')->where('username', '=', 'gtlog')->first();
        if ($user) {
            $userId = $user->id; 
        }

        //If data has been verified, set verified_by default value to GTLog        
        DB::table('applicants')->where('verification_status', '!=', 'not_verified')->update([
            'verified_by' => $userId,
            'verified_at' => date('Y-m-d H:i:s')
        ]);

        //If data has not yet verify, set verified_by to null   
        DB::table('applicants')->where('verification_status', '=', 'not_verified')->update([
            'verified_by' => null,
            'verified_at' => null
        ]);

        //If data has been approved, set approved_by default value to GTLog        
        DB::table('applicants')->whereNotNull('approval_status')->update([
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('verified_at');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');
        });
    }
}
