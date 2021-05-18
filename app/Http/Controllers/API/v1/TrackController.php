<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Tracking;
use App\Needs;
use App\Outbound;
use App\OutboundDetail;
use DB;

class TrackController extends Controller
{
    /**
     * Track Function
     * Show application list based on ID, No. HP, or applicant email
     * @param Request $request
     * @return array of Applicant $data
     */
    public function index(Request $request)
    {
        $list = Tracking::trackList($request);
        $data = [
            'total' => count($list),
            'application' => $list
        ];
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    /**
     * Track Detail function
     * - return data is pagination so it can receive the parameter limit, page, sorting and filtering / searching
     * @param Request $request
     * @param integer $id
     * @return array of Applicant $data
     */
    public function show(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        $select = Tracking::selectFieldsDetail();
        $logisticRealizationItems = Tracking::getLogisticAdmin($select, $request, $id); //List of item(s) added from admin
        $data = Tracking::getLogisticRequest($select, $request, $id); //List of updated item(s)
        $data = $data->union($logisticRealizationItems)->paginate($limit);
        return response()->format(Response::HTTP_OK, 'success', $data);
    }

    public function request(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        return $data = Needs::select(
                    'needs.id',
                    'needs.product_id',
                    'needs.brand as description',
                    'needs.quantity',
                    'needs.usage',
                    'master_unit.unit as unit_name',
                    'products.material_group',
                    'products.name as product_name'
                )
                ->join('master_unit', 'master_unit.id', '=', 'needs.unit')
                ->join('products', 'products.id', '=', 'needs.product_id')
                ->where('needs.agency_id', $id)
                ->orderBy('needs.id')
                ->paginate($limit);
    }

    public function recommendation(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        $select = $this->setSelect('recommendation');

        $logisticAdmin = Tracking::getLogisticAdmin($select, $request, $id) //List of item(s) added from admin
                                    ->whereIn('logistic_realization_items.status', ['approved', 'replaced'])
                                    ->whereNotNull('logistic_realization_items.recommendation_at');
        $data = Tracking::getLogisticRequest($select, $request, $id) //List of updated item(s)
                ->whereIn('logistic_realization_items.status', ['approved', 'replaced'])
                ->whereNotNull('logistic_realization_items.recommendation_at')
                ->union($logisticAdmin)->paginate($limit);
        return $data;
    }

    public function finalization(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        $select = $this->setSelect('finalization');

        $logisticAdmin = Tracking::getLogisticAdmin($select, $request, $id) //List of item(s) added from admin
                                    ->whereIn('final_status', ['approved', 'replaced'])
                                    ->whereNotNull('logistic_realization_items.final_date');
        $data = Tracking::getLogisticRequest($select, $request, $id) //List of updated item(s)
                ->whereIn('final_status', ['approved', 'replaced'])
                ->whereNotNull('logistic_realization_items.final_date')
                ->union($logisticAdmin)->paginate($limit);
        return $data;
    }

    public function outbound(Request $request, $id)
    {
        $limit = $request->input('limit', 3);
        $outbound = Outbound::select('lo_id', 'whs_name', 'pic_name', 'pic_handphone', 'map_url')
                    ->join('soh_locations', 'soh_locations.location_id', '=', 'outbounds.lo_location')
                    ->where('req_id', $id)
                    ->get();
        $outboundDetail = OutboundDetail::query()
                        ->where('req_id', $id)
                        ->where('lo_id', $request->input('lo_id'))
                        ->paginate($limit);
        $data = [
            'outbound' => $outbound,
            'outbound_detail' => $outboundDetail,
        ];
        return $data;
    }

    public function setSelect($phase)
    {
        $select = [
            DB::raw('IFNULL(logistic_realization_items.id, needs.id) as id'),
            'needs.id as need_id',
            'logistic_realization_items.id as realization_id',
            'products.category',
            'logistic_realization_items.final_product_id as product_id',
            'logistic_realization_items.final_product_name as product_name',
            'logistic_realization_items.final_quantity as quantity',
            'logistic_realization_items.final_unit as unit_name',
            'logistic_realization_items.final_date as created_at',
            'logistic_realization_items.final_status as status'
        ];

        if ($phase == 'recommendation') {
            $select = [
                DB::raw('IFNULL(logistic_realization_items.id, needs.id) as id'),
                'needs.id as need_id',
                'logistic_realization_items.id as realization_id',
                'products.category',
                'logistic_realization_items.product_id as product_id',
                'logistic_realization_items.product_name as product_name',
                'logistic_realization_items.realization_quantity as quantity',
                'realization_unit as unit_name',
                'logistic_realization_items.created_at',
                'logistic_realization_items.status as status'
            ];
        }

        return $select;
    }
}
