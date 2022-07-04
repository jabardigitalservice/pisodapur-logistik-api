<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Http\Resources\VaccineTrackingDetailResource;
use App\Http\Resources\VaccineTrackingResource;
use App\Models\Vaccine\VaccineRequest;
use App\Models\Vaccine\VaccineTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VaccineTrackingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'required',
            'id' => 'nullable',
        ]);

        $limit = $request->input('limit', 5);
        $data = VaccineTracking::tracking($request);
        $resource = VaccineTrackingDetailResource::collection($data->paginate($limit));
        return $resource;
    }

    public function show($id, Request $request)
    {
        $data = VaccineRequest::with([
                'outbounds.outboundDetails'
            ])
            ->findOrFail($id);
        return response()->format(Response::HTTP_OK, 'success', new VaccineTrackingDetailResource($data));
    }
}
