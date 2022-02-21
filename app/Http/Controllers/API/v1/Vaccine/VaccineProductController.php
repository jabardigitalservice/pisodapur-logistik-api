<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Models\Vaccine\VaccineProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaccineProductController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = VaccineProduct::when($request->input('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            })->get();
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
