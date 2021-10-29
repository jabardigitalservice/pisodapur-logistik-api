<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnAtVaccineRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->renameColumn('agency_location_address', 'agency_address');
            $table->renameColumn('agency_location_village_code', 'agency_village_id');
            $table->renameIndex('vaccine_requests_agency_location_village_code_index', 'vaccine_requests_agency_village_id_index');
            $table->renameColumn('agency_location_subdistrict_code', 'agency_district_id');
            $table->renameIndex('vaccine_requests_agency_location_subdistrict_code_index', 'vaccine_requests_agency_district_id_index');
            $table->renameColumn('agency_location_district_code', 'agency_city_id');
            $table->renameIndex('vaccine_requests_agency_location_district_code_index', 'vaccine_requests_agency_city_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vaccine_requests', function (Blueprint $table) {
            $table->renameColumn('agency_address', 'agency_location_address');
            $table->renameColumn('agency_village_id', 'agency_location_village_code');
            $table->renameIndex('vaccine_requests_agency_village_id_index', 'vaccine_requests_agency_location_village_code_index');
            $table->renameColumn('agency_district_id', 'agency_location_subdistrict_code');
            $table->renameIndex('vaccine_requests_agency_district_id_index', 'vaccine_requests_agency_location_subdistrict_code_index');
            $table->renameColumn('agency_city_id', 'agency_location_district_code');
            $table->renameIndex('vaccine_requests_agency_city_id_index', 'vaccine_requests_agency_location_district_code_index');
        });
    }
}
