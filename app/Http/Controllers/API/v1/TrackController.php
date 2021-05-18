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
    }
}
