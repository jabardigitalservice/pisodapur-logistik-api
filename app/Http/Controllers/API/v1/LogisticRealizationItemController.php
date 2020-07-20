<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use Validator;
use DB;
use JWTAuth;
use App\User;
use App\Needs;

class LogisticRealizationItemController extends Controller
{
    public function store(Request $request)
    {        
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make($request->all(), [
            'need_id' => 'numeric',
            'quantity' => 'numeric',
            'unit_id' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            $model = new LogisticRealizationItems();
            $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
            unset($request['id']);
            $request['unit_id'] = $request->input('unit_id', 1);
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

    public function add(Request $request)
    {    
        if (!in_array(JWTAuth::user()->roles, User::ADMIN_ROLE)) {
            return response()->format(404, 'You cannot access this page', null);
        }

        $validator = Validator::make($request->all(), [             
            'agency_id' => 'numeric', 
            'product_id' => 'numeric', 
            'unit_id' => 'numeric',
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
            DB::beginTransaction();
            try {    
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
            $data = LogisticRealizationItems::with([
                    'product' => function ($query) {
                        return $query->select(['id', 'name']);
                    },
                    'unit' => function ($query) {
                        return $query->select(['id', 'unit']);
                    }
                ]) 
                ->whereNotNull('created_by')
                ->orderBy('logistic_realization_items.id') 
                ->where('logistic_realization_items.agency_id', $request->agency_id)->paginate($limit);
            $logisticItemSummary = Needs::where('needs.agency_id', $request->agency_id)->sum('quantity');
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
            'product_id' => 'numeric', 
            'unit_id' => 'numeric', 
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
        $realization = LogisticRealizationItems::create(
            [ 
                'need_id' => $request->input('need_id'),
                'agency_id' => $request->input('agency_id'),
                'product_id' => $request->input('product_id'), 
                'realization_quantity' => $request->input('realization_quantity'),
                'unit_id' => $request->input('unit_id'),
                'realization_date' => $request->input('realization_date'),
                'status' => $request->input('status'),
                'created_by' => JWTAuth::user()->id
            ]
        );

        return $realization;
    }

    public function realizationUpdate($request, $id)
    { 
        $findOne = LogisticRealizationItems::find($id);
        if ($findOne) {                
            //updating latest log realization recor
            $findOne->fill(
                [  
                    'agency_id' => $request->input('agency_id'),
                    'product_id' => $request->input('product_id'), 
                    'realization_quantity' => $request->input('realization_quantity'),
                    'unit_id' => $request->input('unit_id'),
                    'realization_date' => $request->input('realization_date'),
                    'status' => $request->input('status'),
                    'updated_by' => JWTAuth::user()->id
                ]
            );  
            $findOne->save();
        }
        return $findOne;
    }
    
}
