<?php

namespace App\Http\Controllers\API\v1\Vaccine;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest\StoreVaccineRequestRatingRequest;
use App\Models\Vaccine\VaccineRequestRating;
use Illuminate\Http\Response;
use DB;

class VaccineRequestRatingController extends Controller
{
    public function store(StoreVaccineRequestRatingRequest $request)
    {
        $vaccineRequestRating = new VaccineRequestRating;
        $vaccineRequestRating->fill($request->validated());
        $vaccineRequestRating->created_by = auth()->user()->id ?? null;
        $vaccineRequestRating->save();
        return response()->format(Response::HTTP_OK, 'success', ['vaccine_request_rating' => $vaccineRequestRating]);
    }
}
