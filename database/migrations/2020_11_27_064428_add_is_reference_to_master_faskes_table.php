<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsReferenceToMasterFaskesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->tinyInteger('is_reference')->default(0);
        });

        // Updating faskes of reference
        DB::table('master_faskes')->where('id', '<=', 12)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 14)->where('id', '<=', 38)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 38)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 40)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 42)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 45)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 61)->where('id', '<=', 67)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 75)->where('id', '<=', 76)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 85)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 94)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 102)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 113)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 121)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 142)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 150)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 152)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 162)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 164)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 167)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 178)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 189)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 192)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 197)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 198)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 203)->where('id', '<=', 206)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 214)->where('id', '<=', 216)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 218)->where('id', '<=', 220)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '>=', 222)->where('id', '<=', 235)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 247)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 249)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 256)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 262)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 263)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 266)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 267)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 270)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 277)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 281)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 283)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 299)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 300)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 302)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 304)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 305)->update(['is_reference' => 1]);
        DB::table('master_faskes')->where('id', '=', 308)->update(['is_reference' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_faskes', function (Blueprint $table) {
            $table->dropColumn('is_reference');
        });
    }
}
