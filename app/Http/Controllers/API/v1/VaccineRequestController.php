<?php

namespace App\Http\Controllers\API\v1;

use App\VaccineRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class VaccineRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = VaccineRequest::where('type', 'alkes')
        ->with(['vaccineDistributionRequests', 'vaccineMaterialRequests'])
        ->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = VaccineRequest::where('type', 'alkes')
        ->with(['vaccineDistributionRequests', 'vaccineMaterialRequests'])
        ->find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
