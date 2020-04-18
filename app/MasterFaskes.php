<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterFaskes extends Model
{
    protected $table = 'master_faskes';

    public function masterFaskesType()
    {
        return $this->hasOne('App\MasterFaskesType', 'id', 'id_tipe_faskes');
    }
}
