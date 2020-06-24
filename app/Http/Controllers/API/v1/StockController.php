<?php

namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Usage;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $param = '';
        
        if ($request->filled('soh_location')) {
            $param = '{"soh_location":"' . $request->input('soh_location') . '"}';
        }

        return Usage::getLogisticStock($param);
    }
}
