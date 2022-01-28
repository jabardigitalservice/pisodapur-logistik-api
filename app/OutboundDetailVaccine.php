<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OutboundDetailVaccine extends Model
{
    protected $fillable = [
        'req_id',
        'lo_id',
        'material_id',
        'material_name',
        'UoM',
        'matg_id',
        'matgsub_id',
        'donatur_id',
        'donatur_name',
        'lo_qty',
        'lo_plan_qty',
        'lo_proses_stt',
        'lo_approved_time'
    ];

    static function massInsert($query)
    {
        $detil = collect($query)->map(function ($lo_detil) {
            $lo_detil['created_at'] = Carbon::now();
            $lo_detil['updated_at'] = Carbon::now();
            return $lo_detil;
        })->toArray();
        OutboundDetail::insert($detil);
    }
}
