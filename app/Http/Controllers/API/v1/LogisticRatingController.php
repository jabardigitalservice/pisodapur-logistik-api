<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLogisticRatingRequest;
use App\LogisticRating;
use Illuminate\Http\Response;
use DB;

class LogisticRatingController extends Controller
{
    public function store(StoreLogisticRatingRequest $request)
    {
        DB::beginTransaction();
        try {
            $logisticRating = new LogisticRating;
            $logisticRating->fill($request->validated());
            $logisticRating->created_by = auth()->user()->id ?? null;
            $logisticRating->save();
            DB::commit();
            return response()->format(Response::HTTP_OK, 'success', ['request' => $request->all(), 'logistic_rating' => $logisticRating]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->format(Response::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage(), $th->getTrace());
        }
    }
}
