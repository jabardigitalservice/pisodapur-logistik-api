<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Http\Resources\VaccineTrackingDetailResource;
use App\Http\Resources\VaccineTrackingResource;
use App\Models\Vaccine\VaccineRequest;
use App\Models\Vaccine\VaccineTracking;
use Illuminate\Http\Request;

class VaccineTrackingController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'search' => 'required',
            'id' => 'nullable',
        ]);

        $data = VaccineTracking::tracking($request)->limit(5)->latest()->get();
        $resource = VaccineTrackingResource::collection($data);

        $id = $request->id ?? (count($data) > 0 ? $data[0]->id : null);
        $detail = VaccineRequest::with([
            'outbounds.outboundDetails'
        ])
        ->findOrFail($id);

        $result = [
            'detail' => new VaccineTrackingDetailResource($detail),
            'overview' => $resource,
        ];
        return response()->json($result);
    }
}
