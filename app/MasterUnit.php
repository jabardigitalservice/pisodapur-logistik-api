<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterUnit extends Model
{
    protected $table = 'master_unit';
    protected $fillable = ['unit'];

    public function need()
    {
        return $this->belongsToOne('App\Needs', 'unit', 'id');
    }
}
