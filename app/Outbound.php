<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Outbound extends Model
{
    protected $fillable = [
        'req_id',
        'req_type',
        'lo_id',
        'lo_date',
        'lo_desc',
        'lo_cb',
        'lo_issued_by',
        'lo_ct',
        'send_to_id',
        'send_to_extid',
        'send_to_name',
        'send_to_address',
        'city_id',
        'send_to_city',
        'lo_location',
        'whs_name',
        'lo_proses_stt',
        'lo_approved_time',
        'lo_app_cb',
        'lo_approved_by',
        'delivery_id',
        'delivery_date',
        'delivery_transporter',
        'delivery_driver',
        'delivery_fleet',
        'delivery_ct',
        'delivery_cb',
        'delivery_issued_by'
    ];

    public function scopeReadyToDeliver($query)
    {
        return $this->where('status', 'NEW');
    }
}
