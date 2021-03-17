<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\WmsJabar;

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
}
