<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AcceptanceReport extends Model
{
    static function setParamStore()
    {
        $param['fullname'] = 'required';
        $param['position'] = 'required';
        $param['phone'] = 'required';
        $param['date'] = 'required';
        $param['officer_fullname'] = 'required';
        $param['note'] = 'required';
        $param['agency_id'] = 'required';
        $param['items'] = 'required';
        $param['proof_picproof_pic'] = 'required';
        $param['proof_picproof_pic_length'] = 'required';
        $param['bast_proof'] = 'required';
        $param['bast_proof_length'] = 'required';
        $param['item_proof'] = 'required';
        $param['item_proof_length'] = 'required';
        return $param;
    }
}
