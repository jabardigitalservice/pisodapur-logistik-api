<?php

namespace App;

use App\Enums\AllocationRequestTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OutboundDetail extends Model
{
    protected $fillable = [
        'req_id',
        'req_type',
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

    static function massInsert($query, $req_type = null)
    {
        $req_type = $req_type ?? AllocationRequestTypeEnum::vaccine();
        $detil = collect($query)->map(function ($lo_detil) use ($req_type) {
            $lo_detil['created_at'] = Carbon::now();
            $lo_detil['updated_at'] = Carbon::now();
            $lo_detil['req_type'] = $req_type;
            return $lo_detil;
        })->toArray();
        return OutboundDetail::insert($detil);
    }
}
