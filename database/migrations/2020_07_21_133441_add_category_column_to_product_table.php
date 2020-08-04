<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryColumnToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema Migration Here
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('ALKES');
        });

        //now the data migration condition for some products
        DB::table('products')->where('name', 'Chamber')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Tisu')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Handuk')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Kipas Angin')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Leaflet')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Meja')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Paket BPJS')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Makanan')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Paket Sembako Siap Saji')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Masker Kain')->update(['category' => 'NON ALKES']);
        DB::table('products')->where('name', 'Suplemen')->update(['category' => 'NON ALKES']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
