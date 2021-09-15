<?php

namespace App\Http\Controllers\API\v1;

use App\AllocationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class AllocationRequestController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $data = AllocationRequest::paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function show(Request $request, $id)
    {
        $data = AllocationRequest::find($id);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
