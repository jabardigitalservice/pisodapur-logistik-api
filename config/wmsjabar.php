<?php

return [
    'url' => env('WMS_JABAR_BASE_URL', 'localhost'),
    'key' => env('WMS_JABAR_API_KEY', '123'),
    'cut_off_datetime' => env('WMS_JABAR_CUT_OFF_DATETIME', '2021-04-01 00:00:00'),
    'cut_off_format' => env('WMS_JABAR_CUT_OFF_FORMAT', 'Y-m-d H:i:s'),
];
