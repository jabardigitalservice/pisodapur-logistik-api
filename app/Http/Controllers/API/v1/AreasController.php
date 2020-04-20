<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\City;
use App\Subdistrict;
use App\Village;

class AreasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCities(Request $request)
    {
        // id JABAR for default
        $idProvience = 32;

        $query = City::where('kemendagri_provinsi_kode', $idProvience)
                        ->orderBy('kemendagri_kabupaten_nama', 'asc');

        if ($request->query('city_code')) {
            $query->where('kemendagri_kabupaten_kode', '=', $request->query('city_code'));
        }

        return response()->format(200, true, $query->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubDistricts(Request $request)
    {
        $cityCode = '32.01';

        $query = Subdistrict::select('*')
                     ->orderBy('kemendagri_kecamatan_nama', 'asc');

        if ($request->query('city_code')) {
            $query->where('kemendagri_kabupaten_kode', '=', $request->query('city_code'));
        } else {
            $query->where('kemendagri_kabupaten_kode', '=', $cityCode);
        }

        if ($request->query('subdistrict_code')) {
            $query->where('kemendagri_kecamatan_kode', '=', $request->query('subdistrict_code'));
        }

        return response()->format(200, true, $query->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getVillages(Request $request)
    {
        $subdistrictCode = '32.01.01';

        $query = Village::select('*')
                     ->orderBy('kemendagri_desa_nama', 'asc');

        if ($request->query('subdistrict_code')) {
            $query->where('kemendagri_kecamatan_kode', '=', $request->query('subdistrict_code'));
        } else {
            $query->where('kemendagri_kecamatan_kode', '=', $subdistrictCode);
        }

        if ($request->query('village_code')) {
            $query->where('kemendagri_desa_kode', '=', $request->query('village_code'));
        }

        return response()->format(200, true, $query->get());
    }

}
