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
        $this->setDefaultReference();
    }

    public function setDefaultReference()
    {
        $ids = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 
            11, 12, 14, 15, 16, 17, 18, 19, 
            20, 21, 22, 23, 24, 25, 26, 27, 
            28, 29, 30, 31, 32, 33, 34, 35, 
            36, 37, 38, 40, 42, 45, 61, 62, 
            63, 64, 65, 66, 67, 75, 76, 85, 
            94, 102, 113, 121, 142, 150, 152, 162, 164, 167, 
            178, 189, 192, 197, 198, 203, 204, 205, 206, 214, 
            215, 216, 218, 219, 220, 222, 223, 224, 225, 226, 
            227, 228, 229, 230, 231, 232, 233, 234, 235,
            247, 249, 256, 262, 263, 266, 267, 270, 277, 281, 
            283, 299, 300, 302, 304, 305, 308
        ];
        DB::table('master_faskes')->whereIn('id', $ids)->update(['is_reference' => 1]);
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
