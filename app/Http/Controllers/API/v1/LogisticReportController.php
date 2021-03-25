<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Validation;
use DB;
use App\LogisticRealizationItems;
use App\AcceptanceReport;
use App\AcceptanceReportDetail;
use App\FileUpload;

class LogisticReportController extends Controller
{
    public function acceptanceStore(Request $request)
    {
        $param = AcceptanceReport::setParamStore();
        $response = Validation::validate($request, $param);
        DB::beginTransaction();
        try {
            $acceptanceReport = $this->storeAcceptanceReport($request);
            $this->itemStore($request, $acceptanceReport);
            // Upload Seluruh File
            $proof_pic = $this->uploadAcceptanceFile($request, 'proof_pic');
            $bast_proof = $this->uploadAcceptanceFile($request, 'bast_proof');
            $item_proof = $this->uploadAcceptanceFile($request, 'item_proof');
            DB::commit();
            $response = response()->format(200, 'success');
        } catch (\Exception $exception) {
            DB::rollBack();
            $response = response()->format(422, 'Terjadi Kesalahan', ['message' => $exception->getMessage(), 'exception' => $exception]);
        }
        return $response;
    }

    public function storeAcceptanceReport($request)
    {
        $acceptanceReport = new AcceptanceReport;
        $acceptanceReport->fullname = $request->fullname;
        $acceptanceReport->position = $request->position;
        $acceptanceReport->phone = $request->phone;
        $acceptanceReport->date = $request->date;
        $acceptanceReport->officer_fullname = $request->officer_fullname;
        $acceptanceReport->note = $request->note;
        $acceptanceReport->agency_id = $request->agency_id;
        $acceptanceReport->save();
        return $acceptanceReport;
    }

    public function itemStore($request, $acceptanceReport)
    {
        foreach (json_decode($request->items, true) as $value) {
            $acceptanceReportDetail = new AcceptanceReportDetail;
            $acceptanceReportDetail->acceptance_report_id = $acceptanceReport->id;
            $acceptanceReportDetail->agency_id = $request->agency_id;
            $acceptanceReportDetail->logistic_realization_item_id = $value['id'];
            $acceptanceReportDetail->product_id = $value['product_id'];
            $acceptanceReportDetail->product_name = $value['name'];
            $acceptanceReportDetail->qty = $value['qty'];
            $acceptanceReportDetail->unit = $value['unit'];
            $acceptanceReportDetail->status = $value['status'];
            $acceptanceReportDetail->qty_ok = $value['qty_ok'];
            $acceptanceReportDetail->qty_nok = $value['qty_nok'];
            $acceptanceReportDetail->quality = $value['quality'];
            $acceptanceReportDetail->save();
        }
    }

    public function uploadAcceptanceFile($request, $paramName)
    {
        $file = [];
        for ($i = 0; $i < $request->input($paramName . '_length'); $i++) {
            if ($request->hasFile($paramName . $i)) {
                $file[] = FileUpload::uploadAcceptanceReportFile($request, $paramName . $i);
            }
        }

        return $file;
    }

    public function realizationLogisticList(Request $request, $id)
    {
        $select = [
            'logistic_realization_items.id as id',
            'logistic_realization_items.final_product_id as product_id',
            'logistic_realization_items.final_product_name as name',
            'logistic_realization_items.final_quantity as qty',
            'logistic_realization_items.final_unit as unit',
            'logistic_realization_items.final_status as status',
            DB::raw('0 as qty_ok'),
            DB::raw('0 as qty_nok')
        ];
        $data = LogisticRealizationItems::select($select)
        ->where('agency_id', $id)
        ->whereIn('logistic_realization_items.final_status', [LogisticRealizationItems::STATUS_REPLACED, LogisticRealizationItems::STATUS_APPROVED])
        ->get();
        $response = response()->format(200, 'success', $data);
        return $response;
    }
}
