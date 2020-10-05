<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use Validator;
use DB;
use JWTAuth;
use App\User;
use App\Applicant;
use App\Needs;
use App\PoslogProduct;

class LogisticRealizationItemController extends Controller
{
    public function store(Request $request)
    {
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make($request->all(), [
            'need_id' => 'numeric',
            'status' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            //Validate applicant verification status must VERIFIED 
            $need = Needs::findOrFail($request->need_id);
            $applicantCheck = Applicant::where('id', $need->applicant_id)->where('verification_status', '=', Applicant::STATUS_VERIFIED)->exists();

            if (!$applicantCheck) {
                return response()->format(422, 'application verification status is not verified');
            } else {
                $model = new LogisticRealizationItems();
                $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
                unset($request['id']);
                $request['unit_id'] = 1;
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
                    $request['final_unit_id'] = $request->input('unit_id');
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
                        $request['unit_id'] = $findOne->unit_id;
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

                $model->fill($request->input());
                if ($model->save()) {            
                    if ($findOne) {
                        //updating latest log realization record 
                        $findOne->realization_ref_id = $model->id;
                        $findOne->deleted_at = date('Y-m-d H:i:s');
                        if ($findOne->save()) {
                            return response()->format(200, 'success', $model);
                        }
                    } else {
                        return response()->format(200, 'success', $model);
                    }
                }
            }
        }
    }

    public function add(Request $request)
    {    
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make($request->all(), [             
            'agency_id' => 'numeric', 
            'product_id' => 'string',
            'usage' => 'string',
            'priority' => 'string',
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            //Validate applicant verification status must VERIFIED  
            $applicantCheck = Applicant::where('agency_id', $request->agency_id)->where('verification_status', '=', Applicant::STATUS_VERIFIED)->exists();

            if (!$applicantCheck) {
                return response()->format(422, 'application verification status is not verified');
            } else {
                DB::beginTransaction();
                try {                    
                    $request['unit_id'] = 1;
                    $request['applicant_id'] = $request->input('applicant_id', $request->input('agency_id'));
        
                    //Get Material from PosLog by Id
                    $request = $this->getPosLogData($request);
                    $realization = $this->realizationStore($request);

                    $response = array(
                        'realization' => $realization
                    );
                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return response()->format(400, $exception->getMessage());
                }
            }
        }

        return response()->format(200, 'success', $response);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                ['agency_id' => 'required']
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $limit = $request->input('limit', 10);
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
            )
            ->with([
                'verifiedBy' => function ($query) {
                    return $query->select(['id', 'name', 'agency_name', 'handphone']);
                },
                'recommendBy' => function ($query) {
                    return $query->select(['id', 'name', 'agency_name', 'handphone']);
                },
                'realizedBy' => function ($query) {
                    return $query->select(['id', 'name', 'agency_name', 'handphone']);
                }
            ])
            ->whereNotNull('created_by')
            ->orderBy('logistic_realization_items.id') 
            ->where('logistic_realization_items.agency_id', $request->agency_id)->paginate($limit);
            $logisticItemSummary = LogisticRealizationItems::where('agency_id', $request->agency_id)->sum('realization_quantity');
            $data->getCollection()->transform(function ($item, $key) use ($logisticItemSummary) {
                $item->status = !$item->status ? 'not_approved' : $item->status;
                $item->logistic_item_summary = (int)$logisticItemSummary;
                return $item;
            });
        }

        return response()->format(200, 'success', $data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make($request->all(), [    
            'agency_id' => 'numeric',
            'product_id' => 'string',
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->format(422, $validator->errors()); 
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            DB::beginTransaction();
            try {                   
                $request['unit_id'] = 1;
                $request['applicant_id'] = $request->input('applicant_id', $request->input('agency_id'));
    
                //Get Material from PosLog by Id
                $request = $this->getPosLogData($request);
                $realization = $this->realizationUpdate($request, $id);

                $response = array( 
                    'realization' => $realization
                );
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->format(400, $exception->getMessage());
            }
        }

        return response()->format(200, 'success', $response);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }
        
        DB::beginTransaction();
        try {   
            $deleteRealization = LogisticRealizationItems::where('id', $id)->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->format(400, $exception->getMessage());
        }
        return response()->format(200, 'success', ['id' => $id]);
    }


    // Utilities Function Below Here

    public function realizationStore($request)
    {
        if ($request->input('store_type') === 'recommendation') {
            $store_type = [  
                'need_id' => $request->input('need_id'), 
                'agency_id' => $request->input('agency_id'), 
                'applicant_id' => $request->input('applicant_id'), 
                'product_id' => $request->input('product_id'), 
                'product_name' => $request->input('product_name'), 
                'realization_unit' => $request->input('realization_unit'), 
                'material_group' => $request->input('material_group'), 
                'realization_quantity' => $request->input('realization_quantity'),
                'unit_id' => $request->input('unit_id'),
                'realization_date' => $request->input('realization_date'),
                'status' => $request->input('status'),
                'created_by' => JWTAuth::user()->id,
                'recommendation_by' => JWTAuth::user()->id,
                'recommendation_at' => date('Y-m-d H:i:s')
            ];
        } else {
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
            $store_type['final_unit_id'] = $request->input('unit_id');
            $store_type['final_by'] = JWTAuth::user()->id;
            $store_type['final_at'] = date('Y-m-d H:i:s');
        }

        $realization = LogisticRealizationItems::create($store_type);
        return $realization;
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
                    'realization_unit' => $request->input('realization_unit'), 
                    'material_group' => $request->input('material_group'), 
                    'realization_quantity' => $request->input('realization_quantity'),
                    'unit_id' => $request->input('unit_id'),
                    'realization_date' => $request->input('realization_date'),
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
                $store_type['final_unit_id'] = $request->input('unit_id');
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
}
