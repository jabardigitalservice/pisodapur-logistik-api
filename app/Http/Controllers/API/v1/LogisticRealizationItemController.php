<?php

namespace App\Http\Controllers\API\v1;

use App\Needs;
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
            'applicant_id' => 'numeric',
            'product_id' => 'numeric',
            'quantity' => 'numeric',
            'unit_id' => 'numeric',
            'usage' => 'string',
            'priority' => 'string', 
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } elseif (!in_array($request->priority, Needs::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'priority value is not accepted']);
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            DB::beginTransaction();
            try {  
                $need = $this->needStore($request);
                $request->request->add(['need_id' => $need->id]);

                $realization = $this->realizationStore($request);

                $response = array(
                    'need' => $need,
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
            $data = Needs::select(
                'needs.id',
                'needs.agency_id',
                'needs.applicant_id',
                'needs.product_id',
                'needs.item',
                'needs.brand',
                'needs.quantity',
                'needs.unit',
                'needs.usage',
                'needs.priority',
                'needs.created_at',
                'needs.updated_at',
                'logistic_realization_items.need_id',
                'logistic_realization_items.realization_quantity',
                'logistic_realization_items.unit_id',
                'logistic_realization_items.realization_date',
                'logistic_realization_items.status',
                'logistic_realization_items.realization_quantity',
                'logistic_realization_items.created_by',
                'logistic_realization_items.updated_by'
            )
            ->with([
                'product' => function ($query) {
                    return $query->select(['id', 'name']);
                },
                'unit' => function ($query) {
                    return $query->select(['id', 'unit']);
                }
            ])
            ->join('logistic_realization_items', 'logistic_realization_items.need_id', '=', 'needs.id', 'left')
            ->whereNull('logistic_realization_items.deleted_at')
            ->where('needs.created_by', 'admin')
            ->orderBy('needs.id')
            ->where('needs.agency_id', $request->agency_id)->paginate($limit);
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
        $validator = Validator::make($request->all(), [    
            'agency_id' => 'numeric',  
            'product_id' => 'numeric',
            'quantity' => 'numeric',
            'unit_id' => 'numeric',
            'usage' => 'string',
            'priority' => 'string',
            'realization_quantity' => 'numeric',
            'realization_date' => 'date',
            'status' => 'string', 
            'created_by' => 'string',
            'updated_by' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->format(422, $validator->errors());
        } elseif (!in_array($request->priority, Needs::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'priority value is not accepted']);
        } elseif (!in_array($request->status, LogisticRealizationItems::STATUS)) {
            return response()->json(['status' => 'fail', 'message' => 'verification_status_value_is_not_accepted']);
        } else {
            DB::beginTransaction();
            try {  
                $need = $this->needUpdate($request, $id);
                $realization = $this->realizationUpdate($request, $id);

                $response = array(
                    'need' => $need,
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
        $data = LogisticRealizationItems::all();
        return response()->format(200, 'success', $data);
    }
    
    public function needStore($request)
    { 
        $need = Needs::create(
            [
                'agency_id' => $request->input('agency_id'),
                'applicant_id' => $request->input('applicant_id'),
                'product_id' => $request->input('product_id'),
                'brand' => $request->input('brand'),
                'quantity' => $request->input('quantity'),
                'unit' => $request->input('unit_id'),
                'usage' => $request->input('usage'),
                'priority' => $request->input('priority'),
                'created_by' => 'admin'
            ]
        );

        return $need;
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
            ]
        );

        return $realization;
    }
    
    
    public function needUpdate($request, $id)
    { 
        $need = Needs::where('id', $id)->update(
            [
                'agency_id' => $request->input('agency_id'), 
                'product_id' => $request->input('product_id'),
                'brand' => $request->input('brand'),
                'quantity' => $request->input('quantity'),
                'unit' => $request->input('unit_id'),
                'usage' => $request->input('usage'),
                'priority' => $request->input('priority'),
                'created_by' => 'admin'
            ]
        );

        return $need;
    }

    public function realizationUpdate($request, $id)
    {
        $model = new LogisticRealizationItems();
        $findOne = LogisticRealizationItems::where('need_id', $id)->orderBy('created_at', 'desc')->first();
        $model->fill(
            [ 
                'need_id' => $id,
                'agency_id' => $request->input('agency_id'),
                'product_id' => $request->input('product_id'), 
                'realization_quantity' => $request->input('realization_quantity'),
                'unit_id' => $request->input('unit_id'),
                'realization_date' => $request->input('realization_date'),
                'status' => $request->input('status'),
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
