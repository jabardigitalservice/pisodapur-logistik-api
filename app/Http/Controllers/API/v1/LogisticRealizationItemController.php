<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use App\Validation;
use DB;
use JWTAuth;
use App\Applicant;
use App\Needs;
use App\PoslogProduct;

class LogisticRealizationItemController extends Controller
{
    public function validator($request, $parameters)
    {
        return Validator::make($request->all(), $parameters);
    }

    public function store(Request $request)
    {
        $params = [
            'need_id' => 'numeric',
            'status' => 'string'
        ];
        $params = $this->extraParam($request->input('store_type'), $params);
        $response = Validation::validate($request, $params);        
        if ($response->getStatusCode() === 200) {
            $response = $this->isValidStatus($request);
            if ($response->getStatusCode() === 200) { //Validate applicant verification status must VERIFIED
                if ($this->isApplicantExists($request, 'store')) {
                    try {
                        $model = new LogisticRealizationItems();
                        $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
                        $resultset = $this->setValue($request, $findOne);
                        $findOne = $resultset['findOne'];
                        $request = $resultset['request'];
                        $model->fill($request->input());
                        $model->save();
                        if ($findOne) { //updating latest log realization record
                            $findOne->realization_ref_id = $model->id;
                            $findOne->deleted_at = date('Y-m-d H:i:s');
                            $findOne->save();
                        }
                        $response = response()->format(200, 'success', $model);
                    } catch (\Exception $exception) { //Return Error Exception
                        $response = response()->format(400, $exception->getMessage());
                    }
                }
            }
        }
        return $response;
    }

