<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Requests\StoreLogisticRatingRequest;
use App\Http\Controllers\Controller;
use App\LogisticRating;
use Illuminate\Http\Response;
use DB;

class LogisticRatingController extends Controller
{
    public function store(StoreLogisticRatingRequest $request)
    {
        $logisticRating = new LogisticRating;
        $logisticRating->fill($request->validated());
        $logisticRating->created_by = auth()->user()->id ?? null;
        $logisticRating->save();
        return response()->format(Response::HTTP_OK, 'success', ['logistic_rating' => $logisticRating]);
    }
}
