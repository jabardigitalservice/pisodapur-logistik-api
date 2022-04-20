<?php

return [
    'url' => env('WMS_POSLOG_VACCINE_BASE_URL', 'wmsposlog'),
    'key' => env('WMS_POSLOG_VACCINE_API_KEY', '123'),
    'cut_off_datetime' => env('WMS_JABAR_CUT_OFF_DATETIME', '2021-04-01 00:00:00'),
    'cut_off_format' => env('WMS_JABAR_CUT_OFF_FORMAT', 'Y-m-d H:i:s'),
];
