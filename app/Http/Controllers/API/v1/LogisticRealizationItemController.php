<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LogisticRealizationItems;
use Validator;
use DB;

class LogisticRealizationItemController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'need_id' => 'numeric',
            'quantity' => 'numeric',
            'unit_id' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string',
            'created_by' => 'string',
            'updated_by' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->format(422,  $validator->messages()->all());
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            $model = new LogisticRealizationItems();
            $findOne = LogisticRealizationItems::where('need_id', $request->need_id)->orderBy('created_at', 'desc')->first();
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
        $validator = Validator::make(
            $request->all(),
            array_merge(
                ['agency_id' => 'required']
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $limit = $request->filled('limit') ? $request->input('limit') : 10;
            $data = LogisticRealizationItems::where('created_by', '999999')
            ->with([
                'product' => function ($query) {
                    return $query->select(['id', 'name']);
                },
                'unit' => function ($query) {
                    return $query->select(['id', 'unit']);
                }
            ]) 
            ->whereNull('logistic_realization_items.deleted_at')
            ->orderBy('id')
            ->where('agency_id', $request->agency_id)
            ->paginate($limit);
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
        $validator = Validator::make($request->all(), [    
            'agency_id' => 'numeric',  
            'product_id' => 'numeric', 
            'unit_id' => 'numeric', 
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string', 
            'created_by' => 'string',
            'updated_by' => 'string'
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
                'created_by' => 999999
            ]
        );

        return $realization;
    }

    public function realizationUpdate($request, $id)
    {
        $model = new LogisticRealizationItems();
        $findOne = LogisticRealizationItems::find($id);
        $model->fill(
            [ 
                'need_id' => $id,
                'agency_id' => $request->input('agency_id'),
                'product_id' => $request->input('product_id'), 
                'realization_quantity' => $request->input('realization_quantity'),
                'unit_id' => $request->input('unit_id'),
                'realization_date' => $request->input('realization_date'),
                'status' => $request->input('status'),
                'created_by' => 999999
            ]
        );
        $model->save();
        if ($findOne) {                
            //updating latest log realization record 
            $findOne->realization_ref_id = $model->id;
            $findOne->deleted_at = date('Y-m-d H:i:s');
            $findOne->save();
        }
        return $model;
    }
    
}
