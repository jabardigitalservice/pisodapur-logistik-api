<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use App\Http\Requests\LogisticRealizationItem\AddRequest;
use App\Http\Requests\LogisticRealizationItem\GetRequest;
use App\Http\Requests\LogisticRealizationItem\StoreRequest;
use App\PoslogProduct;
use Illuminate\Http\Response;
use Log;

class LogisticRealizationItemController extends Controller
{
    public function store(StoreRequest $request)
    {
        $model = new LogisticRealizationItems();
        $resultset = $this->setValue($request);
        $findOne = $resultset['findOne'];
        $request = $resultset['request'];
        $model->fill($request->input());
        $model->save();
        if ($findOne) { //updating latest log realization record
            $findOne->realization_ref_id = $model->id;
            $findOne->deleted_at = date('Y-m-d H:i:s');
            $findOne->save();
        }
        $response = response()->format(Response::HTTP_OK, 'success', $model);
        Log::channel('dblogging')->debug('post:v1/logistic-request/realization', $request->all());
        return $response;
    }

    public function add(AddRequest $request)
    {
        $request = $this->getPosLogData($request);
        return $this->realizationStore($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GetRequest $request)
    {
        $limit = $request->input('limit', 3);
        $data = LogisticRealizationItems::getList($request);
        $response = response()->format(Response::HTTP_OK, 'success', $data);
        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(AddRequest $request, $id)
    {
        //Get Material from PosLog by Id
        $request = $this->getPosLogData($request);
        $findOne = LogisticRealizationItems::findOrFail($id);
        $realization = $this->realizationUpdate($request, $findOne);
        $data['realization']  = $realization;

        $response = response()->format(Response::HTTP_OK, 'success', $data);
        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteRealization = LogisticRealizationItems::where('id', $id)->delete();
        return response()->format(Response::HTTP_OK, 'success', $id);
    }

    // Utilities Function Below Here

    public function realizationStore($request)
    {
        $store_type = $this->setFinalData($request);
        if ($request->input('store_type') === 'recommendation') {
            $store_type = $this->setRecommendationData($request);
        }
        $store_type['agency_id'] = $request->input('agency_id');
        $store_type['created_by'] = auth()->user()->id;
        return LogisticRealizationItems::create($store_type);
    }

    public function realizationUpdate($request, $findOne)
    {
        $store_type = $this->setFinalData($request);
        if ($request->input('store_type') === 'recommendation') {
            $store_type = $this->setRecommendationData($request);
        }
        $store_type['updated_by'] = auth()->user()->id;

        $findOne->fill($store_type);
        $findOne->save();
        return $findOne;
    }

    public function setRecommendationData($request)
    {
        return [
            'agency_id' => $request->input('agency_id'),
            'applicant_id' => $request->input('applicant_id'),
            'product_id' => $request->input('product_id'),
            'product_name' => $request->input('product_name'),
            'realization_unit' => $request->input('recommendation_unit'),
            'material_group' => $request->input('material_group'),
            'realization_quantity' => $request->input('recommendation_quantity'),
            'realization_date' => $request->input('recommendation_date'),
            'status' => $request->input('status'),
            'recommendation_by' => auth()->user()->id,
            'recommendation_at' => date('Y-m-d H:i:s')
        ];
    }

    public function setFinalData($request)
    {
        $store_type['final_product_id'] = $request->input('product_id');
        $store_type['final_product_name'] = $request->input('product_name');
        $store_type['final_quantity'] = $request->input('realization_quantity');
        $store_type['final_unit'] = $request->input('realization_unit');
        $store_type['final_date'] = $request->input('realization_date');
        $store_type['final_status'] = $request->input('status');
        $store_type['final_by'] = auth()->user()->id;
        $store_type['final_at'] = date('Y-m-d H:i:s');
        return $store_type;
    }

    public function getPosLogData($request)
    {
        $material = PoslogProduct::where('material_id', $request->product_id)->first();
        if ($material) {
            $request['product_name'] = $material->material_name;
            $request['realization_unit'] = $material->uom;
            $request['material_group'] = $material->matg_id;
        }
        return $request;
    }

    public function setValue($request)
    {
        $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
        unset($request['id']);
        if ($request->input('status') !== LogisticRealizationItems::STATUS_NOT_AVAILABLE) {
            //Get Material from PosLog by Id
            $request = $this->getPosLogData($request);
        } else {
            unset($request['realization_unit']);
            unset($request['material_group']);
            unset($request['realization_quantity']);
            unset($request['unit_id']);
            unset($request['realization_date']);
        }
        $request = LogisticRealizationItems::setValue($request, $findOne);
        $result = [
            'request' => $request,
            'findOne' => $findOne
        ];
        return $result;
    }
}
