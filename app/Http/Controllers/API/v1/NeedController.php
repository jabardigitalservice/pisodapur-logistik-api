<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogisticRequestNeedResource;
use App\Needs;
use App\Traits\TransformTrait;
use App\Validation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NeedController extends Controller
{
    use TransformTrait;

    public function index(Request $request)
    {
        $data = array();
        $limit = $request->input('limit', 10);

        $param = ['agency_id' => 'required'];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() != 200) {
            return $response;
        }

        $query = Needs::getFields()
            ->joinLogisticRealizationItem()
            ->joinProduct()
            ->joinUnit()
            ->whereNull('logistic_realization_items.deleted_at')
            ->where('needs.agency_id', $request->agency_id);

        $administration = $this->getDataTransform($query->paginate($limit));
        $recommendation = $this->getDataTransform($query->paginate($limit), 'recommendation');
        $realization = $this->getDataTransform($query->paginate($limit), 'realization');

        array_push($data, $administration);
        array_push($data, $recommendation);
        array_push($data, $realization);

        return response()->format(Response::HTTP_OK, 'success', $data);
    }
}