    public function add(Request $request)
    {
        $params = [
            'agency_id' => 'numeric', 
            'product_id' => 'string',
            'usage' => 'string',
            'priority' => 'string',
            'status' => 'string'
        ];
        $params = $this->extraParam($request->input('store_type'), $params);
        $requirParams = $this->validator($request, $params);
        if ($requirParams->fails()) {
            return response()->format(422, $requirParams->errors());
        } else if (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else if ($this->isApplicantExists($request, 'add')) {
            $request['applicant_id'] = $request->input('applicant_id', $request->input('agency_id'));  
            //Get Material from PosLog by Id
            $request = $this->getPosLogData($request);
            $realization = $this->realizationStore($request);
        }
        return response()->format(200, 'success');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $params = [
            'agency_id' => 'required'
        ];
        $response = Validation::validate($request, $params);        
        if ($response->getStatusCode() === 200) {
            $limit = $request->input('limit', 3);
            $data = LogisticRealizationItems::select(
                'id',
                'realization_ref_id',
                'agency_id',
                'applicant_id',
                'created_at',
                'created_by',
                'material_group',
                'need_id',
                'product_id',
                'unit_id',
                'updated_at',
                'updated_by',
                'final_at',
                'final_by',

                'product_id as recommendation_product_id',
                'product_name as recommendation_product_name',
                'realization_ref_id as recommendation_ref_id',
                'realization_date as recommendation_date',
                'realization_quantity as recommendation_quantity',
                'realization_unit as recommendation_unit',
                'status as recommendation_status',
                'recommendation_by',
                'recommendation_at',

                'final_product_id as realization_product_id',
                'final_product_name as realization_product_name',
                'final_date as realization_date',
                'final_quantity as realization_quantity',
                'final_unit as realization_unit',
                'final_status as realization_status',
                'final_unit_id as realization_unit_id',
                'final_at as realization_at',
                'final_by as realization_by'
            );
            $data = LogisticRealizationItems::withPICData($data);
            $data = $data->whereNotNull('created_by')
                ->orderBy('logistic_realization_items.id') 
                ->where('logistic_realization_items.agency_id', $request->agency_id)->paginate($limit);
            $logisticItemSummary = LogisticRealizationItems::where('agency_id', $request->agency_id)->sum('realization_quantity');
            $data->getCollection()->transform(function ($item, $key) use ($logisticItemSummary) {
                $item->status = !$item->status ? 'not_approved' : $item->status;
                $item->logistic_item_summary = (int)$logisticItemSummary;
                return $item;
            });
            $response = response()->format(200, 'success', $data);
        }
        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $params = [
            'agency_id' => 'numeric',
            'product_id' => 'string',
            'status' => 'string'
        ];
        $params = $this->extraParam($request->input('store_type'), $params);
        $response = Validation::validate($request, $params);        
        if ($response->getStatusCode() === 200) {
            $response = $this->isValidStatus($request);
            if ($response->getStatusCode() === 200) {
                DB::beginTransaction();
                try {
                    $request['applicant_id'] = $request->input('applicant_id', $request->input('agency_id'));
        
                    //Get Material from PosLog by Id
                    $request = $this->getPosLogData($request);
                    $realization = $this->realizationUpdate($request, $id);

                    $data = array( 
                        'realization' => $realization
                    );
                    DB::commit();
                    $response = response()->format(200, 'success', $data);
                } catch (\Exception $exception) {
                    DB::rollBack();
                    $response = response()->format(400, $exception->getMessage());
                }
            }
        }
        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = LogisticRealizationItems::deleteData($id);
        return response()->format($result['code'], $result['message'], $result['data']);
    }

    // Utilities Function Below Here

    public function realizationStore($request)
    {   
        $store_type = $this->setStoreType($request);
        return LogisticRealizationItems::storeData($store_type);
    }

    public function realizationUpdate($request, $id)
    { 
        $findOne = LogisticRealizationItems::find($id);
        if ($findOne) {                
            //updating latest log realization record
            if ($request->input('store_type') === 'recommendation') {
                $store_type = [  
                    'agency_id' => $request->input('agency_id'),
                    'applicant_id' => $request->input('applicant_id'),
                    'product_id' => $request->input('product_id'), 
                    'product_name' => $request->input('product_name'), 
                    'realization_unit' => $request->input('recommendation_unit'), 
                    'material_group' => $request->input('material_group'), 
                    'realization_quantity' => $request->input('recommendation_quantity'),
                    'realization_date' => $request->input('recommendation_date'),
                    'status' => $request->input('status'),
                    'updated_by' => JWTAuth::user()->id,
                    'recommendation_by' => JWTAuth::user()->id,
                    'recommendation_at' => date('Y-m-d H:i:s')
                ];
            } else {
                $store_type['final_product_id'] = $request->input('product_id');
                $store_type['final_product_name'] = $request->input('product_name');
                $store_type['final_quantity'] = $request->input('realization_quantity');
                $store_type['final_unit'] = $request['realization_unit'];
                $store_type['final_date'] = $request->input('realization_date');
                $store_type['final_status'] = $request->input('status');
                $store_type['final_by'] = JWTAuth::user()->id;
                $store_type['final_at'] = date('Y-m-d H:i:s');
            }

            $findOne->fill($store_type);  
            $findOne->save();
        }
        return $findOne;
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

    public function setValue($request, $findOne)
    {
        unset($request['id']);
        $request['applicant_id'] = $request->input('applicant_id', $request->input('agency_id'));
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
        if ($request->input('store_type') === 'recommendation') {
            $request['realization_quantity'] = $request->input('recommendation_quantity');
            $request['realization_date'] = $request->input('recommendation_date');
            $request['recommendation_by'] = JWTAuth::user()->id;
            $request['recommendation_at'] = date('Y-m-d H:i:s');
        } else {
            $request['final_product_id'] = $request->input('product_id');
            $request['final_product_name'] = $request->input('product_name');
            $request['final_quantity'] = $request->input('realization_quantity');
            $request['final_unit'] = $request['realization_unit'];
            $request['final_date'] = $request->input('realization_date');
            $request['final_status'] = $request->input('status');
            $request['final_by'] = JWTAuth::user()->id;
            $request['final_at'] = date('Y-m-d H:i:s');                    
            if ($findOne) {
                $request['product_id'] = $findOne->product_id;
                $request['product_name'] = $findOne->product_name;
                $request['realization_quantity'] = $findOne->realization_quantity;
                $request['realization_unit'] = $findOne->realization_unit;
                $request['realization_date'] = $findOne->realization_date;
                $request['material_group'] = $findOne->material_group;
                $request['quantity'] = $findOne->quantity;
                $request['date'] = $findOne->date;
                $request['status'] = $findOne->status;
                $request['recommendation_by'] = $findOne->recommendation_by;
                $request['recommendation_at'] = $findOne->recommendation_at;
            } else {
                unset($request['product_id']);
                unset($request['product_name']);
                unset($request['realization_unit']);
                unset($request['material_group']);
                unset($request['quantity']);
                unset($request['date']);
                unset($request['status']);
                unset($request['unit_id']);
                unset($request['recommendation_by']);
                unset($request['recommendation_at']);
            }
        }
        $result = [
            'request' => $request, 
            'findOne' => $findOne
        ];
        return $result;
    }
    
    public function isApplicantExists($request, $method)
    {
        $applicantCheck = Applicant::where('verification_status', '=', Applicant::STATUS_VERIFIED);
        if ($method === 'store') {
            $need = Needs::findOrFail($request->need_id);
            $applicantCheck = $applicantCheck->where('id', $need->applicant_id);
        } else {
            $applicantCheck = $applicantCheck->where('agency_id', $request->agency_id);
        }
        return $applicantCheck->exists();
    }

    public function setStoreType($request)
    {        
        $store_type['need_id'] = $request->input('need_id');
        $store_type['agency_id'] = $request->input('agency_id');
        $store_type['applicant_id'] = $request->input('applicant_id');
        $store_type['created_by'] = JWTAuth::user()->id;
        $store_type['final_product_id'] = $request->input('product_id');
        $store_type['final_product_name'] = $request->input('product_name');
        $store_type['final_quantity'] = $request->input('realization_quantity');
        $store_type['final_unit'] = $request['realization_unit'];
        $store_type['final_date'] = $request->input('realization_date');
        $store_type['final_status'] = $request->input('status');
        $store_type['final_by'] = JWTAuth::user()->id;
        $store_type['final_at'] = date('Y-m-d H:i:s');
        if ($request->input('store_type') === 'recommendation') {
            $store_type = [  
                'need_id' => $request->input('need_id'), 
                'agency_id' => $request->input('agency_id'), 
                'applicant_id' => $request->input('applicant_id'), 
                'product_id' => $request->input('product_id'), 
                'product_name' => $request->input('product_name'), 
                'realization_unit' => $request->input('recommendation_unit'), 
                'material_group' => $request->input('material_group'), 
                'realization_quantity' => $request->input('recommendation_quantity'),
                'realization_date' => $request->input('recommendation_date'),
                'status' => $request->input('status'),
                'created_by' => JWTAuth::user()->id,
                'recommendation_by' => JWTAuth::user()->id,
                'recommendation_at' => date('Y-m-d H:i:s')
            ];
        }
        return $store_type;
    }
    
    public function extraParam($storeInput, $param)
    {
        $extra = [
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
        ];
        if ($storeInput === 'recommendation') {
            $extra = [
                'recommendation_quantity' => 'numeric',
                'recommendation_date' => 'date',
                'recommendation_unit' => 'string',
            ];
        }
        return array_merge($extra, $param);
    }

    public function isValidStatus($request)
    {
        $response = response()->format(200, 'success', $model);
        if (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            $response = response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        }
        return $response;
    }
}
