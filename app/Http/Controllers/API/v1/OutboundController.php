<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\WmsJabar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Validation;

class OutboundController extends Controller
{
    /**
     * sendPing function
     * send notification to WMS Jabar to read new logistic request list and create their outbound tickets
     *
     * @return void
     */
    public function sendPing()
    {
        return WmsJabar::sendPing();
    }

    /**
     * getNotification function for API {{base_url}}/api/v1/poslog-notify
     * Get notification from POSLOG to read update logistic request data
     *
     * @return void
     */
    public function getNotification(Request $request)
    {
        $param = [
            'request_id' => 'required'
        ];
        $response = Validation::validate($request, $param);
        if ($response->getStatusCode() == Response::HTTP_OK) {
            $getOutboundById = WmsJabar::getOutboundById($request);
            $response = response()->format(Response::HTTP_OK, 'notification accepted', $getOutboundById);
        }
        return $response;
    }
}