<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserFilterColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('user_filter')->default(0);
        });

        DB::table('products')->whereIn('name', [
            'Handuk',
            'Lainnya',
            'Kipas Angin',
            'Makanan',
            'Masker Kain',
            'Meja',
            'Obat-obatan Herbal',
            'Obat-obatan Suplemen',
            'Paket BPJS',
            'Paket Sembako Siap Saji',
            'Sanitizer 1L',
            'Sanitizer 20 L',
            'Sanitizer 30 mL',
            'Sanitizer 5 L',
            'Sanitizer 500 mL', 
            'Sanitiizer 60 mL', 
            'Sanitizer 70 mL', 
            'Suplemen',
            'Tissue',
            'Lainnya'
        ])->update(['user_filter' => 9]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('user_filter');
        });
    }
}
