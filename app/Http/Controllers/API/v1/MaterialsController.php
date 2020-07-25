<?php

namespace App\Http\Controllers\API\v1;

use App\Material;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;
use App\SohLocation;

class MaterialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $sohLocation = SohLocation::all();
        foreach ($sohLocation as $val) {
            $materials = Usage::getLogisticStockByLocation($val['location_id']);
            foreach ($materials as $material) {
                if (!isset($data[$material->material_id])) {
                    if ($material->stock_ok > 0) {
                        $data[$material->material_id] = [
                            'material_id' => $material->material_id,
                            'material_name' => $material->material_name,
                            'matg_id' => $material->matg_id,
                            'UoM' => $material->UoM,
                            'stock_ok' => $material->stock_ok
                        ];
                    } 
                } else { 
                    $data[$material->material_id]['stock_ok'] += $material->stock_ok;
                }
            }
        }

        // Finalisasi data yang akan dilempar
        $dataFinal = [];
        foreach ($data as $material_id => $material) {
            $dataFinal[] = $material;
        }
        $data = $dataFinal;
        return response()->format(200, 'success', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Material  $Material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $Material)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Material  $Material
     * @return \Illuminate\Http\Response
     */
    public function edit(Material $Material)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Material  $Material
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $Material)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Material  $Material
     * @return \Illuminate\Http\Response
     */
    public function destroy(Material $Material)
    {
        $data = [];
        return response()->format(200, 'success', $data);
    }
}
